import { createHash, createHmac, randomBytes, randomUUID, timingSafeEqual } from "node:crypto";
import { EventEmitter } from "node:events";

const digestPattern = /^[a-f0-9]{64}$/;
const revisionPattern = /^[a-f0-9]{40}$/;
const identifierPattern = /^[a-z][a-z0-9_-]{0,63}$/;
const bootPattern = /^[A-Za-z0-9_-]{8,80}$/;
const fail = code => { throw Object.assign(new Error(code), { safeCode: code }); };
const canonical = value => value === null || typeof value !== "object" ? JSON.stringify(value)
  : Array.isArray(value) ? `[${value.map(canonical).join(",")}]`
  : `{${Object.keys(value).sort().map(key => `${JSON.stringify(key)}:${canonical(value[key])}`).join(",")}}`;
export const digest = value => createHash("sha256").update(canonical(value)).digest("hex");
const isoDate = value => {
  const date = new Date(value);
  if (typeof value !== "string" || !/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d\.\d{3}Z$/.test(value)
    || !Number.isFinite(date.getTime()) || date.toISOString() !== value) fail("INVALID_ABSOLUTE_DEADLINE");
  return date.getTime();
};

/** Protocol core only. The real framework/route/CLI adapters are deliberately not installed here. */
export class AdmissionCoordinator extends EventEmitter {
  #pending = Promise.resolve(); #pendingCount = 0; #instances = new Map(); #operations = new Map(); #completed = new Map();
  #unknownInstances = new Set(); #routerAcks = new Set(); #journal; #key; #now; #checks = new Set();
  #phase = "legacy_open"; #epoch = 1; #recovery; #drain; #prepared; #commit; #authorityConfirmed = false;
  constructor({ roster, routers, identityInstanceId, journal, capabilityKey = randomBytes(32), now = Date.now, maxInflight = 1024, maxDepth = 8, maxPendingControls = 4096 }) {
    super();
    if (!Array.isArray(roster) || !roster.length || roster.length > 128 || !Array.isArray(routers) || !routers.length || routers.length > 32
      || !journal?.append || !Buffer.isBuffer(capabilityKey) || capabilityKey.length < 32
      || !Number.isSafeInteger(maxInflight) || maxInflight < 1 || maxInflight > 4096 || !Number.isSafeInteger(maxDepth) || maxDepth < 1 || maxDepth > 16
      || !Number.isSafeInteger(maxPendingControls) || maxPendingControls < 1 || maxPendingControls > 8192) fail("INVALID_COORDINATOR_CONFIGURATION");
    this.#journal = journal; this.#key = Buffer.from(capabilityKey); this.#now = now; this.maxInflight = maxInflight; this.maxDepth = maxDepth; this.maxPendingControls = maxPendingControls;
    this.#recovery = journal.recoveryRequired === true;
    this.roster = Object.freeze(roster.map(item => {
      if (Object.keys(item).sort().join(",") !== ["id", "bootId", "revision", "routerHash", "generation", "requiresSessionClose", "allowedChildren"].sort().join(",")
        || !identifierPattern.test(item.id) || !bootPattern.test(item.bootId) || !revisionPattern.test(item.revision) || !digestPattern.test(item.routerHash)
        || !["legacy", "identity"].includes(item.generation) || typeof item.requiresSessionClose !== "boolean" || !Array.isArray(item.allowedChildren)) fail("INVALID_EXPECTED_INSTANCE");
      const result = Object.freeze({ ...item, allowedChildren: Object.freeze([...item.allowedChildren]) });
      if (this.#instances.has(item.id)) fail("DUPLICATE_EXPECTED_INSTANCE");
      this.#instances.set(item.id, { expected: result, registered: false, lost: false, begin: 0, end: 0, ack: null, warm: null });
      return result;
    }));
    if (!["legacy", "identity"].every(generation => this.roster.some(item => item.generation === generation))
      || this.roster.some(item => item.allowedChildren.some(id => !this.#instances.has(id)))) fail("INCOMPLETE_EXPECTED_GENERATIONS");
    this.identityRevision = this.#instances.get(identityInstanceId)?.expected;
    if (this.identityRevision?.generation !== "identity") fail("REVIEWED_IDENTITY_INSTANCE_REQUIRED");
    this.identityRevision = this.identityRevision.revision;
    this.routers = Object.freeze(routers.map(item => {
      if (Object.keys(item).sort().join(",") !== ["id", "bootId", "routerHash"].sort().join(",") || !identifierPattern.test(item.id) || !bootPattern.test(item.bootId) || !digestPattern.test(item.routerHash)) fail("INVALID_EXPECTED_ROUTER");
      return Object.freeze({ ...item });
    }));
    if (new Set(this.routers.map(item => item.id)).size !== this.routers.length) fail("DUPLICATE_EXPECTED_ROUTER");
    this.rosterSha256 = digest({ environment: "xrugc-development", roster: this.roster, routers: this.routers });
    for (const key of ["roster", "routers", "identityRevision", "rosterSha256", "maxInflight", "maxDepth", "maxPendingControls"]) {
      Object.defineProperty(this, key, { writable: false, configurable: false });
    }
  }
  #serial(action) {
    if (this.#pendingCount >= this.maxPendingControls) return Promise.reject(Object.assign(new Error("PENDING_CONTROL_BOUND_REACHED"), { safeCode: "PENDING_CONTROL_BOUND_REACHED" }));
    this.#pendingCount++;
    const result = this.#pending.then(action).finally(() => { this.#pendingCount--; });
    this.#pending = result.catch(() => {});
    return result;
  }
  #assertKnown() { if (this.#recovery) fail("COORDINATOR_RECOVERY_UNKNOWN_SEALED"); }
  async #record(event, extra = {}) {
    try { await this.#journal.append({ event, phase: this.#phase, epoch: this.#epoch, at: new Date(this.#now()).toISOString(), rosterSha256: this.rosterSha256, ...extra }); }
    catch { this.#recovery = true; this.#emit(); fail("JOURNAL_RESULT_UNKNOWN_SEALED"); }
  }
  #identity(identity, registered = true) {
    const item = this.#instances.get(identity?.id);
    if (!item || ["bootId", "revision", "routerHash", "generation"].some(key => item.expected[key] !== identity[key])) fail("UNREVIEWED_INSTANCE_OR_BOOT");
    if (item.lost) fail("INSTANCE_LOST_REQUIRES_REVIEW");
    if (registered && !item.registered) fail("INSTANCE_NOT_REGISTERED");
    return item;
  }
  #emit() { this.emit("state", this.status()); }
  status() {
    return { phase: this.#phase, epoch: this.#epoch, unknownSealed: this.#recovery, rosterSha256: this.rosterSha256, pendingControls: this.#pendingCount,
      active: [...this.#operations.values()].filter(op => !op.unknown).length,
      unknown: [...this.#operations.values()].filter(op => op.unknown).length + this.#unknownInstances.size,
      authorityConfirmed: this.#authorityConfirmed,
      instances: [...this.#instances.values()].map(item => ({ id: item.expected.id, bootId: item.expected.bootId, generation: item.expected.generation,
        registered: item.registered, lost: item.lost, lastBegin: item.begin, lastEnd: item.end, closeAcknowledged: Boolean(item.ack), warmed: Boolean(item.warm) })),
      drainSha256: this.#drain ? digest(this.#drain) : null, preparedSha256: this.#prepared ? digest(this.#prepared) : null };
  }
  addCommitCheck(check) { this.#checks.add(check); return () => this.#checks.delete(check); }
  #latchUnknown(id) {
    // Safety observations must be visible even while an earlier fsync is
    // pending. The serialized path below still records and invalidates proof.
    if (!identifierPattern.test(id) || this.#unknownInstances.size >= 128 && !this.#unknownInstances.has(id)) { this.#recovery = true; this.#emit(); return; }
    this.#unknownInstances.add(id);
  }
  async #retainUnknownInstance(id) {
    if (!identifierPattern.test(id) || this.#unknownInstances.size >= 128 && !this.#unknownInstances.has(id)) fail("UNKNOWN_INSTANCE_BOUND_REACHED");
    this.#unknownInstances.add(id);
    const known = this.#instances.get(id); if (known) { known.lost = true; known.ack = null; }
    for (const op of this.#operations.values()) if (op.instanceId === id) op.unknown = true;
    if (["drained", "prepared"].includes(this.#phase)) { this.#phase = "closing"; this.#drain = null; this.#prepared = null; }
    await this.#record("unreviewed_instance", { instanceId: id }); this.#emit();
  }
  observeUnreviewedInstance(id) { this.#latchUnknown(id); return this.#serial(async () => { this.#assertKnown(); await this.#retainUnknownInstance(id); return this.status(); }); }
  register(identity) {
    const expected = this.#instances.get(identity?.id)?.expected;
    if (!expected || ["bootId", "revision", "routerHash", "generation"].some(key => expected[key] !== identity[key])) this.#latchUnknown(identity?.id);
    return this.#serial(async () => {
    this.#assertKnown(); let item;
    try { item = this.#identity(identity, false); }
    catch (error) {
      if (error.safeCode === "UNREVIEWED_INSTANCE_OR_BOOT" && identifierPattern.test(identity?.id)) await this.#retainUnknownInstance(identity.id);
      throw error;
    }
    if (!item.registered) { await this.#record("instance_registered", { instanceId: item.expected.id }); item.registered = true; }
    return this.status();
  }); }
  #allRegistered(generation) { return this.roster.filter(item => item.generation === generation).every(item => { const actual = this.#instances.get(item.id); return actual.registered && !actual.lost; }); }
  #capability(op) {
    const body = Buffer.from(canonical({ operationId: op.id, rootOperationId: op.rootId, instanceId: op.instanceId, epoch: op.epoch, nonce: op.nonce })).toString("base64url");
    return `${body}.${createHmac("sha256", this.#key).update(body).digest("base64url")}`;
  }
  #operation(capability, allowCompleted = false) {
    if (typeof capability !== "string" || capability.length > 2048) fail("INVALID_OPERATION_CAPABILITY");
    const pieces = capability.split(".");
    if (pieces.length !== 2 || !/^[A-Za-z0-9_-]+$/.test(pieces[0]) || !/^[A-Za-z0-9_-]{43}$/.test(pieces[1])) fail("INVALID_OPERATION_CAPABILITY");
    const signature = createHmac("sha256", this.#key).update(pieces[0]).digest();
    const actual = Buffer.from(pieces[1], "base64url");
    if (actual.length !== signature.length || !timingSafeEqual(actual, signature)) fail("INVALID_OPERATION_CAPABILITY");
    let body; try { body = JSON.parse(Buffer.from(pieces[0], "base64url").toString()); } catch { fail("INVALID_OPERATION_CAPABILITY"); }
    const operation = this.#operations.get(body.operationId) ?? (allowCompleted ? this.#completed.get(body.operationId) : null);
    if (!operation || operation.capability !== capability) fail("OPERATION_NOT_ACTIVE");
    return operation;
  }
  async #begin(item, parent = null) {
    if (this.#operations.size >= this.maxInflight) fail("MAX_INFLIGHT_REACHED");
    if (parent && parent.depth + 1 > this.maxDepth) fail("CONTINUATION_DEPTH_REACHED");
    const id = randomUUID(), op = { id, rootId: parent?.rootId ?? id, parentId: parent?.id ?? null,
      instanceId: item.expected.id, epoch: parent?.epoch ?? this.#epoch, nonce: randomBytes(16).toString("hex"), depth: parent ? parent.depth + 1 : 0,
      children: new Set(), forwarded: false, entered: false, unknown: false };
    op.capability = this.#capability(op);
    await this.#record("operation_begin", { operationId: id, instanceId: item.expected.id });
    this.#operations.set(id, op); if (parent) parent.children.add(id); item.begin++; item.ack = null;
    return { capability: op.capability, operationId: id, epoch: op.epoch };
  }
  beginRoot(identity) { return this.#serial(async () => {
    this.#assertKnown(); const item = this.#identity(identity);
    const generation = this.#phase === "legacy_open" ? "legacy" : this.#phase === "identity_open" ? "identity" : null;
    if (!generation) fail("ROOT_ADMISSION_CLOSED");
    if (item.expected.generation !== generation || !this.#allRegistered(generation) || this.#unknownInstances.size) fail("REVIEWED_GENERATION_NOT_READY");
    return this.#begin(item);
  }); }
  admitIngress(legacyIdentity, identityIdentity) { return this.#serial(async () => {
    this.#assertKnown();
    if (!["legacy_open", "identity_open"].includes(this.#phase)) return { hold: true, epoch: this.#epoch };
    const generation = this.#phase === "legacy_open" ? "legacy" : "identity";
    const item = this.#identity(generation === "legacy" ? legacyIdentity : identityIdentity);
    if (item.expected.generation !== generation || !this.#allRegistered(generation) || this.#unknownInstances.size) fail("REVIEWED_GENERATION_NOT_READY");
    return { generation, lease: await this.#begin(item) };
  }); }
  validateOperation(identity, capability) { return this.#serial(async () => {
    this.#assertKnown(); const item = this.#identity(identity), op = this.#operation(capability);
    if (op.instanceId !== item.expected.id) fail("OPERATION_INSTANCE_MISMATCH");
    return { operationId: op.id, rootOperationId: op.rootId, epoch: op.epoch, unknown: op.unknown, childrenActive: op.children.size };
  }); }
  enterOperation(identity, capability) { return this.#serial(async () => {
    this.#assertKnown(); const item = this.#identity(identity), op = this.#operation(capability);
    if (op.unknown) fail("OPERATION_UNKNOWN_REQUIRES_REVIEW");
    if (op.instanceId !== item.expected.id || op.entered) fail("HANDLER_CAPABILITY_ALREADY_ENTERED_OR_WRONG_INSTANCE");
    await this.#record("handler_enter", { operationId: op.id, instanceId: op.instanceId });
    if (op.unknown) fail("OPERATION_UNKNOWN_REQUIRES_REVIEW");
    op.entered = true;
    return { operationId: op.id, rootOperationId: op.rootId, epoch: op.epoch };
  }); }
  continueOperation(identity, parentCapability, targetId) { return this.#serial(async () => {
    this.#assertKnown(); const caller = this.#identity(identity), parent = this.#operation(parentCapability);
    if (caller.expected.id !== parent.instanceId || !parent.entered || parent.unknown || ["drained", "prepared", "committing", "identity_open"].includes(this.#phase) && caller.expected.generation !== "identity") fail("CONTINUATION_NOT_AUTHORIZED");
    const target = this.#instances.get(targetId);
    if (!target || !caller.expected.allowedChildren.includes(targetId) || target.expected.generation !== caller.expected.generation) fail("CONTINUATION_TARGET_NOT_REVIEWED");
    this.#identity(target.expected); return this.#begin(target, parent);
  }); }
  markForwarded(capability) { return this.#serial(async () => {
    const operation = this.#operation(capability);
    if (operation.unknown) fail("OPERATION_UNKNOWN_REQUIRES_REVIEW");
    if (operation.forwarded) fail("OPERATION_ALREADY_FORWARDED");
    await this.#record("operation_forwarded", { operationId: operation.id, instanceId: operation.instanceId });
    if (operation.unknown) fail("OPERATION_UNKNOWN_REQUIRES_REVIEW");
    operation.forwarded = true;
  }); }
  complete(identity, capability, evidence) { return this.#serial(async () => {
    this.#assertKnown(); const item = this.#identity(identity), op = this.#operation(capability, true);
    if (op.instanceId !== item.expected.id) fail("OPERATION_INSTANCE_MISMATCH");
    if (op.completed) return this.status();
    if (op.unknown) fail("OPERATION_UNKNOWN_REQUIRES_REVIEW");
    if (!op.entered || op.children.size || evidence?.handlerFinished !== true || evidence?.sideEffectsFinished !== true
      || (item.expected.requiresSessionClose && evidence?.sessionWriteClosed !== true)) fail("REAL_HANDLER_COMPLETION_REQUIRED");
    await this.#record("operation_end", { operationId: op.id, instanceId: op.instanceId });
    // A transport/process observation can arrive while the durable append is
    // pending. A late framework completion cannot erase that uncertainty.
    if (op.unknown || this.#unknownInstances.has(op.instanceId)) fail("OPERATION_UNKNOWN_REQUIRES_REVIEW");
    this.#operations.delete(op.id); if (op.parentId) this.#operations.get(op.parentId)?.children.delete(op.id);
    this.#completed.set(op.id, { capability: op.capability, id: op.id, instanceId: op.instanceId, completed: true });
    if (this.#completed.size > 8192) this.#completed.delete(this.#completed.keys().next().value);
    item.end++; item.ack = null; await this.#maybeDrain(); return this.status();
  }); }
  markOperationUnknown(capability) {
    // Latch before joining the serialized queue, including during a pending
    // completion fsync. Invalid or already-completed capabilities do not latch.
    try { this.#operation(capability).unknown = true; this.#emit(); }
    catch (error) { return Promise.reject(error); }
    return this.#serial(async () => {
      const op = this.#operation(capability);
      await this.#record("operation_unknown", { operationId: op.id, instanceId: op.instanceId }); this.#emit();
    });
  }
  markInstanceLost(identity) {
    const expected = this.#instances.get(identity?.id)?.expected;
    if (expected && ["bootId", "revision", "routerHash", "generation"].every(key => expected[key] === identity[key])) this.#latchUnknown(identity.id);
    return this.#serial(async () => {
    const item = this.#identity(identity);
    await this.#record("instance_lost", { instanceId: item.expected.id }); item.lost = true; item.ack = null;
    for (const op of this.#operations.values()) if (op.instanceId === item.expected.id) op.unknown = true;
    this.#unknownInstances.add(item.expected.id);
    if (["drained", "prepared"].includes(this.#phase)) { this.#phase = "closing"; this.#drain = null; this.#prepared = null; }
    this.#emit();
  }); }
  close() { return this.#serial(async () => {
    this.#assertKnown(); if (this.#phase !== "legacy_open") fail("INVALID_CLOSE_PHASE");
    if (!this.#allRegistered("legacy") || this.#unknownInstances.size) fail("REVIEWED_GENERATION_NOT_READY");
    this.#epoch++; await this.#record("close_requested"); this.#phase = "closing"; this.#drain = null; this.#prepared = null; this.#routerAcks.clear();
    for (const item of this.#instances.values()) { item.ack = null; item.warm = null; }
    this.#emit(); return this.status();
  }); }
  acknowledgeClose(identity, { epoch, lastBegin, lastEnd }) { return this.#serial(async () => {
    this.#assertKnown(); const item = this.#identity(identity);
    if (item.expected.generation === "legacy" && epoch === this.#epoch && item.ack?.epoch === epoch && item.ack.lastBegin === lastBegin
      && item.ack.lastEnd === lastEnd && item.begin === lastBegin && item.end === lastEnd
      && ["closing", "drained", "prepared", "committing"].includes(this.#phase)) return this.status();
    if (this.#phase !== "closing" || item.expected.generation !== "legacy" || epoch !== this.#epoch || lastBegin !== item.begin || lastEnd !== item.end) fail("CLOSE_ACK_SEQUENCE_MISMATCH");
    await this.#record("close_ack", { instanceId: item.expected.id }); item.ack = { epoch, lastBegin, lastEnd };
    await this.#maybeDrain(); return this.status();
  }); }
  async #maybeDrain() {
    if (this.#phase !== "closing" || this.#operations.size || this.#unknownInstances.size || !this.#allRegistered("legacy")) return;
    const rows = this.roster.filter(item => item.generation === "legacy").map(item => this.#instances.get(item.id));
    if (rows.some(item => !item.ack || item.ack.epoch !== this.#epoch || item.ack.lastBegin !== item.begin || item.ack.lastEnd !== item.end)) return;
    this.#drain = Object.freeze({ version: 1, epoch: this.#epoch, rosterSha256: this.rosterSha256,
      participants: rows.map(item => ({ ...item.expected, lastBegin: item.begin, lastEnd: item.end })), closedAt: new Date(this.#now()).toISOString() });
    await this.#record("drained", { drainSha256: digest(this.#drain) }); this.#phase = "drained"; this.#emit();
  }
  drainEvidence() { if (this.#phase !== "drained" || !this.#drain) fail("NO_ACTUAL_DRAIN_EVIDENCE"); return structuredClone(this.#drain); }
  acknowledgeWarm(identity, { epoch, allocatorProofSha256 }) { return this.#serial(async () => {
    this.#assertKnown(); const item = this.#identity(identity);
    if (item.expected.generation !== "identity" || epoch !== this.#epoch || !digestPattern.test(allocatorProofSha256)) fail("INVALID_WARM_ACK");
    await this.#record("warm_ack", { instanceId: item.expected.id }); item.warm = { epoch, allocatorProofSha256 }; return this.status();
  }); }
  prepare(proof) { return this.#serial(async () => {
    this.#assertKnown();
    if (this.#phase !== "drained" || !this.#drain || this.#operations.size || this.#unknownInstances.size || !this.#allRegistered("legacy")
      || proof?.drainSha256 !== digest(this.#drain) || proof.epoch !== this.#epoch) fail("ACTUAL_DRAIN_PROOF_REQUIRED");
    if (["sourceSnapshotSha256", "syncReportSha256", "allocatorProofSha256"].some(key => !digestPattern.test(proof[key])) || proof.identityRevision !== this.identityRevision
      || Object.keys(proof).sort().join(",") !== ["allocatorProofSha256", "drainSha256", "epoch", "identityRevision", "sourceSnapshotSha256", "syncReportSha256"].sort().join(",")) fail("INVALID_PREPARED_BINDING");
    if (!this.#allRegistered("identity") || this.roster.filter(item => item.generation === "identity").some(item => {
      const warm = this.#instances.get(item.id).warm; return !warm || warm.epoch !== this.#epoch || warm.allocatorProofSha256 !== proof.allocatorProofSha256;
    })) fail("IDENTITY_GENERATION_NOT_WARMED");
    this.#prepared = Object.freeze({ ...proof, rosterSha256: this.rosterSha256 });
    await this.#record("prepared", { preparedSha256: digest(this.#prepared) }); this.#phase = "prepared"; this.#emit(); return this.status();
  }); }
  async #cancel(code) {
    if (!["closing", "drained", "prepared"].includes(this.#phase)) fail("CANCEL_FORBIDDEN_AFTER_COMMIT_INTENT");
    await this.#record("cancelled", { code }); this.#phase = "legacy_open"; this.#drain = null; this.#prepared = null; this.#emit(); return this.status();
  }
  cancel(code = "PRECOMMIT_CANCELLED") { return this.#serial(async () => { this.#assertKnown(); return this.#cancel(/^[A-Z][A-Z0-9_]{0,79}$/.test(code) ? code : "PRECOMMIT_CANCELLED"); }); }
  startCommit({ deadline }) { return this.#serial(async () => {
    this.#assertKnown(); if (this.#phase !== "prepared") fail("PREPARED_PHASE_REQUIRED");
    const end = isoDate(deadline);
    const check = () => {
      if (this.#operations.size || this.#unknownInstances.size || this.#recovery || !this.#allRegistered("legacy") || !this.#allRegistered("identity")
        || this.roster.filter(item => item.generation === "identity").some(item => {
          const warm = this.#instances.get(item.id).warm; return !warm || warm.epoch !== this.#epoch || warm.allocatorProofSha256 !== this.#prepared.allocatorProofSha256;
        })) fail("PREPARED_ROSTER_NO_LONGER_READY");
      if (end <= this.#now() || end - this.#now() > 300000) fail("COMMIT_BUDGET_UNAVAILABLE"); for (const inspect of this.#checks) inspect();
    };
    try { check(); } catch { await this.#cancel("PRECOMMIT_BUDGET_UNAVAILABLE"); fail("PRECOMMIT_CANCELLED"); }
    const requestSha256 = digest({ version: 1, database: "xrugc_identity_dev", allocatorManifestSha256: this.#prepared.allocatorProofSha256, compiledRevision: this.#prepared.identityRevision, deadline });
    await this.#record("commit_intent", { requestSha256 });
    try { check(); } catch { await this.#cancel("PRECOMMIT_BUDGET_UNAVAILABLE"); fail("PRECOMMIT_CANCELLED"); }
    this.#commit = { deadline, requestSha256, unknown: false }; this.#phase = "committing"; this.#emit();
    return { epoch: this.#epoch, expectedProofSha256: this.#prepared.allocatorProofSha256, expectedRevision: this.#prepared.identityRevision, deadline, requestSha256 };
  }); }
  commitUnknown() { return this.#serial(async () => {
    if (this.#phase !== "committing") fail("COMMITTING_PHASE_REQUIRED");
    await this.#record("commit_unknown", { requestSha256: this.#commit.requestSha256 }); this.#commit.unknown = true; this.#emit(); return this.status();
  }); }
  confirmIdentity(receipt) { return this.#serial(async () => {
    this.#assertKnown(); if (this.#phase !== "committing") fail("COMMITTING_PHASE_REQUIRED");
    if (receipt?.requestSha256 !== this.#commit.requestSha256 || receipt.deadline !== this.#commit.deadline
      || receipt.allocatorManifestSha256 !== this.#prepared.allocatorProofSha256 || receipt.compiledRevision !== this.#prepared.identityRevision
      || isoDate(receipt.decisionAt) >= isoDate(receipt.deadline)) fail("ACTUAL_ACTIVATION_RECEIPT_REQUIRED");
    await this.#record("identity_confirmed", { requestSha256: receipt.requestSha256 }); this.#authorityConfirmed = true;
    await this.#maybeOpenIdentity(); return this.status();
  }); }
  acknowledgeRouter(identity, { epoch, generation }) { return this.#serial(async () => {
    this.#assertKnown(); const router = this.routers.find(item => item.id === identity?.id);
    if (this.#phase !== "committing" || !router || ["bootId", "routerHash"].some(key => router[key] !== identity[key])
      || epoch !== this.#epoch || generation !== this.rosterSha256) fail("ROUTER_ACK_BINDING_MISMATCH");
    await this.#record("router_ack", { routerId: router.id }); this.#routerAcks.add(router.id); await this.#maybeOpenIdentity(); return this.status();
  }); }
  async #maybeOpenIdentity() {
    if (!this.#authorityConfirmed || this.#routerAcks.size !== this.routers.length || !this.#allRegistered("identity") || this.#unknownInstances.size) return;
    await this.#record("identity_open", { requestSha256: this.#commit.requestSha256 }); this.#phase = "identity_open"; this.#emit();
  }
}
