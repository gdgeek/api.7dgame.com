import test from 'node:test';
import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { spawn } from 'node:child_process';
import { chown, copyFile, mkdtemp, mkdir, readFile, readdir, rm, writeFile } from 'node:fs/promises';
import { createConnection, createServer } from 'node:net';
import http from 'node:http';
import { frameworks, applicationRoot, namespace, vendor } from './fixture-config.mjs';
import { loadProtocolFixture } from './protocol-fixture.mjs';
const { fixture, registerAll, until, startControlServer } = await loadProtocolFixture();
const fixtureSource = new URL('./fixture.php', import.meta.url);
async function unusedPort() {
  const server = createServer(); await new Promise(resolve => server.listen(0, '127.0.0.1', resolve)); const port = server.address().port;
  await new Promise(resolve => server.close(resolve)); return port;
}
async function trace(file) { try { return await readFile(file, 'utf8'); } catch { return ''; } }
async function untilTrace(file, text) {
  const end = Date.now() + 3000;
  while (!(await trace(file)).includes(text)) { if (Date.now() >= end) throw new Error('Fixture trace timed out.'); await new Promise(resolve => setTimeout(resolve, 5)); }
}
async function runFixture(framework, mode, enabled, check, sapi = 'cli-server', options = {}) {
  const f = fixture(); await registerAll(f);
  const keys = new Map(f.roster.map(item => [item.id, randomBytes(32)]));
  const control = await startControlServer({ coordinator: f.coordinator, operatorKey: randomBytes(32), participantKeys: keys });
  const counts = { childCalls: 0, proxyCalls: 0 }; let faultProxy; let controlUrl = control.url;
  if (options.controlFault || options.proxyEnvironment) {
    faultProxy = http.createServer(async (request, response) => {
      response.on('error', () => {}); const action = request.url.slice(1); counts[action] = (counts[action] ?? 0) + 1;
      if (options.proxyEnvironment) { ++counts.proxyCalls; response.writeHead(500).end(); request.resume(); return; }
      try {
        const chunks = []; for await (const chunk of request) chunks.push(Buffer.from(chunk));
        if (action === 'enter' && ['empty-enter', 'auth-refusal-enter'].includes(options.controlFault)) {
          response.writeHead(options.controlFault === 'empty-enter' ? 200 : 401, { 'content-type': 'application/json' }).end(JSON.stringify(options.controlFault === 'empty-enter' ? {} : { code: 'CONTROL_AUTHENTICATION_REQUIRED' })); return;
        }
        const upstream = await fetch(new URL(action, control.url), { method: 'POST', headers: request.headers, body: Buffer.concat(chunks) });
        let body = await upstream.text(), status = upstream.status;
        if (action === 'enter' && options.controlFault === 'wrong-enter') { const value = JSON.parse(body); value.operationId = '11111111-1111-4111-8111-111111111111'; body = JSON.stringify(value); }
        if (action === 'validate' && options.controlFault === 'wrong-validate') { const value = JSON.parse(body); value.rootOperationId = '11111111-1111-4111-8111-111111111111'; body = JSON.stringify(value); }
        if (action === 'continue' && ['wrong-child-epoch', 'child-is-parent', 'wrong-child-id'].includes(options.controlFault)) {
          const value = JSON.parse(body), input = JSON.parse(Buffer.concat(chunks).toString()), segments = value.capability.split('.');
          const claims = JSON.parse(Buffer.from(segments[0], 'base64url'));
          if (options.controlFault === 'wrong-child-epoch') { ++claims.epoch; value.epoch = claims.epoch; }
          if (options.controlFault === 'child-is-parent') { claims.operationId = JSON.parse(Buffer.from(input.capability.split('.')[0], 'base64url')).operationId; value.operationId = claims.operationId; }
          if (options.controlFault === 'wrong-child-id') value.operationId = '11111111-1111-4111-8111-111111111111';
          value.capability = `${Buffer.from(JSON.stringify(claims)).toString('base64url')}.${segments[1]}`; body = JSON.stringify(value);
        }
        if (action === 'complete' && options.controlFault === 'empty-complete') body = '{}';
        if (action === 'complete' && ['500-complete', 'journal-complete'].includes(options.controlFault)) { status = options.controlFault === '500-complete' ? 500 : 409; body = JSON.stringify({ code: 'JOURNAL_RESULT_UNKNOWN_SEALED' }); }
        if (action === 'enter' && options.controlFault === 'journal-enter') { status = 409; body = JSON.stringify({ code: 'JOURNAL_RESULT_UNKNOWN_SEALED' }); }
        if ((action === 'enter' && options.controlFault === 'slow-enter') || (action === 'continue' && options.controlFault === 'slow-continue') || (['unknown', 'lost'].includes(action) && options.controlFault === 'slow-safety')) await new Promise(resolve => setTimeout(resolve, 220));
        response.writeHead(status, { 'content-type': 'application/json' }).end(body);
      } catch { response.destroy(); }
    });
    await new Promise(resolve => faultProxy.listen(0, '127.0.0.1', resolve));
    if (!options.proxyEnvironment) controlUrl = `http://127.0.0.1:${faultProxy.address().port}/`;
  }

  const childServer = http.createServer(async (request, response) => {
    ++counts.childCalls;
    const identity = f.roster.find(row => row.id === 'identity-old'), capability = request.headers['x-identity-admission-lease'];
    await f.coordinator.enterOperation(identity, capability); response.end('child-http-response');
    if (mode === 'child-late') {
      await new Promise(resolve => setTimeout(resolve, 200));
      await f.coordinator.complete(identity, capability, { handlerFinished: true, sideEffectsFinished: true, sessionWriteClosed: true });
    }
  });
  await new Promise(resolve => childServer.listen(0, '127.0.0.1', resolve));
  const base = await mkdtemp('/tmp/identity-yii-admission-'); const traceFile = `${base}/trace`; await mkdir(`${base}/sessions`, { mode: 0o700 });
  await copyFile(fixtureSource, `${base}/index.php`);
  const root = applicationRoot;
  const runnerFile = framework === 'server' ? `${root}/common/components/admission/HttpAdmission.php` : `${root}/components/admission/HttpAdmission.php`;
  const port = await unusedPort();
  const env = { ...process.env, IDENTITY_DEV_ADMISSION_ENABLED: enabled ? 'true' : 'false', IDENTITY_DEV_ADMISSION_ENVIRONMENT: 'xrugc-development',
    IDENTITY_DEV_ADMISSION_CONTROL_URL: controlUrl, IDENTITY_DEV_ADMISSION_KEY: keys.get(f.old.id).toString('base64url'),
    IDENTITY_DEV_ADMISSION_INSTANCE_ID: f.old.id, IDENTITY_DEV_ADMISSION_BOOT_ID: f.old.bootId, IDENTITY_DEV_ADMISSION_REVISION: f.old.revision,
    IDENTITY_DEV_ADMISSION_ROUTER_HASH: f.old.routerHash, IDENTITY_DEV_ADMISSION_GENERATION: 'legacy', IDENTITY_DEV_ADMISSION_CHILDREN: f.old.allowedChildren.join(','),
    ADMISSION_FIXTURE_RUNNER_FILE: runnerFile, ADMISSION_FIXTURE_RUNNER_CLASS: `${namespace}\\components\\admission\\HttpAdmission`,
    ADMISSION_FIXTURE_VENDOR: vendor, ADMISSION_FIXTURE_BASE: base, ADMISSION_FIXTURE_TRACE: traceFile,
    ADMISSION_FIXTURE_CHILD_URL: `http://127.0.0.1:${childServer.address().port}/`,
  };
  if (options.requestBudgetMs) env.IDENTITY_DEV_ADMISSION_REQUEST_BUDGET_MS = String(options.requestBudgetMs);
  if (options.proxyEnvironment) { env.http_proxy = `http://127.0.0.1:${faultProxy.address().port}/`; env.no_proxy = ''; env.NO_PROXY = ''; }
  if (!enabled) { env.IDENTITY_DEV_ADMISSION_KEY = 'deliberately-invalid-disabled-key'; env.IDENTITY_DEV_ADMISSION_CONTROL_URL = 'not-a-url'; }
  let executable = 'php', arguments_ = ['-d', 'zend.exception_ignore_args=0', '-d', 'display_errors=0', '-d', 'log_errors=0', '-d', 'output_buffering=0', '-d', 'session.auto_start=0', '-S', `127.0.0.1:${port}`, '-t', base];
  if (sapi === 'apache2handler') {
    await chown(base, 33, 33); await chown(`${base}/sessions`, 33, 33);
    const configFile = `${base}/apache.conf`;
    await writeFile(configFile, `ServerRoot /etc/apache2
ServerName 127.0.0.1
PidFile ${base}/apache.pid
Listen 127.0.0.1:${port}
LoadModule mpm_prefork_module /usr/lib/apache2/modules/mod_mpm_prefork.so
LoadModule authz_core_module /usr/lib/apache2/modules/mod_authz_core.so
LoadModule dir_module /usr/lib/apache2/modules/mod_dir.so
LoadModule php_module /usr/lib/apache2/modules/libphp.so
User www-data
Group www-data
ErrorLog ${base}/apache-error.log
LogLevel warn
KeepAlive Off
DocumentRoot ${base}
<Directory ${base}>
Require all granted
DirectoryIndex index.php
<FilesMatch \"\\.php$\">
SetHandler application/x-httpd-php
</FilesMatch>
</Directory>
php_value zend.exception_ignore_args 0
php_admin_value display_errors 0
php_admin_value log_errors 0
php_admin_value output_buffering 0
php_admin_value session.auto_start 0
`, { mode: 0o600 });
    executable = 'apache2'; arguments_ = ['-X', '-f', configFile];
  }
  const php = spawn(executable, arguments_, { env, stdio: ['ignore', 'ignore', 'pipe'] });
  let listening = false, diagnostics = ''; php.stderr.on('data', data => { diagnostics = (diagnostics + data.toString()).slice(-8192); if (data.toString().includes('Development Server')) listening = true; });
  try {
    if (sapi === 'cli-server') await until(() => listening, 4000);
    else {
      const end = Date.now() + 4000;
      for (;;) {
        const available = await new Promise(resolve => { const socket = createConnection({ host: '127.0.0.1', port }); socket.once('connect', () => { socket.destroy(); resolve(true); }); socket.once('error', () => resolve(false)); });
        if (available) break; if (Date.now() >= end) throw new Error('Apache fixture did not listen.');
        await new Promise(resolve => setTimeout(resolve, 10));
      }
    }
    await check({ f, url: `http://127.0.0.1:${port}/index.php?case=${mode}`, traceFile, base, counts });
  } catch (error) {
    console.error(JSON.stringify({ fixture: framework, mode, events: await trace(traceFile), active: f.coordinator.status().active, unknown: f.coordinator.status().unknown, diagnostics, apacheLog: sapi === 'apache2handler' ? await trace(`${base}/apache-error.log`) : undefined }));
    throw error;
  } finally {
    if (php.exitCode === null && php.signalCode === null) { const exited = new Promise(resolve => php.once('exit', resolve)); php.kill('SIGTERM'); await exited; } childServer.closeAllConnections(); await new Promise(resolve => childServer.close(resolve));
    if (faultProxy) { faultProxy.closeAllConnections(); await new Promise(resolve => faultProxy.close(resolve)); }
    await control.close(); await rm(base, { recursive: true, force: true });
  }
}
async function persisted(base) {
  const names = await readdir(`${base}/sessions`); return (await Promise.all(names.map(name => readFile(`${base}/sessions/${name}`, 'utf8')))).join('\n');
}
for (const framework of frameworks) {
  test(`${framework} OFF preserves real Yii/session despite invalid admission configuration`, async () => runFixture(framework, 'normal', false, async ({ f, url, base }) => {
    const response = await fetch(url); assert.equal(response.status, 200); assert.equal(await response.text(), 'normal');
    assert.match(await persisted(base), /marker\|s:5:"saved"/); assert.equal(f.coordinator.status().active, 0);
    const disabledEvents = await trace(`${base}/trace`); assert.match(disabledEvents, /private-key-visible:1/); assert.match(disabledEvents, /exception-args-private:0/);
    assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastBegin, 0);
  }));
  for (const mode of ['normal', 'end', 'session-delay', 'shutdown-work', 'transaction-commit']) test(`${framework} ${mode} completes only after actual handler/finally/session writes`, async () => runFixture(framework, mode, true, async ({ f, url, traceFile, base }) => {
    let sawBody = false;
    const finished = new Promise((resolve, reject) => {
      http.get(url, response => { response.on('data', () => {
        if (!sawBody) { sawBody = true; if (mode === 'end' || mode === 'session-delay') assert.equal(f.coordinator.status().active, 1); }
      }); response.on('end', resolve); response.on('error', reject); }).on('error', reject);
    });
    await finished; await until(() => f.coordinator.status().active === 0 && f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd === 1);
    const events = await trace(traceFile), saved = await persisted(base);
    assert.match(events, /bootstrap-before-session:1/); assert.match(events, /private-key-visible:0/); assert.match(events, /exception-args-private:1/); assert.match(saved, /marker\|s:5:"saved"/);
    if (mode === 'end') { assert.ok(events.indexOf('response-sent') < events.indexOf('business-finally')); assert.match(saved, /finally\|s:5:"saved"/); }
    if (mode === 'session-delay') assert.match(events, /session-write-done/);
    if (mode === 'shutdown-work') assert.equal(await readFile(`${base}/shutdown-work`, 'utf8'), 'finished');
    assert.equal(f.coordinator.status().unknown, 0);
  }));
  for (const mode of ['exit', 'fatal', 'throw', 'swallowed-end', 'repeated-end', 'caught-work', 'transaction-open', 'custom-error', 'session-fail', 'shutdown-session']) test(`${framework} ${mode} remains unknown and never advances lastEnd`, async () => runFixture(framework, mode, true, async ({ f, url, traceFile }) => {
    try { const response = await fetch(url); await response.text(); } catch {}
    await until(() => f.coordinator.status().unknown > 0);
    assert.match(await trace(traceFile), /handler-start/);
    assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 0);
  }));
  test(`${framework} unsupported custom Yii error handler cannot bootstrap or complete`, async () => runFixture(framework, 'custom-yii-handler', true, async ({ f, url, traceFile, base }) => {
    const response = await fetch(url); await response.text(); await until(() => f.coordinator.status().unknown > 0);
    assert.equal(await trace(traceFile), ''); assert.equal(await persisted(base), ''); assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 0);
  }));
  test(`${framework} client disconnect does not release while real work/session remains pending`, async () => runFixture(framework, 'abort', true, async ({ f, url, traceFile, base }) => {
    const request = http.get(url); request.on('error', () => {});
    await untilTrace(traceFile, 'handler-start');
    request.destroy(); assert.equal(f.coordinator.status().active, 1);
    await until(() => f.coordinator.status().active === 0 && f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd === 1);
    assert.match(await trace(traceFile), /abort-work-done/); assert.match(await persisted(base), /marker\|s:5:"saved"/);
  }));
  test(`${framework} closed direct root cannot initialize Yii/bootstrap/session`, async () => runFixture(framework, 'normal', true, async ({ f, url, traceFile, base }) => {
    await f.coordinator.close(); try { const response = await fetch(url); await response.text(); } catch {}
    assert.equal(await trace(traceFile), ''); assert.equal(await persisted(base), ''); assert.equal(f.coordinator.status().active, 0); assert.equal(f.coordinator.status().unknown, 0);
  }));
  test(`${framework} actual gateway capability enters once without an extra root and cannot be replayed`, async () => runFixture(framework, 'normal', true, async ({ f, url, traceFile }) => {
    const lease = await f.coordinator.beginRoot(f.old); await f.coordinator.markForwarded(lease.capability);
    const headers = { 'X-Identity-Admission-Lease': lease.capability };
    const response = await fetch(url, { headers }); assert.equal(response.status, 200); await response.text();
    await until(() => f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd === 1);
    const before = await trace(traceFile);
    const replay = await fetch(url, { headers }); assert.notEqual(replay.status, 200); await replay.text();
    assert.equal(await trace(traceFile), before); assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastBegin, 1);
    assert.equal(f.coordinator.status().active, 0); assert.equal(f.coordinator.status().unknown, 0);
  }));
  test(`${framework} forged public capability cannot reach Yii or poison another root as unknown`, async () => runFixture(framework, 'normal', true, async ({ f, url, traceFile, base }) => {
    for (const capability of [`${Buffer.from('{}').toString('base64url')}.${'a'.repeat(43)}`, 'malformed']) {
      const response = await fetch(url, { headers: { 'X-Identity-Admission-Lease': capability } });
      assert.notEqual(response.status, 200); await response.text(); assert.equal(await trace(traceFile), ''); assert.equal(await persisted(base), '');
    }
    assert.equal(f.coordinator.status().active, 0); assert.equal(f.coordinator.status().unknown, 0); assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastBegin, 0);
  }));
  test(`${framework} successful child HTTP response does not release parent before actual child completion`, async () => runFixture(framework, 'child-late', true, async ({ f, url, traceFile }) => {
    const response = await fetch(url); await response.text(); await untilTrace(traceFile, 'child-http-response');
    if (f.coordinator.status().active > 0) assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 0);
    await until(() => f.coordinator.status().active === 0);
    assert.equal(f.coordinator.status().instances.find(row => row.id === 'identity-old').lastEnd, 1);
    assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 1); assert.equal(f.coordinator.status().unknown, 0);
  }));
  test(`${framework} lost completion ACK never retries or clears the permanent unknown latch`, async () => runFixture(framework, 'normal', true, async ({ f, url }) => {
    const complete = f.coordinator.complete.bind(f.coordinator); let completedCalls = 0;
    f.coordinator.complete = async (...args) => { ++completedCalls; await complete(...args); throw new Error('Synthetic post-commit ACK loss.'); };
    const response = await fetch(url); await response.text(); await until(() => f.coordinator.status().unknown > 0);
    assert.equal(completedCalls, 1); assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 1);
    await new Promise(resolve => setTimeout(resolve, 75)); assert.equal(completedCalls, 1); assert.ok(f.coordinator.status().unknown > 0);
  }));
  test(`${framework} child past original wait budget remains tracked and parent becomes unknown`, async () => runFixture(framework, 'child-hang', true, async ({ f, url, traceFile }) => {
    const response = await fetch(url); await response.text(); await untilTrace(traceFile, 'child-http-response');
    await until(() => f.coordinator.status().unknown > 0, 2500);
    assert.equal(f.coordinator.status().active, 1); assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 0);
    assert.equal(f.coordinator.status().instances.find(row => row.id === 'identity-old').lastEnd, 0);
  }));
}

