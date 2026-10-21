import { spawn } from 'node:child_process';
import { fileURLToPath } from 'node:url';

// Missing dependencies/SAPI fail the tests. A green exit with skipped, todo,
// cancelled or zero/partial coverage is also a failed mandatory release gate.
const files = ['protocol-fixture.test.mjs', 'http.test.mjs', 'transport.test.mjs'].map(file => fileURLToPath(new URL(file, import.meta.url)));
const child = spawn(process.execPath, ['--test', '--test-concurrency=1', '--test-reporter=tap', ...files], { env: process.env, stdio: ['ignore', 'pipe', 'inherit'] });
let output = ''; child.stdout.on('data', chunk => { output += chunk; process.stdout.write(chunk); });
const status = await new Promise((resolve, reject) => { child.once('error', reject); child.once('exit', resolve); });
const count = field => [...output.matchAll(new RegExp(`^# ${field} ([0-9]+)$`, 'gm'))].map(match => Number(match[1]));
const fields = ['tests', 'pass', 'fail', 'cancelled', 'skipped', 'todo'];
const values = Object.fromEntries(fields.map(field => [field, count(field)]));
if (status !== 0 || fields.some(field => values[field].length !== 1) || values.tests[0] < 83 || values.pass[0] !== values.tests[0]
    || ['fail', 'cancelled', 'skipped', 'todo'].some(field => values[field][0] !== 0)) {
  console.error('Mandatory real HTTP/Apache admission coverage failed or was incomplete.'); process.exitCode = 1;
}
