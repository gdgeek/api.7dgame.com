import http from "node:http";
import { timingSafeEqual } from "node:crypto";
import { once } from "node:events";
import { performance } from "node:perf_hooks";
import { HoldQueue } from "./hold-queue.mjs";

const errorCode = error => /^[A-Z][A-Z0-9_]{0,100}$/.test(error?.safeCode ?? error?.message ?? "") ? error.safeCode ?? error.message : "ADMISSION_OPERATION_FAILED";
const fail = code => { throw Object.assign(new Error(code), { safeCode: code }); };
const hopHeaders = new Set(["connection", "proxy-connection", "keep-alive", "proxy-authenticate", "proxy-authorization", "te", "trailer", "transfer-encoding", "upgrade"]);
export function filteredRawHeaders(rawHeaders) {
  const blocked = new Set(hopHeaders);
  for (let i = 0; i < rawHeaders.length; i += 2) if (rawHeaders[i].toLowerCase() === "connection") {
    for (const name of rawHeaders[i + 1].split(",")) blocked.add(name.trim().toLowerCase());
  }
  const output = [];
  for (let i = 0; i < rawHeaders.length; i += 2) {
    const name = rawHeaders[i].toLowerCase();
    if (blocked.has(name) || /^x-(?:identity-)?admission(?:-|$)/.test(name) || /^x-.*continu(?:ation|ed)/.test(name)
      || /^x-identity-(?:instance(?:-|$)|router(?:-|$)|generation$)/.test(name)) continue;
    output.push(rawHeaders[i], rawHeaders[i + 1]);
  }
  return output;
}
function respond(response, status, code) {
  if (response.destroyed || response.writableEnded) return;
  response.writeHead(status, { "content-type": "application/json", "cache-control": "no-store" });
  response.end(JSON.stringify({ code }));
}
async function listen(server, port) {
  if (!Number.isInteger(port) || port < 0 || port > 65535) fail("INVALID_LOOPBACK_PORT");
  server.listen(port, "127.0.0.1"); await once(server, "listening");
  return `http://127.0.0.1:${server.address().port}/`;
}
async function close(server) {
  server.closeAllConnections(); await new Promise((resolve, reject) => server.close(error => error ? reject(error) : resolve()));
}
function singleHeader(request, name) {
  const values = [];
  for (let i = 0; i < request.rawHeaders.length; i += 2) if (request.rawHeaders[i].toLowerCase() === name) values.push(request.rawHeaders[i + 1]);
  return values.length === 1 ? values[0] : null;
}
function authenticated(request, key) {
  if (!Buffer.isBuffer(key) || key.length < 32) return false;
  const actual = singleHeader(request, "authorization"), expected = `Bearer ${key.toString("base64url")}`;
  if (typeof actual !== "string" || actual.length !== expected.length) return false;
  return timingSafeEqual(Buffer.from(actual), Buffer.from(expected));
}
async function metadata(request) {
  let bytes = 0; const chunks = [];
  for await (const chunk of request) {
    bytes += chunk.length; if (bytes > 8192) fail("CONTROL_METADATA_BOUND_REACHED"); chunks.push(chunk);
  }
  try { return JSON.parse(Buffer.concat(chunks).toString() || "{}"); } catch { fail("INVALID_CONTROL_METADATA"); }
}

