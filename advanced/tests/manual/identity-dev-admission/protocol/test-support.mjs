import { randomBytes } from "node:crypto";
import { AdmissionCoordinator, digest } from "./coordinator.mjs";
import { memoryFixtureJournal } from "./journal.mjs";
export const hash = letter => letter.repeat(64);
export function fixture(overrides = {}) {
  const member = (id, generation, allowedChildren = [], session = false) => ({ id, bootId: `${id}-boot-0001`, revision: "a".repeat(40), routerHash: hash("b"), generation, requiresSessionClose: session, allowedChildren });
  const roster = [member("php-old", "legacy", ["identity-old"], true), member("identity-old", "legacy", ["provider-old"]), member("provider-old", "legacy"),
    member("php-new", "identity", ["identity-new"], true), member("identity-new", "identity")];
  const routers = ["edge-b0", "edge-b1"].map(id => ({ id, bootId: `${id}-boot-0001`, routerHash: hash("c") }));
  const journal = memoryFixtureJournal(), key = randomBytes(32);
  const coordinator = new AdmissionCoordinator({ roster, routers, identityInstanceId: "identity-new", journal, capabilityKey: key, ...overrides });
  return { coordinator, roster, routers, journal, key, old: roster[0], green: roster[3] };
}
export async function registerAll(f) { for (const item of f.roster) await f.coordinator.register(item); }
export async function acknowledgeAll(f) {
  for (const identity of f.roster.filter(item => item.generation === "legacy")) {
    const status = f.coordinator.status(), row = status.instances.find(item => item.id === identity.id);
    await f.coordinator.acknowledgeClose(identity, { epoch: status.epoch, lastBegin: row.lastBegin, lastEnd: row.lastEnd });
  }
}
export async function prepare(f) {
  if (f.coordinator.status().phase === "legacy_open") await f.coordinator.close();
  if (f.coordinator.status().phase === "closing") await acknowledgeAll(f);
  const drain = f.coordinator.drainEvidence(), proof = { epoch: drain.epoch, drainSha256: digest(drain), sourceSnapshotSha256: hash("d"), syncReportSha256: hash("e"), allocatorProofSha256: hash("f"), identityRevision: "a".repeat(40) };
  for (const item of f.roster.filter(item => item.generation === "identity")) await f.coordinator.acknowledgeWarm(item, { epoch: drain.epoch, allocatorProofSha256: proof.allocatorProofSha256 });
  await f.coordinator.prepare(proof); return proof;
}
export function receipt(request) { return { allocatorManifestSha256: request.expectedProofSha256, compiledRevision: request.expectedRevision,
  requestSha256: request.requestSha256, deadline: request.deadline, decisionAt: new Date(Date.parse(request.deadline) - 1).toISOString() }; }
export const completion = { handlerFinished: true, sideEffectsFinished: true, sessionWriteClosed: true };
export function deferred() { let resolve; const promise = new Promise(done => { resolve = done; }); return { promise, resolve }; }
export async function until(check, timeout = 2000) {
  const end = Date.now() + timeout;
  while (!check()) { if (Date.now() >= end) throw new Error("FIXTURE_WAIT_EXPIRED"); await new Promise(resolve => setTimeout(resolve, 5)); }
}
