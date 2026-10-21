import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { lstat, readFile, readdir } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { pathToFileURL, fileURLToPath } from 'node:url';

export const protocolFiles = ['coordinator.mjs', 'journal.mjs', 'participant-client.mjs', 'gateway.mjs', 'hold-queue.mjs', 'test-support.mjs'];
export const snapshotDirectory = fileURLToPath(new URL('./protocol/', import.meta.url));
const expectedUpstream = { repository: 'xrugc-superproject', commit: 'f603930906d55c54fc2c3552ed2f431d05cafe18', directory: 'driver/identity-dev-admission' };
export async function verifyProtocolSnapshot(directory, upstream) {
  const manifest = JSON.parse(await readFile(join(directory, 'manifest.json'), 'utf8'));
  assert.deepEqual(Object.keys(manifest).sort(), ['files', 'kind', 'upstream', 'version']);
  assert.equal(manifest.version, 1); assert.equal(manifest.kind, 'identity-admission-test-protocol-snapshot'); assert.deepEqual(manifest.upstream, expectedUpstream);
  assert.deepEqual(Object.keys(manifest.files).sort(), [...protocolFiles].sort());
  assert.deepEqual((await readdir(directory)).sort(), [...protocolFiles, 'manifest.json'].sort());
  for (const file of protocolFiles) {
    const stat = await lstat(join(directory, file)); assert.ok(stat.isFile() && !stat.isSymbolicLink() && stat.size < 1048576);
    const bytes = await readFile(join(directory, file)); const actual = createHash('sha256').update(bytes).digest('hex');
    assert.match(manifest.files[file], /^[a-f0-9]{64}$/); assert.equal(actual, manifest.files[file], `Frozen protocol drift: ${file}`);
    if (upstream) assert.ok((await readFile(join(upstream, file))).equals(bytes), `Upstream protocol drift: ${file}`);
  }
  return manifest;
}
export async function loadProtocolFixture() {
  let upstream = process.env.ADMISSION_PROTOCOL_DIR;
  if (!upstream) {
    let parent = fileURLToPath(new URL('../../../../', import.meta.url));
    for (let i = 0; i < 9; ++i) {
      const candidate = join(parent, 'driver/identity-dev-admission');
      try { const stat = await lstat(candidate); assert.ok(stat.isDirectory() && !stat.isSymbolicLink(), 'Local upstream protocol must be a real directory.'); upstream = candidate; break; }
      catch (error) { if (error.code !== 'ENOENT') throw error; }
      const next = dirname(parent); if (next === parent) break; parent = next;
    }
  }
  await verifyProtocolSnapshot(snapshotDirectory, upstream);
  // Always execute the frozen real production core, also when an upstream
  // checkout is present. The sixth file is its byte-reviewed test helper.
  return { ...(await import(pathToFileURL(join(snapshotDirectory, 'test-support.mjs')))), ...(await import(pathToFileURL(join(snapshotDirectory, 'gateway.mjs')))) };
}