/** Loopback fixture/control building block, never 0.0.0.0 and no cloud CLI. */
export async function startControlServer({ coordinator, operatorKey, participantKeys, port = 0 }) {
  if (!Buffer.isBuffer(operatorKey) || operatorKey.length < 32 || !(participantKeys instanceof Map)) fail("CONTROL_KEYS_REQUIRED");
  const identities = new Map(coordinator.roster.map(item => [item.id, item]));
  if (participantKeys.size !== identities.size || [...identities.keys()].some(id => !Buffer.isBuffer(participantKeys.get(id)) || participantKeys.get(id).length < 32)
    || new Set([operatorKey, ...participantKeys.values()].map(key => key.toString("hex"))).size !== identities.size + 1) fail("DISTINCT_BOOT_SCOPED_CONTROL_KEYS_REQUIRED");
  const server = http.createServer({ maxHeaderSize: 8192 }, async (request, response) => {
    try {
      const id = singleHeader(request, "x-identity-instance-id");
      const operator = !id && authenticated(request, operatorKey);
      const participant = id && identities.has(id) && authenticated(request, participantKeys.get(id));
      if (!operator && !participant) return respond(response, 401, "CONTROL_AUTHENTICATION_REQUIRED");
      const identity = identities.get(id);
      if (participant) {
        const reported = { ...identity, bootId: singleHeader(request, "x-identity-instance-boot-id"), revision: singleHeader(request, "x-identity-instance-revision"),
          routerHash: singleHeader(request, "x-identity-router-hash"), generation: singleHeader(request, "x-identity-generation") };
        if (["bootId", "revision", "routerHash", "generation"].some(key => reported[key] !== identity[key])) await coordinator.register(reported);
      }
      if (request.method !== "POST" || !/^\/[a-z-]+$/.test(request.url)) return respond(response, 404, "CONTROL_OPERATION_NOT_FOUND");
      const payload = await metadata(request), action = request.url.slice(1); let result;
      if (action === "status") result = coordinator.status();
      else if (participant) {
        switch (action) {
          case "register":
            if (payload.identity?.id !== identity.id) fail("REGISTERED_IDENTITY_MISMATCH");
            result = await coordinator.register(payload.identity); break;
          case "begin": result = await coordinator.beginRoot(identity); break;
          case "validate": result = await coordinator.validateOperation(identity, payload.capability); break;
          case "enter": result = await coordinator.enterOperation(identity, payload.capability); break;
          case "continue": result = await coordinator.continueOperation(identity, payload.capability, payload.targetId); break;
          case "complete": result = await coordinator.complete(identity, payload.capability, payload.evidence); break;
          case "unknown": result = await coordinator.markOperationUnknown(payload.capability); break;
          case "lost": result = await coordinator.markInstanceLost(identity); break;
          case "close-ack": result = await coordinator.acknowledgeClose(identity, payload); break;
          case "warm-ack": result = await coordinator.acknowledgeWarm(identity, payload); break;
          default: fail("CONTROL_OPERATION_NOT_AUTHORIZED");
        }
      } else {
        switch (action) {
          case "close": result = await coordinator.close(); break;
          case "cancel": result = await coordinator.cancel(); break;
          case "prepare": result = await coordinator.prepare(payload); break;
          case "start-commit": result = await coordinator.startCommit(payload); break;
          case "commit-unknown": result = await coordinator.commitUnknown(); break;
          case "confirm-identity": result = await coordinator.confirmIdentity(payload); break;
          case "router-ack": result = await coordinator.acknowledgeRouter(payload.identity, payload); break;
          case "drain-evidence": result = coordinator.drainEvidence(); break;
          case "observe-unreviewed": result = await coordinator.observeUnreviewedInstance(payload.id); break;
          default: fail("CONTROL_OPERATION_NOT_FOUND");
        }
      }
      response.writeHead(200, { "content-type": "application/json", "cache-control": "no-store" }); response.end(JSON.stringify(result ?? { accepted: true }));
    } catch (error) { respond(response, errorCode(error) === "CONTROL_METADATA_BOUND_REACHED" ? 413 : 409, errorCode(error)); }
  });
  server.requestTimeout = 2000;
  return { url: await listen(server, port), close: () => close(server) };
}

