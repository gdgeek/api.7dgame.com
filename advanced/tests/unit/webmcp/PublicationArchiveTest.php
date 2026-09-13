<?php
namespace tests\unit\webmcp;

use api\modules\v1\services\PublicationArchive;
use PHPUnit\Framework\TestCase;
use yii\web\HttpException;

final class PublicationArchiveTest extends TestCase
{
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
