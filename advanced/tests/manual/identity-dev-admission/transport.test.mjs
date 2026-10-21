import test from 'node:test';
import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { spawn } from 'node:child_process';
import http from 'node:http';
import { frameworks, namespace, clientFile } from './fixture-config.mjs';

// No real coordinator key or source data is used. This private local peer tests
// the exact client's codec and total wall clock under real socket I/O.
for (const framework of frameworks) for (const phase of ['fast', 'headers', 'body']) {
  test(`${framework} private control ${phase} is bounded by total wall time rather than idle reads`, async () => {
    const timers = new Set(); const sockets = new Set();
    const claims = { operationId: '11111111-1111-4111-8111-111111111111', rootOperationId: '11111111-1111-4111-8111-111111111111', instanceId: 'fixture-old', epoch: 1, nonce: 'a'.repeat(32) };
    const capability = `${Buffer.from(JSON.stringify(claims)).toString('base64url')}.${'a'.repeat(43)}`;
    const body = JSON.stringify({ operationId: claims.operationId, rootOperationId: claims.rootOperationId, epoch: 1, unknown: false, childrenActive: 0 });
    const server = http.createServer((request, response) => {
      request.resume();
      if (phase === 'fast') { response.writeHead(200, { 'content-type': 'application/json' }).end(body); return; }
      const data = phase === 'body' ? body : `HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nContent-Length: ${body.length}\r\nConnection: close\r\n\r\n${body}`;
      if (phase === 'body') { response.writeHead(200, { 'content-type': 'application/json' }); response.flushHeaders(); }
      let index = 0;
      const timer = setInterval(() => {
        if (index >= data.length) { clearInterval(timer); timers.delete(timer); if (phase === 'body') response.end(); else request.socket.end(); return; }
        if (phase === 'body') response.write(data[index++]); else request.socket.write(data[index++]);
      }, 20);
      timers.add(timer); request.socket.once('close', () => { clearInterval(timer); timers.delete(timer); });
    });
    server.on('connection', socket => { sockets.add(socket); socket.once('close', () => sockets.delete(socket)); socket.on('error', () => {}); });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const ns = namespace;
    const file = clientFile;
    const script = `require getenv('FIXTURE_CLIENT_FILE'); $class=getenv('FIXTURE_CLIENT_CLASS');$client=$class::fromEnvironment();$start=hrtime(true);try{$client->call('validate',['capability'=>getenv('FIXTURE_CAPABILITY')],100);$ok=true;$safe=true;}catch(Throwable $error){$ok=false;$safe=$error->getMessage()==='Identity admission control is unavailable.';}echo json_encode(['accepted'=>$ok,'safeError'=>$safe,'elapsedMs'=>(hrtime(true)-$start)/1000000]);`;
    const env = { ...process.env, IDENTITY_DEV_ADMISSION_ENVIRONMENT: 'xrugc-development', IDENTITY_DEV_ADMISSION_CONTROL_URL: `http://127.0.0.1:${server.address().port}/`,
      IDENTITY_DEV_ADMISSION_KEY: randomBytes(32).toString('base64url'), IDENTITY_DEV_ADMISSION_INSTANCE_ID: 'fixture-old', IDENTITY_DEV_ADMISSION_BOOT_ID: 'fixture-boot-0001',
      IDENTITY_DEV_ADMISSION_REVISION: 'a'.repeat(40), IDENTITY_DEV_ADMISSION_ROUTER_HASH: 'b'.repeat(64), IDENTITY_DEV_ADMISSION_GENERATION: 'legacy', IDENTITY_DEV_ADMISSION_CHILDREN: '',
      FIXTURE_CLIENT_FILE: file, FIXTURE_CLIENT_CLASS: `${ns}\\components\\admission\\CoordinatorClient`, FIXTURE_CAPABILITY: capability };
    const php = spawn('php', ['-r', script], { env, stdio: ['ignore', 'pipe', 'pipe'] });
    let output = '', errors = ''; php.stdout.on('data', bytes => { output += bytes; }); php.stderr.on('data', bytes => { errors += bytes; });
    try {
      const status = await new Promise(resolve => php.once('exit', resolve)); assert.equal(status, 0); assert.equal(errors, '');
      const result = JSON.parse(output); assert.equal(result.safeError, true);
      if (phase === 'fast') assert.equal(result.accepted, true);
      else { assert.equal(result.accepted, false); assert.ok(result.elapsedMs >= 65, JSON.stringify(result)); assert.ok(result.elapsedMs < 250, JSON.stringify(result)); }
    } finally {
      for (const timer of timers) clearInterval(timer); for (const socket of sockets) socket.destroy(); await new Promise(resolve => server.close(resolve));
      if (php.exitCode === null && php.signalCode === null) php.kill('SIGKILL');
    }
  });
}

