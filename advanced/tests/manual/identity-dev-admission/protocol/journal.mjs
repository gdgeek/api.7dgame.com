import { constants } from "node:fs";
import { open, lstat } from "node:fs/promises";
import path from "node:path";

const fields = new Set(["event", "phase", "epoch", "at", "rosterSha256", "drainSha256", "preparedSha256", "requestSha256", "operationId", "instanceId", "routerId", "code"]);
export function publicControlRecord(record) {
  if (!record || Object.keys(record).some(key => !fields.has(key))) throw new Error("JOURNAL_PUBLIC_FIELDS_ONLY");
  for (const [key, value] of Object.entries(record)) {
    if (key === "epoch") {
      if (!Number.isSafeInteger(value) || value < 1) throw new Error("JOURNAL_INVALID_EPOCH");
    } else if (typeof value !== "string" || value.length > 160 || /[\r\n\u0000]/.test(value)) throw new Error("JOURNAL_INVALID_PUBLIC_VALUE");
    if (key.endsWith("Sha256") && !/^[a-f0-9]{64}$/.test(value)) throw new Error("JOURNAL_INVALID_DIGEST");
  }
  return JSON.stringify(record) + "\n";
}

/** Only public control evidence is persisted. Request bytes and capabilities never enter this API. */
export async function openControlJournal(filename) {
  let file, recoveryRequired = false;
  try { file = await open(filename, constants.O_RDWR | constants.O_CREAT | constants.O_EXCL | constants.O_NOFOLLOW, 0o600); }
  catch (error) {
    if (error.code !== "EEXIST") throw error;
    const before = await lstat(filename);
    if (!before.isFile() || before.isSymbolicLink() || before.size > 64 * 1024 * 1024) throw new Error("JOURNAL_PROTECTED_FILE_REQUIRED");
    file = await open(filename, constants.O_RDWR | constants.O_APPEND | constants.O_NOFOLLOW);
    const after = await file.stat();
    if (before.ino !== after.ino || before.dev !== after.dev) { await file.close(); throw new Error("JOURNAL_CHANGED_WHILE_OPENING"); }
    // Even an empty previous file can be an interrupted boot. No automatic legacy recovery.
    recoveryRequired = true;
  }
  const stat = await file.stat();
  if (!stat.isFile() || stat.nlink !== 1 || (stat.mode & 0o777) !== 0o600 || stat.uid !== process.getuid?.()) { await file.close(); throw new Error("JOURNAL_PROTECTED_FILE_REQUIRED"); }
  if (!recoveryRequired) {
    // Persist the new directory entry before any process can accept work. A
    // missing journal after a power loss must not look like a new legacy boot.
    try {
      await file.sync();
      const directory = await open(path.dirname(filename), constants.O_RDONLY | constants.O_DIRECTORY | constants.O_NOFOLLOW);
      try { await directory.sync(); } finally { await directory.close(); }
    } catch (error) { await file.close(); throw error; }
  }
  let bytes = stat.size;
  return {
    recoveryRequired,
    async append(record) {
      const text = publicControlRecord(record);
      if (bytes + Buffer.byteLength(text) > 64 * 1024 * 1024) throw new Error("JOURNAL_BOUND_REACHED");
      await file.writeFile(text); await file.sync(); bytes += Buffer.byteLength(text);
    },
    async close() { await file.close(); },
  };
}

/** Local test seam; no daemon or deployment entry point selects it. */
export function memoryFixtureJournal() {
  const records = [];
  return { recoveryRequired: false, records, async append(record) { records.push(JSON.parse(publicControlRecord(record))); }, async close() {} };
}
