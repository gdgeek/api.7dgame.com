/** Integration building block only; framework finally/session adapters are not installed by this module. */
export function participantClient({ controlUrl, key, identity }) {
  const target = new URL(controlUrl);
  if (target.protocol !== "http:" || target.hostname !== "127.0.0.1" || target.username || target.password || target.pathname !== "/" || target.search || target.hash
    || !Buffer.isBuffer(key) || key.length < 32) throw new Error("LOOPBACK_FIXTURE_CONTROL_REQUIRED");
  key = Buffer.from(key); identity = Object.freeze({ ...identity });
  async function call(operation, payload = {}) {
    const response = await fetch(new URL(operation, target), { method: "POST", headers: {
      authorization: `Bearer ${key.toString("base64url")}`, "x-identity-instance-id": identity.id, "x-identity-instance-boot-id": identity.bootId,
      "x-identity-instance-revision": identity.revision, "x-identity-router-hash": identity.routerHash, "x-identity-generation": identity.generation, "content-type": "application/json",
    }, body: JSON.stringify(payload), signal: AbortSignal.timeout(2000), redirect: "error" });
    const result = await response.json();
    if (!response.ok) throw Object.assign(new Error(result.code), { safeCode: result.code });
    return result;
  }
  return {
    register: () => call("register", { identity }),
    begin: () => call("begin", {}),
    validate: capability => call("validate", { capability }),
    enter: capability => call("enter", { capability }),
    continue: (capability, targetId) => call("continue", { capability, targetId }),
    complete: (capability, evidence) => call("complete", { capability, evidence }),
    unknown: capability => call("unknown", { capability }),
    lost: () => call("lost", {}),
    async acknowledgeClose() {
      const status = await call("status", {}), row = status.instances.find(item => item.id === identity.id);
      return call("close-ack", { epoch: status.epoch, lastBegin: row.lastBegin, lastEnd: row.lastEnd });
    },
    acknowledgeWarm: (epoch, allocatorProofSha256) => call("warm-ack", { epoch, allocatorProofSha256 }),
  };
}