// A private ACK must bind the entire parent scope, even when its child
// capability and returned operationId/epoch are internally self-consistent.
for (const framework of frameworks) for (const variant of ['valid', 'wrong-epoch', 'same-operation', 'wrong-ack-operation', 'wrong-root']) {
  test(`${framework} continue ${variant} preserves the original parent scope`, async () => {
    const parent = { operationId: '11111111-1111-4111-8111-111111111111', rootOperationId: '11111111-1111-4111-8111-111111111111', instanceId: 'fixture-old', epoch: 1, nonce: 'a'.repeat(32) };
    const child = { ...parent, operationId: '22222222-2222-4222-8222-222222222222', instanceId: 'fixture-child', nonce: 'b'.repeat(32) };
    if (variant === 'wrong-epoch') child.epoch = 2;
    if (variant === 'same-operation') child.operationId = parent.operationId;
    if (variant === 'wrong-root') child.rootOperationId = '33333333-3333-4333-8333-333333333333';
    const capability = claims => `${Buffer.from(JSON.stringify(claims)).toString('base64url')}.${'a'.repeat(43)}`;
    const ack = { capability: capability(child), operationId: variant === 'wrong-ack-operation' ? '44444444-4444-4444-8444-444444444444' : child.operationId, epoch: child.epoch };
    const sockets = new Set(); let calls = 0;
    const server = http.createServer((request, response) => { ++calls; request.resume(); response.writeHead(200, { 'content-type': 'application/json' }).end(JSON.stringify(ack)); });
    server.on('connection', socket => { sockets.add(socket); socket.once('close', () => sockets.delete(socket)); socket.on('error', () => {}); });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const ns = namespace;
    const script = `require getenv('FIXTURE_CLIENT_FILE');$class=getenv('FIXTURE_CLIENT_CLASS');$client=$class::fromEnvironment();try{$client->call('continue',['capability'=>getenv('FIXTURE_CAPABILITY'),'targetId'=>'fixture-child'],1000);$ok=true;$safe=true;}catch(Throwable $error){$ok=false;$safe=$error->getMessage()==='Identity admission control is unavailable.';}echo json_encode(['accepted'=>$ok,'safeError'=>$safe]);`;
    const env = { ...process.env, IDENTITY_DEV_ADMISSION_ENVIRONMENT: 'xrugc-development', IDENTITY_DEV_ADMISSION_CONTROL_URL: `http://127.0.0.1:${server.address().port}/`,
      IDENTITY_DEV_ADMISSION_KEY: randomBytes(32).toString('base64url'), IDENTITY_DEV_ADMISSION_INSTANCE_ID: 'fixture-old', IDENTITY_DEV_ADMISSION_BOOT_ID: 'fixture-boot-0001',
      IDENTITY_DEV_ADMISSION_REVISION: 'a'.repeat(40), IDENTITY_DEV_ADMISSION_ROUTER_HASH: 'b'.repeat(64), IDENTITY_DEV_ADMISSION_GENERATION: 'legacy', IDENTITY_DEV_ADMISSION_CHILDREN: 'fixture-child',
      FIXTURE_CLIENT_FILE: clientFile,
      FIXTURE_CLIENT_CLASS: `${ns}\\components\\admission\\CoordinatorClient`, FIXTURE_CAPABILITY: capability(parent) };
    const php = spawn('php', ['-r', script], { env, stdio: ['ignore', 'pipe', 'pipe'] });
    let output = '', errors = ''; php.stdout.on('data', bytes => { output += bytes; }); php.stderr.on('data', bytes => { errors += bytes; });
    try {
      const status = await new Promise(resolve => php.once('exit', resolve)); assert.equal(status, 0); assert.equal(errors, '');
      const result = JSON.parse(output); assert.equal(result.safeError, true); assert.equal(result.accepted, variant === 'valid'); assert.equal(calls, 1);
    } finally { for (const socket of sockets) socket.destroy(); await new Promise(resolve => server.close(resolve)); if (php.exitCode === null && php.signalCode === null) php.kill('SIGKILL'); }
  });
}
