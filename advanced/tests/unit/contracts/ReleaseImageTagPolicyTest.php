<?php

namespace tests\unit\contracts;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ReleaseImageTagPolicyTest extends TestCase
{
    private const COMMIT = '1234567890abcdef1234567890abcdef12345678';
    private const IMAGE = 'hkccr.ccs.tencentyun.com/gdgeek/api';
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/release-image-policy-' . bin2hex(random_bytes(8));
        mkdir($this->directory . '/bin', 0700, true);
        mkdir($this->directory . '/advanced');
        file_put_contents($this->directory . '/advanced/GIT_COMMIT', self::COMMIT . "\n");
        foreach (['docker', 'git'] as $tool) {
            file_put_contents($this->directory . '/bin/' . $tool, $this->commandStub());
            chmod($this->directory . '/bin/' . $tool, 0700);
        }
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            $this->directory, \FilesystemIterator::SKIP_DOTS
        ), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        rmdir($this->directory);
    }

    #[DataProvider('releaseBranches')]
    public function testOnlyBranchAndPublishLatestTagsArePublishedAndDigestIsVerified(string $branch): void
    {
        [$status, $output] = $this->runWorkflow($branch);
        $this->assertSame(0, $status, $output);
        $commands = $this->commands();
        $pushes = array_values(array_filter($commands, static fn ($entry) => $entry[0] === 'docker' && $entry[1][0] === 'push'));
        $this->assertSame($branch === 'publish' ? [self::IMAGE . ':publish', self::IMAGE . ':latest']
            : [self::IMAGE . ':' . $branch], array_column(array_column($pushes, 1), 1));
        $build = array_values(array_filter($commands, static fn ($entry) => $entry[0] === 'docker' && $entry[1][0] === 'build'))[0][1];
        $this->assertSame(1, count(array_keys($build, '-t', true)));
        $this->assertSame(self::IMAGE . ':' . $branch, $build[array_search('-t', $build, true) + 1]);
        $digestImage = self::IMAGE . '@sha256:' . str_repeat('a', 64);
        $this->assertContains(['docker', ['pull', $digestImage]], $commands);
        $this->assertStringContainsString($digestImage, file_get_contents($this->directory . '/summary'));
        $this->assertStringContainsString(self::COMMIT, file_get_contents($this->directory . '/summary'));
        $this->assertSame(4, (int) file_get_contents($this->directory . '/head-reads'));
    }

    public static function releaseBranches(): array { return [['develop'], ['main'], ['publish']]; }

    #[DataProvider('failedRegistryProofs')]
    public function testRegistryOrHeadMismatchFailsBeforeReportingVerified(string $scenario): void
    {
        [$status] = $this->runWorkflow('publish', $scenario);
        $this->assertNotSame(0, $status, $scenario);
        $this->assertFileDoesNotExist($this->directory . '/summary');
    }

    public static function failedRegistryProofs(): array
    {
        return [['invalid-digest'], ['branch-manifest'], ['latest-manifest'], ['image-id'],
            ['revision'], ['commit-file'], ['stale-head']];
    }

    public function testUnexpectedBranchFailsBeforeBuildingOrPushingAnyImage(): void
    {
        [$status] = $this->runWorkflow('feature/unexpected');
        $this->assertNotSame(0, $status);
        $this->assertSame([], $this->commands());
    }

    private function runWorkflow(string $branch, string $scenario = ''): array
    {
        $workflow = Yaml::parseFile(dirname(__DIR__, 4) . '/.github/workflows/ci.yml');
        $steps = array_column($workflow['jobs']['build']['steps'], null, 'name');
        $script = $steps['Build Docker Image']['run'] . "\nsource \"\${GITHUB_ENV}\"\n"
            . $steps['Push Docker Image']['run'];
        $script = str_replace(['${{ github.ref }}', '${{ github.sha }}'],
            ['refs/heads/' . $branch, self::COMMIT], $script);
        $scriptPath = $this->directory . '/workflow.bash';
        file_put_contents($scriptPath, $script);
        $environment = array_merge(getenv(), [
            'PATH' => $this->directory . '/bin:' . getenv('PATH'),
            'GITHUB_REF' => 'refs/heads/' . $branch,
            'GITHUB_ENV' => $this->directory . '/environment',
            'GITHUB_STEP_SUMMARY' => $this->directory . '/summary',
            'RELEASE_TEST_DIRECTORY' => $this->directory,
            'RELEASE_TEST_COMMIT' => self::COMMIT,
            'RELEASE_TEST_SCENARIO' => $scenario,
        ]);
        $process = proc_open(['/bin/bash', $scriptPath], [
            0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
        ], $pipes, $this->directory, $environment);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        return [proc_close($process), $output];
    }

    private function commands(): array
    {
        $path = $this->directory . '/commands';
        return is_file($path) ? array_map(static fn ($line) => json_decode($line, true, 32, JSON_THROW_ON_ERROR),
            file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) : [];
    }

    private function commandStub(): string
    {
        return <<<'PHP'
#!/usr/bin/env php
<?php
$directory = getenv('RELEASE_TEST_DIRECTORY');
$scenario = getenv('RELEASE_TEST_SCENARIO');
$commit = getenv('RELEASE_TEST_COMMIT');
$tool = basename(array_shift($argv));
file_put_contents($directory . '/commands', json_encode([$tool, $argv]) . "\n", FILE_APPEND);
if ($tool === 'git') {
    $reads = is_file($directory . '/head-reads') ? (int) file_get_contents($directory . '/head-reads') : 0;
    file_put_contents($directory . '/head-reads', (string) ++$reads);
    echo ($scenario === 'stale-head' && $reads === 4 ? str_repeat('0', 40) : $commit) . "\trefs/heads/publish\n";
    exit;
}
if ($argv[0] === 'build' || $argv[0] === 'push' || $argv[0] === 'tag'
    || array_slice($argv, 0, 2) === ['image', 'rm']) exit;
if ($argv[0] === 'pull') {
    touch($directory . '/pulled');
    exit;
}
if (array_slice($argv, 0, 2) === ['image', 'inspect']) {
    if (in_array('--format={{.Id}}', $argv, true)) {
        echo $scenario === 'image-id' && is_file($directory . '/pulled') ? 'sha256:wrong' : 'sha256:built-image-id';
    } else {
        echo $scenario === 'revision' ? str_repeat('0', 40) : $commit;
    }
    exit;
}
if (array_slice($argv, 0, 3) === ['buildx', 'imagetools', 'inspect']) {
    if (in_array('--format', $argv, true)) {
        echo json_encode(['digest' => $scenario === 'invalid-digest' ? 'sha256:invalid' : 'sha256:' . str_repeat('a', 64)]);
    } else {
        $reference = end($argv);
        $wrong = ($scenario === 'branch-manifest' && str_ends_with($reference, ':publish'))
            || ($scenario === 'latest-manifest' && str_ends_with($reference, ':latest'));
        echo json_encode(['schemaVersion' => 2, 'image' => $wrong ? 'wrong-image' : 'built-image']);
    }
    exit;
}
if ($argv[0] === 'run') {
    echo $scenario === 'commit-file' ? str_repeat('0', 40) : $commit;
    exit;
}
fwrite(STDERR, 'Unexpected stub command');
exit(90);
PHP;
    }
}
