import test from 'node:test';
import assert from 'node:assert/strict';
import { cp, mkdtemp, readFile, rm, symlink, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { protocolFiles, snapshotDirectory, verifyProtocolSnapshot } from './protocol-fixture.mjs';

for (const variant of ['standalone', 'matching-upstream', 'source-drift', 'snapshot-drift', 'wrong-provenance', 'extra-file', 'symlink', 'missing-source']) {
  test(`frozen real admission protocol ${variant}`, async () => {
    const root = await mkdtemp(join(tmpdir(), 'admission-snapshot-'));
    const snapshot = join(root, 'snapshot'), upstream = join(root, 'upstream');
    try {
      await cp(snapshotDirectory, snapshot, { recursive: true }); await cp(snapshotDirectory, upstream, { recursive: true });
      if (variant === 'source-drift') await writeFile(join(upstream, protocolFiles[0]), 'not the reviewed production source');
      if (variant === 'snapshot-drift') await writeFile(join(snapshot, protocolFiles[0]), 'not the reviewed production source');
      if (variant === 'wrong-provenance') { const file = join(snapshot, 'manifest.json'), value = JSON.parse(await readFile(file, 'utf8')); value.upstream.commit = 'a'.repeat(40); await writeFile(file, JSON.stringify(value)); }
      if (variant === 'extra-file') await writeFile(join(snapshot, 'other.mjs'), 'export const fake = true;');
      if (variant === 'symlink') { await rm(join(snapshot, protocolFiles[0])); await symlink(join(upstream, protocolFiles[0]), join(snapshot, protocolFiles[0])); }
      if (variant === 'missing-source') await rm(join(upstream, protocolFiles[0]));
      if (['standalone', 'matching-upstream'].includes(variant)) assert.equal((await verifyProtocolSnapshot(snapshot, variant === 'standalone' ? undefined : upstream)).upstream.commit, 'f603930906d55c54fc2c3552ed2f431d05cafe18');
      else await assert.rejects(verifyProtocolSnapshot(snapshot, upstream));
    } finally { await rm(root, { recursive: true, force: true }); }
  });
}