for (const framework of frameworks) {
  test(`${framework} actual Apache OFF keeps original Yii and session with invalid private config`, async () => runFixture(framework, 'normal', false, async ({ f, url, base }) => {
    const response = await fetch(url); assert.equal(response.status, 200); assert.equal(await response.text(), 'normal');
    assert.match(await persisted(base), /marker\|s:5:"saved"/); assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastBegin, 0);
  }, 'apache2handler'));
  for (const mode of ['normal', 'end', 'session-delay', 'abort', 'child-late']) test(`${framework} actual Apache ${mode} waits real finally/session/children`, async () => runFixture(framework, mode, true, async ({ f, url, traceFile, base }) => {
    if (mode === 'abort') {
      const request = http.get(url); request.on('error', () => {}); await untilTrace(traceFile, 'handler-start'); request.destroy(); assert.equal(f.coordinator.status().active, 1);
    } else { const response = await fetch(url); assert.equal(response.status, 200); await response.text(); }
    await until(() => f.coordinator.status().active === 0 && f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd === 1);
    assert.match(await persisted(base), /marker\|s:5:"saved"/); assert.equal(f.coordinator.status().unknown, 0);
    const runtimeEvents = await trace(traceFile); assert.match(runtimeEvents, /private-key-visible:0/); assert.match(runtimeEvents, /exception-args-private:1/);
    if (mode === 'end') { const events = await trace(traceFile); assert.ok(events.indexOf('response-sent') < events.indexOf('business-finally')); }
    if (mode === 'child-late') assert.equal(f.coordinator.status().instances.find(row => row.id === 'identity-old').lastEnd, 1);
  }, 'apache2handler'));
  test(`${framework} actual Apache session failure cannot complete`, async () => runFixture(framework, 'session-fail', true, async ({ f, url, traceFile }) => {
    const response = await fetch(url); await response.text(); await until(() => f.coordinator.status().unknown > 0); assert.match(await trace(traceFile), /handler-start/);
    assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 0);
  }, 'apache2handler'));
}

