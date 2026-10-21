import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

// Each independent repository tests only its own runtime and locked vendor.
const framework = process.env.ADMISSION_FIXTURE_FRAMEWORK;
assert.ok(['server', 'a1'].includes(framework), 'Explicit own-repository fixture framework required.');
export const frameworks = [framework];
export const repoRoot = fileURLToPath(new URL('../../../../', import.meta.url)).replace(/\/$/, '');
export const applicationRoot = `${repoRoot}/${framework === 'server' ? 'advanced' : 'api'}`;
export const vendor = `${applicationRoot}/vendor`;
export const namespace = framework === 'server' ? 'common' : 'app';
export const clientFile = `${applicationRoot}/${framework === 'server' ? 'common/' : ''}components/admission/CoordinatorClient.php`;
const lock = JSON.parse(await readFile(`${applicationRoot}/composer.lock`, 'utf8'));
const installed = JSON.parse(await readFile(`${vendor}/composer/installed.json`, 'utf8'));
const lockedYii = lock.packages.find(item => item.name === 'yiisoft/yii2');
const installedYii = (Array.isArray(installed) ? installed : installed.packages).find(item => item.name === 'yiisoft/yii2');
assert.ok(lockedYii && installedYii, 'Real locked Yii dependency required; no cached-image vendor fallback.');
assert.equal(installedYii.version, lockedYii.version);
assert.equal(installedYii.source.reference, lockedYii.source.reference);
