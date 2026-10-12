<?php
namespace tests\unit\webmcp;

use api\modules\v1\services\PublicationArchive;
use PHPUnit\Framework\TestCase;
use yii\web\HttpException;

final class PublicationArchiveTest extends TestCase
{
    public function testRetentionConfigurationIsBoundedAndInvalidValuesFailClosed(): void
    {
        $previous = getenv('WEBMCP_PUBLICATION_RETAINED_VERSIONS');
        try {
            putenv('WEBMCP_PUBLICATION_RETAINED_VERSIONS');
            $this->assertSame(20, PublicationArchive::retainedVersions());
            foreach ([1, 20, 64] as $value) {
                putenv('WEBMCP_PUBLICATION_RETAINED_VERSIONS=' . $value);
                $this->assertSame($value, PublicationArchive::retainedVersions());
            }
            foreach (['0', '-1', '65', '20x', '1.5', '999999999999999999999'] as $value) {
                putenv('WEBMCP_PUBLICATION_RETAINED_VERSIONS=' . $value);
                try { PublicationArchive::retainedVersions(); $this->fail('Invalid retention must not disable cleanup'); }
                catch (HttpException $e) { $this->assertSame(503, $e->statusCode); }
            }
        } finally {
            putenv($previous === false ? 'WEBMCP_PUBLICATION_RETAINED_VERSIONS' : 'WEBMCP_PUBLICATION_RETAINED_VERSIONS=' . $previous);
        }
    }

    public function testSharedUtf8ByteFixtures(): void
    {
        $cases = json_decode(file_get_contents(dirname(__DIR__, 2) . '/fixtures/publication-v1.json'), false, 64, JSON_THROW_ON_ERROR);
        foreach ($cases as $case) {
            $text = PublicationArchive::encode((array) $case->input);
            $this->assertSame($case->canonicalBody, $text, $case->name);
            $this->assertSame($case->contentHash, 'sha256:' . hash('sha256', $text));
            $this->assertSame($case->byteLength, strlen($text));
        }
    }

    public function testLimitCountsUtf8BytesNotCharacters(): void
    {
        try { PublicationArchive::encode(['value' => str_repeat('图', 3000000)]); }
        catch (HttpException $e) { $this->assertSame(413, $e->statusCode); return; }
        $this->fail('Oversized archive must be rejected');
    }

    public function testDepthIsBounded(): void
    {
        $data = [];
        for ($i = 0; $i < 65; $i++) $data = ['nested' => $data];
        $this->expectExceptionMessage('publication_too_deep');
        PublicationArchive::encode($data);
    }
}