// Fresh restoration/control fault acceptance: exercise both real SAPIs, never a mock Yii.
for (const framework of frameworks) for (const sapi of ['cli-server', 'apache2handler']) {
  for (const controlFault of ['empty-enter', 'wrong-enter', 'journal-enter', 'auth-refusal-enter', 'slow-enter']) test(`${framework} ${sapi} ${controlFault} cannot initialize Yii/session`, async () => runFixture(framework, 'normal', true, async ({ f, url, traceFile, base }) => {
    const response = await fetch(url); await response.text(); await until(() => f.coordinator.status().unknown > 0);
    assert.equal(await trace(traceFile), ''); assert.equal(await persisted(base), ''); assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 0);
  }, sapi, { controlFault, requestBudgetMs: controlFault === 'slow-enter' ? 160 : 1500 }));
  for (const controlFault of ['wrong-validate', 'empty-complete', '500-complete', 'journal-complete']) test(`${framework} ${sapi} ${controlFault} never clears unknown or retries complete`, async () => runFixture(framework, 'normal', true, async ({ f, url, traceFile, counts }) => {
    const response = await fetch(url); await response.text(); await until(() => f.coordinator.status().unknown > 0);
    assert.match(await trace(traceFile), /handler-start/); assert.equal(counts.complete ?? 0, controlFault === 'wrong-validate' ? 0 : 1);
    await new Promise(resolve => setTimeout(resolve, 25)); assert.ok(f.coordinator.status().unknown > 0);
  }, sapi, { controlFault }));
  test(`${framework} ${sapi} expired original budget issues no new child`, async () => runFixture(framework, 'child-before-expired', true, async ({ f, url, counts }) => {
    const response = await fetch(url); await response.text(); await until(() => f.coordinator.status().unknown > 0);
    assert.equal(counts.childCalls, 0); assert.equal(f.coordinator.status().instances.find(row => row.id === 'identity-old').lastBegin, 0);
  }, sapi, { requestBudgetMs: 140 }));
  test(`${framework} ${sapi} a late continue ACK cannot dispatch a child`, async () => runFixture(framework, 'child-late', true, async ({ f, url, counts }) => {
    const response = await fetch(url); await response.text(); await until(() => f.coordinator.status().unknown > 0);
    assert.equal(counts.continue, 1); assert.equal(counts.childCalls, 0); assert.equal(f.coordinator.status().instances.find(row => row.id === 'identity-old').lastEnd, 0);
  }, sapi, { controlFault: 'slow-continue', requestBudgetMs: 160 }));
  for (const controlFault of ['wrong-child-epoch', 'child-is-parent', 'wrong-child-id']) test(`${framework} ${sapi} ${controlFault} ACK cannot dispatch or complete a child`, async () => runFixture(framework, 'child-late', true, async ({ f, url, counts }) => {
    const response = await fetch(url); await response.text(); await until(() => f.coordinator.status().unknown > 0);
    assert.equal(counts.continue, 1); assert.equal(counts.childCalls, 0); assert.equal(counts.complete ?? 0, 0);
    assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 0);
    assert.equal(f.coordinator.status().instances.find(row => row.id === 'identity-old').lastEnd, 0);
    assert.equal(f.coordinator.status().instances.find(row => row.id === 'identity-old').lastBegin, 1);
    // Status counts unknown operations separately: parent is unknown, while
    // the actual allocated child remains active and is never inferred ended.
    assert.equal(f.coordinator.status().active, 1); assert.equal(f.coordinator.status().unknown, 1);
  }, sapi, { controlFault }));
  test(`${framework} ${sapi} completed long handler without a child can record actual completion`, async () => runFixture(framework, 'long-no-child', true, async ({ f, url }) => {
    const response = await fetch(url); assert.equal(response.status, 200); await response.text(); await until(() => f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd === 1);
    assert.equal(f.coordinator.status().active, 0); assert.equal(f.coordinator.status().unknown, 0);
  }, sapi, { requestBudgetMs: 140 }));
  test(`${framework} ${sapi} private loopback control never follows ambient HTTP proxy`, async () => runFixture(framework, 'normal', true, async ({ f, url, counts }) => {
    const response = await fetch(url); assert.equal(response.status, 200); await response.text(); await until(() => f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd === 1);
    assert.equal(counts.proxyCalls, 0); assert.equal(f.coordinator.status().unknown, 0);
  }, sapi, { proxyEnvironment: true }));
}