/** All routes are gated in the fixture. Actual development edge coverage remains an external prerequisite. */
export async function startFixtureGateway({ coordinator, legacy, identity, queueOptions = {}, port = 0 }) {
  const targets = { legacy, identity };
  for (const target of Object.values(targets)) {
    const url = new URL(target.url);
    if (url.protocol !== "http:" || url.hostname !== "127.0.0.1" || url.username || url.password || url.pathname !== "/" || url.search || url.hash) fail("LOOPBACK_FIXTURE_UPSTREAM_REQUIRED");
    target.parsed = url;
  }
  let queue;
  const cancel = async () => {
    const phase = coordinator.status().phase;
    if (["closing", "drained", "prepared"].includes(phase)) return coordinator.cancel("REQUEST_QUEUE_BUDGET_EXHAUSTED");
    fail("CANCEL_FORBIDDEN_AFTER_COMMIT_INTENT");
  };
  queue = new HoldQueue({ ...queueOptions, onExpire: cancel });
  // A single pipelined TCP connection can otherwise enqueue arbitrarily many
  // request handlers while the serialized durable admission decision is pending.
  const maxPendingRequests = coordinator.maxInflight + queue.maxRequests;
  const pendingRequests = new Map(); let peakPendingRequests = 0, rejectedRequests = 0;
  const arrivalClock = queueOptions.now ?? (() => performance.now());
  const stateListener = status => {
    if (!status.unknownSealed && status.phase === "legacy_open") queue.release("legacy");
    if (!status.unknownSealed && status.phase === "identity_open") queue.release("identity");
  };
  coordinator.on("state", stateListener);
  const removeCheck = coordinator.addCommitCheck(() => {
    queue.assertCommitBudget();
    // Includes headers already received but still awaiting the serialized gate.
    // They cannot disappear from the earliest-client budget before queue.add().
    if ([...pendingRequests.values()].some(item => !item.forwarded && item.receivedAt + queue.holdMs - arrivalClock() <= queue.minCommitRemainingMs)) fail("FIRST_REQUEST_ABSOLUTE_BUDGET_UNAVAILABLE");
  });
  async function forward(request, response, generation, chunks, lease) {
    request.pause();
    if (request.aborted || response.destroyed) { await coordinator.markOperationUnknown(lease.capability); return; }
    const target = targets[generation];
    await coordinator.markForwarded(lease.capability);
    const pending = pendingRequests.get(request); if (pending) pending.forwarded = true;
    const headers = [...filteredRawHeaders(request.rawHeaders), "x-identity-admission-lease", lease.capability];
    const outgoing = http.request({ hostname: target.parsed.hostname, port: target.parsed.port, path: request.url, method: request.method, headers });
    let transportUnknown = false;
    const unknown = () => { if (!transportUnknown) { transportUnknown = true; coordinator.markOperationUnknown(lease.capability).catch(() => {}); } };
    outgoing.on("error", () => { unknown(); respond(response, 502, "FORWARD_RESULT_UNKNOWN"); });
    request.on("aborted", () => { if (!request.complete) { unknown(); outgoing.destroy(); } });
    outgoing.on("response", upstream => {
      if (response.destroyed) { upstream.resume(); return; }
      response.writeHead(upstream.statusCode, filteredRawHeaders(upstream.rawHeaders));
      upstream.on("error", unknown);
      // Neither response finish/close nor upstream end is an operation completion.
      response.on("error", () => {});
      upstream.pipe(response);
    });
    for (const chunk of chunks) if (!outgoing.write(chunk)) await once(outgoing, "drain");
    if (request.readableEnded) outgoing.end(); else request.pipe(outgoing);
    request.resume();
  }
  async function dispatch(request, response, receivedAt, buffered = []) {
    request.pause();
    try {
      if (request.aborted || response.destroyed) return;
      if (typeof request.url !== "string" || !request.url.startsWith("/") || /[\r\n\u0000]/.test(request.url)) fail("INVALID_ORIGINAL_REQUEST_TARGET");
      // One serialized decision both reads the gate and admits the operation.
      // Reheld requests retain their original absolute arrival budget and bytes.
      const admission = await coordinator.admitIngress(legacy.identity, identity.identity);
      if (!admission.hold) { await forward(request, response, admission.generation, buffered, admission.lease); return; }
      let entry, released = false;
      const onFailure = code => { if (!released) { released = true; request.pause(); request.removeListener("data", data); respond(response, 504, code); } };
      const onRelease = async (_generation, chunks) => {
        if (released || request.aborted) return;
        released = true; request.pause(); request.removeListener("data", data); request.removeListener("end", end);
        await dispatch(request, response, receivedAt, chunks);
      };
      const overflow = async () => {
        try { await cancel(); stateListener(coordinator.status()); }
        catch { if (entry) queue.fail(entry.id, "POSTCOMMIT_QUEUE_CAPACITY_REACHED"); else onFailure("POSTCOMMIT_QUEUE_CAPACITY_REACHED"); }
      };
      const data = chunk => {
        try { entry.append(chunk); }
        catch {
          // Put the one parser chunk back; it is streamed once after precommit cancel,
          // rather than exceeding the queue body/byte bound or losing original bytes.
          request.pause(); request.unshift(chunk); void overflow();
        }
      };
      const end = () => entry.seal();
      try {
        entry = queue.add({ headerBytes: Buffer.byteLength(request.method + request.url + request.rawHeaders.join("\r\n")), receivedAt, onRelease, onFailure });
        for (const chunk of buffered) entry.append(chunk);
        if (request.readableEnded) entry.seal();
      } catch {
        if (entry) entry.abort();
        await cancel(); await dispatch(request, response, receivedAt, buffered); return;
      }
      request.on("data", data); request.on("end", end);
      request.on("aborted", () => { entry.abort(); });
      response.on("close", () => { if (!released) { released = true; entry.abort(); } });
      stateListener(coordinator.status());
      if (!released) request.resume();
    } catch (error) { respond(response, 503, errorCode(error)); }
  }
  const server = http.createServer({ maxHeaderSize: 16384, highWaterMark: 16384 }, (request, response) => {
    if (pendingRequests.size >= maxPendingRequests) {
      // This is a transport overload failure, never a normal cutover release or
      // a reason to forward an already attempted request to another generation.
      rejectedRequests++; request.socket.destroy(); return;
    }
    const receivedAt = arrivalClock(); pendingRequests.set(request, { receivedAt, forwarded: false }); peakPendingRequests = Math.max(peakPendingRequests, pendingRequests.size);
    let accounted = true;
    const finished = () => { if (accounted) { accounted = false; pendingRequests.delete(request); } };
    response.once("finish", finished); response.once("close", finished);
    void dispatch(request, response, receivedAt);
  });
  server.maxConnections = coordinator.maxInflight;
  server.requestTimeout = 5000;
  return { url: await listen(server, port), status: () => ({ coordinator: coordinator.status(), queue: queue.status(),
    transport: { pendingRequests: pendingRequests.size, peakPendingRequests, maxPendingRequests, rejectedRequests } }),
    async close() { coordinator.removeListener("state", stateListener); removeCheck(); queue.close(); await close(server); } };
}
