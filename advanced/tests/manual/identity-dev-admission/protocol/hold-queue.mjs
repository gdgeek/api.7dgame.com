const fail = code => { throw Object.assign(new Error(code), { safeCode: code }); };
/** Raw request material lives only in these bounded in-memory entries. */
export class HoldQueue {
  #entries = new Map(); #bytes = 0; #next = 1; #now; #expire; #timer; #clear;
  constructor({ maxRequests = 64, maxBytes = 1024 * 1024, maxBodyBytes = 256 * 1024, holdMs = 500, minCommitRemainingMs = 100,
    now = () => performance.now(), onExpire, schedule = setTimeout, unschedule = clearTimeout } = {}) {
    for (const [value, limit] of [[maxRequests, 1024], [maxBytes, 64 * 1024 * 1024], [maxBodyBytes, 16 * 1024 * 1024], [holdMs, 3000], [minCommitRemainingMs, 1000]]) {
      if (!Number.isSafeInteger(value) || value < 1 || value > limit) fail("INVALID_QUEUE_BOUND");
    }
    if (maxBodyBytes > maxBytes || minCommitRemainingMs >= holdMs || typeof onExpire !== "function") fail("INVALID_QUEUE_CONFIGURATION");
    this.maxRequests = maxRequests; this.maxBytes = maxBytes; this.maxBodyBytes = maxBodyBytes; this.holdMs = holdMs;
    this.minCommitRemainingMs = minCommitRemainingMs; this.#now = now; this.#expire = onExpire; this.#timer = schedule; this.#clear = unschedule;
  }
  add({ headerBytes, receivedAt, onRelease, onFailure }) {
    if (!Number.isSafeInteger(headerBytes) || headerBytes < 0 || !Number.isFinite(receivedAt) || receivedAt > this.#now()
      || typeof onRelease !== "function" || typeof onFailure !== "function") fail("INVALID_HELD_REQUEST");
    if (this.#entries.size >= this.maxRequests || this.#bytes + headerBytes > this.maxBytes) fail("QUEUE_CAPACITY_REACHED");
    const id = this.#next++, deadline = receivedAt + this.holdMs;
    const entry = { id, headerBytes, bodyBytes: 0, chunks: [], deadline, onRelease, onFailure, reading: true };
    this.#entries.set(id, entry); this.#bytes += headerBytes;
    entry.timer = this.#timer(() => { Promise.resolve(this.#expire()).catch(() => this.fail(id, "POSTCOMMIT_REQUEST_BUDGET_EXPIRED")); }, Math.max(0, deadline - this.#now()));
    return {
      id,
      append: chunk => {
        if (!this.#entries.has(id)) return false;
        if (!Buffer.isBuffer(chunk)) fail("RAW_BODY_BUFFER_REQUIRED");
        if (entry.bodyBytes + chunk.length > this.maxBodyBytes || this.#bytes + chunk.length > this.maxBytes) fail("QUEUE_CAPACITY_REACHED");
        entry.chunks.push(Buffer.from(chunk)); entry.bodyBytes += chunk.length; this.#bytes += chunk.length; return true;
      },
      seal: () => { if (this.#entries.has(id)) entry.reading = false; },
      abort: () => this.#remove(id),
    };
  }
  #remove(id) {
    const entry = this.#entries.get(id); if (!entry) return null;
    this.#entries.delete(id); this.#bytes -= entry.headerBytes + entry.bodyBytes; this.#clear(entry.timer); return entry;
  }
  release(generation) {
    if (!["legacy", "identity"].includes(generation)) fail("INVALID_RELEASE_GENERATION");
    for (const id of [...this.#entries.keys()]) {
      const entry = this.#remove(id);
      // Pause/remove capture listeners synchronously before another parser chunk arrives.
      try { Promise.resolve(entry.onRelease(generation, entry.chunks)).catch(() => entry.onFailure("FORWARD_RESULT_UNKNOWN")); }
      catch { entry.onFailure("FORWARD_RESULT_UNKNOWN"); }
    }
  }
  fail(id, code) { const entry = this.#remove(id); if (entry) entry.onFailure(code); }
  assertCommitBudget() {
    if ([...this.#entries.values()].some(entry => entry.deadline - this.#now() <= this.minCommitRemainingMs)) fail("FIRST_REQUEST_ABSOLUTE_BUDGET_UNAVAILABLE");
  }
  status() { return { heldRequests: this.#entries.size, heldBytes: this.#bytes,
    readingBodies: [...this.#entries.values()].filter(entry => entry.reading).length,
    earliestDeadline: this.#entries.size ? Math.min(...[...this.#entries.values()].map(entry => entry.deadline)) : null }; }
  close() { for (const id of [...this.#entries.keys()]) this.fail(id, "GATEWAY_CLOSED_UNKNOWN"); }
}
import { performance } from "node:perf_hooks";