for (const framework of frameworks) for (const sapi of ['cli-server', 'apache2handler']) test(`${framework} ${sapi} unknown and lost share a bounded safety reporting tail`, async () => runFixture(framework, 'exit', true, async ({ f, url, counts }) => {
  const start = performance.now(); const response = await fetch(url); await response.text(); const elapsed = performance.now() - start;
  assert.equal(counts.unknown, 1); assert.equal(counts.lost, 1); assert.ok(elapsed < 650, `Local safety tail unexpectedly took ${elapsed}ms`);
  assert.ok(f.coordinator.status().unknown > 0); assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastEnd, 0);
}, sapi, { controlFault: 'slow-safety' }));

for (const framework of frameworks) for (const sapi of ['cli-server', 'apache2handler']) test(`${framework} ${sapi} forged capability with malformed private enter ACK cannot bootstrap`, async () => runFixture(framework, 'normal', true, async ({ f, url, traceFile, base }) => {
  const response = await fetch(url, { headers: { 'X-Identity-Admission-Lease': `${Buffer.from('{}').toString('base64url')}.${'a'.repeat(43)}` } }); await response.text();
  await until(() => f.coordinator.status().unknown > 0); assert.equal(await trace(traceFile), ''); assert.equal(await persisted(base), '');
  assert.equal(f.coordinator.status().instances.find(row => row.id === f.old.id).lastBegin, 0);
}, sapi, { controlFault: 'empty-enter' }));
