<?php

namespace tests\unit\components;

use common\components\security\UploadValidator;
use common\components\security\ValidationResult;
use PHPUnit\Framework\TestCase;
use yii\web\UploadedFile;

/**
 * UploadValidator Unit Tests
 *
 * Tests the UploadValidator component for MIME type, extension,
 * file size validation, and double extension detection.
 *
 * Requirements: 2.1, 2.2, 2.3, 2.5, 2.8
 *
 * @group upload-validator
 * @group security
 */
class UploadValidatorTest extends TestCase
{
    /**
     * @var UploadValidator
     */
    protected $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new UploadValidator();
    }

    // =========================================================================
    // Helper: create a mock UploadedFile
    // =========================================================================

    /**
     * Create a mock UploadedFile with the given properties.
     *
     * @param string $name Original filename
     * @param string $type MIME type
     * @param int $size File size in bytes
     * @param string|null $tempName Temporary file path
     * @return UploadedFile
     */
    protected function createUploadedFile(
        string $name = 'test.jpg',
        string $type = 'image/jpeg',
        int $size = 1024,
        ?string $tempName = null
    ): UploadedFile {
        $file = new UploadedFile();
        $file->name = $name;
        $file->type = $type;
        $file->size = $size;
        $file->tempName = $tempName ?? sys_get_temp_dir() . '/test_upload_' . uniqid();
        $file->error = UPLOAD_ERR_OK;
        return $file;
    }

    // =========================================================================
    // MIME Type Validation Tests (Requirement 2.1)
    // =========================================================================

    /**
     * Test that allowed MIME types pass validation.
     * Requirement 2.1: Verify file type against a whitelist of allowed MIME types
     */
    public function testValidateMimeTypeAllowedTypes()
    {
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/x-rar-compressed',
        ];

        foreach ($allowedTypes as $mimeType) {
            $file = $this->createUploadedFile('test.file', $mimeType);
            $this->assertTrue(
                $this->validator->validateMimeType($file),
                "MIME type '$mimeType' should be allowed"
            );
        }
    }

    /**
     * Test that disallowed MIME types are rejected.
     * Requirement 2.1: Verify file type against a whitelist of allowed MIME types
     */
    public function testValidateMimeTypeDisallowedTypes()
    {
        $disallowedTypes = [
            'application/x-php',
            'application/javascript',
            'text/html',
            'application/x-executable',
            'application/x-shellscript',
            'text/x-python',
            'application/octet-stream',
        ];

        foreach ($disallowedTypes as $mimeType) {
            $file = $this->createUploadedFile('test.file', $mimeType);
            $this->assertFalse(
                $this->validator->validateMimeType($file),
                "MIME type '$mimeType' should be rejected"
            );
        }
    }

    /**
     * Test that empty MIME type is rejected.
     * Requirement 2.1
     */
    public function testValidateMimeTypeEmptyType()
    {
        $file = $this->createUploadedFile('test.jpg', '');
        $this->assertFalse($this->validator->validateMimeType($file));
    }

    // =========================================================================
    // Extension Validation Tests (Requirement 2.2)
    // =========================================================================

    /**
     * Test that allowed extensions pass validation.
     * Requirement 2.2: Verify file extension against a whitelist of allowed extensions
     */
    public function testValidateExtensionAllowedExtensions()
    {
        $allowedFiles = [
            'photo.jpg',
            'photo.jpeg',
            'image.png',
            'animation.gif',
            'modern.webp',
            'document.pdf',
            'letter.doc',
            'report.docx',
            'archive.zip',
            'backup.rar',
        ];

        foreach ($allowedFiles as $filename) {
            $file = $this->createUploadedFile($filename, 'image/jpeg');
            $this->assertTrue(
                $this->validator->validateExtension($file),
                "Extension for '$filename' should be allowed"
            );
        }
    }

    /**
     * Test that disallowed extensions are rejected.
     * Requirement 2.2: Verify file extension against a whitelist of allowed extensions
     */
    public function testValidateExtensionDisallowedExtensions()
    {
        $disallowedFiles = [
            'script.php',
            'code.js',
            'page.html',
            'style.css',
            'program.exe',
            'shell.sh',
            'batch.bat',
            'script.py',
            'binary.bin',
        ];

        foreach ($disallowedFiles as $filename) {
            $file = $this->createUploadedFile($filename, 'image/jpeg');
            $this->assertFalse(
                $this->validator->validateExtension($file),
                "Extension for '$filename' should be rejected"
            );
        }
    }

    /**
     * Test that extension validation is case-insensitive.
     * Requirement 2.2
     */
    public function testValidateExtensionCaseInsensitive()
    {
        $caseVariations = ['photo.JPG', 'photo.Jpg', 'photo.jPg', 'photo.PNG', 'photo.Pdf'];

        foreach ($caseVariations as $filename) {
            $file = $this->createUploadedFile($filename, 'image/jpeg');
            $this->assertTrue(
                $this->validator->validateExtension($file),
                "Extension for '$filename' should be allowed (case-insensitive)"
            );
        }
    }

    /**
     * Test that files without extensions are rejected.
     * Requirement 2.2
     */
    public function testValidateExtensionNoExtension()
    {
        $file = $this->createUploadedFile('noextension', 'image/jpeg');
        $this->assertFalse($this->validator->validateExtension($file));
    }

    // =========================================================================
    // File Size Validation Tests (Requirement 2.3)
    // =========================================================================

    /**
     * Test that files within the size limit pass validation.
     * Requirement 2.3: Enforce a maximum file size limit of 10MB
     */
    public function testValidateFileSizeWithinLimit()
    {
        $file = $this->createUploadedFile('test.jpg', 'image/jpeg', 1024); // 1KB
        $this->assertTrue($this->validator->validateFileSize($file));
    }

    /**
     * Test that files exactly at the size limit pass validation.
     * Requirement 2.3: Boundary - exactly 10MB
     */
    public function testValidateFileSizeExactlyAtLimit()
    {
        $file = $this->createUploadedFile('test.jpg', 'image/jpeg', 10485760); // Exactly 10MB
        $this->assertTrue($this->validator->validateFileSize($file));
    }

    /**
     * Test that files exceeding the size limit are rejected.
     * Requirement 2.3: Enforce a maximum file size limit of 10MB
     */
    public function testValidateFileSizeExceedsLimit()
    {
        $file = $this->createUploadedFile('test.jpg', 'image/jpeg', 10485761); // 10MB + 1 byte
        $this->assertFalse($this->validator->validateFileSize($file));
    }

    /**
     * Test that zero-size files pass validation.
     * Requirement 2.3
     */
    public function testValidateFileSizeZero()
    {
        $file = $this->createUploadedFile('test.jpg', 'image/jpeg', 0);
        $this->assertTrue($this->validator->validateFileSize($file));
    }

    /**
     * Test that the default max file size is 10MB.
     * Requirement 2.3
     */
    public function testDefaultMaxFileSize()
    {
        $this->assertEquals(10485760, $this->validator->maxFileSize);
    }

    /**
     * Test that custom max file size can be configured.
     */
    public function testCustomMaxFileSize()
    {
        $validator = new UploadValidator(['maxFileSize' => 5242880]); // 5MB
        $file = $this->createUploadedFile('test.jpg', 'image/jpeg', 5242881);
        $this->assertFalse($validator->validateFileSize($file));

        $file2 = $this->createUploadedFile('test.jpg', 'image/jpeg', 5242880);
        $this->assertTrue($validator->validateFileSize($file2));
    }

    // =========================================================================
    // Double Extension Detection Tests (Requirement 2.8)
    // =========================================================================

    /**
     * Test that double extensions are detected.
     * Requirement 2.8: Reject files with double extensions
     */
    public function testHasDoubleExtensionDetected()
    {
        $doubleExtFiles = [
            'file.php.jpg',
            'script.exe.png',
            'malware.js.pdf',
            'shell.sh.doc',
            'hack.asp.gif',
        ];

        foreach ($doubleExtFiles as $filename) {
            $this->assertTrue(
                $this->validator->hasDoubleExtension($filename),
                "Double extension should be detected in '$filename'"
            );
        }
    }

    /**
     * Test that single extensions are not flagged.
     * Requirement 2.8
     */
    public function testHasDoubleExtensionSingleExtension()
    {
        $singleExtFiles = [
            'photo.jpg',
            'document.pdf',
            'archive.zip',
            'image.png',
        ];

        foreach ($singleExtFiles as $filename) {
            $this->assertFalse(
                $this->validator->hasDoubleExtension($filename),
                "Single extension should not be flagged in '$filename'"
            );
        }
    }

    /**
     * Test that files without extensions are not flagged.
     * Requirement 2.8
     */
    public function testHasDoubleExtensionNoExtension()
    {
        $this->assertFalse($this->validator->hasDoubleExtension('noextension'));
    }

    // =========================================================================
    // Safe Filename Generation Tests (Requirement 2.5)
    // =========================================================================

    /**
     * Test that generated filenames are safe (no path traversal).
     * Requirement 2.5: Generate a random filename to prevent path traversal attacks
     */
    public function testGenerateSafeFilenameNoPathTraversal()
    {
        $dangerousNames = [
            '../../../etc/passwd',
            '..\\..\\windows\\system32\\config',
            '/etc/shadow',
            'C:\\Windows\\System32\\cmd.exe',
            '....//....//etc/passwd',
        ];

        foreach ($dangerousNames as $name) {
            $safeName = $this->validator->generateSafeFilename($name);
            $this->assertStringNotContainsString('..', $safeName);
            $this->assertStringNotContainsString('/', $safeName, "Safe name should not contain '/' for input '$name'");
            $this->assertStringNotContainsString('\\', $safeName);
        }
    }

    /**
     * Test that generated filenames preserve the original extension.
     * Requirement 2.5
     */
    public function testGenerateSafeFilenamePreservesExtension()
    {
        $safeName = $this->validator->generateSafeFilename('photo.jpg');
        $this->assertStringEndsWith('.jpg', $safeName);

        $safeName = $this->validator->generateSafeFilename('document.PDF');
        $this->assertStringEndsWith('.pdf', $safeName);
    }

    /**
     * Test that generated filenames are unique.
     * Requirement 2.5
     */
    public function testGenerateSafeFilenameUniqueness()
    {
        $names = [];
        for ($i = 0; $i < 10; $i++) {
            $names[] = $this->validator->generateSafeFilename('test.jpg');
        }

        // All names should be unique
        $this->assertCount(10, array_unique($names));
    }

    /**
     * Test that generated filenames use SHA-256 hash format.
     * Requirement 2.5
     */
    public function testGenerateSafeFilenameFormat()
    {
        $safeName = $this->validator->generateSafeFilename('test.jpg');

        // SHA-256 hash is 64 hex characters + extension
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}\.jpg$/', $safeName);
    }

    // =========================================================================
    // File Content Scanning Tests (Requirement 2.4)
    // =========================================================================

    /**
     * Create a temporary file with real content for finfo_file() testing.
     *
     * @param string $content Binary content to write
     * @return string Path to the temporary file
     */
    protected function createTempFileWithContent(string $content): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'upload_test_');
        file_put_contents($tempFile, $content);
        return $tempFile;
    }

    /**
     * Get minimal valid JPEG content (JFIF header).
     * @return string
     */
    protected function getJpegContent(): string
    {
        // Minimal valid JPEG: SOI marker + JFIF APP0 + minimal data + EOI
        return "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9";
    }

    /**
     * Get minimal valid PNG content (PNG signature + IHDR chunk).
     * @return string
     */
    protected function getPngContent(): string
    {
        // PNG signature
        return "\x89PNG\r\n\x1A\n" .
            // IHDR chunk: length(13) + type + data + CRC
            "\x00\x00\x00\x0D" . "IHDR" .
            "\x00\x00\x00\x01" . // width: 1
            "\x00\x00\x00\x01" . // height: 1
            "\x08\x02" .         // bit depth: 8, color type: 2 (RGB)
            "\x00\x00\x00" .     // compression, filter, interlace
            "\x90\x77\x53\xDE" . // CRC
            // IEND chunk
            "\x00\x00\x00\x00" . "IEND" . "\xAE\x42\x60\x82";
    }

    /**
     * Get minimal valid GIF content (GIF89a header).
     * @return string
     */
    protected function getGifContent(): string
    {
        // GIF89a header + minimal image data
        return "GIF89a\x01\x00\x01\x00\x80\x00\x00\xFF\xFF\xFF\x00\x00\x00!\xF9\x04\x00\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;";
    }

    /**
     * Get minimal valid PDF content.
     * @return string
     */
    protected function getPdfContent(): string
    {
        return "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n%%EOF";
    }

    /**
     * Test that scanFileContent returns true when actual content matches declared JPEG type.
     * Requirement 2.4: Scan file content to verify it matches the declared MIME type
     */
    public function testScanFileContentMatchingJpeg()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('photo.jpg', 'image/jpeg', 1024, $tempFile);
            $this->assertTrue($this->validator->scanFileContent($file));
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that scanFileContent returns true when actual content matches declared PNG type.
     * Requirement 2.4
     */
    public function testScanFileContentMatchingPng()
    {
        $tempFile = $this->createTempFileWithContent($this->getPngContent());
        try {
            $file = $this->createUploadedFile('image.png', 'image/png', 1024, $tempFile);
            $this->assertTrue($this->validator->scanFileContent($file));
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that scanFileContent returns true when actual content matches declared GIF type.
     * Requirement 2.4
     */
    public function testScanFileContentMatchingGif()
    {
        $tempFile = $this->createTempFileWithContent($this->getGifContent());
        try {
            $file = $this->createUploadedFile('animation.gif', 'image/gif', 1024, $tempFile);
            $this->assertTrue($this->validator->scanFileContent($file));
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that scanFileContent returns true when actual content matches declared PDF type.
     * Requirement 2.4
     */
    public function testScanFileContentMatchingPdf()
    {
        $tempFile = $this->createTempFileWithContent($this->getPdfContent());
        try {
            $file = $this->createUploadedFile('document.pdf', 'application/pdf', 1024, $tempFile);
            $this->assertTrue($this->validator->scanFileContent($file));
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that scanFileContent rejects when content is JPEG but declared as PNG.
     * Requirement 2.4: Content type mismatch should be rejected
     */
    public function testScanFileContentMismatchJpegDeclaredAsPng()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('fake.png', 'image/png', 1024, $tempFile);
            $this->assertFalse($this->validator->scanFileContent($file));
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that scanFileContent rejects when content is PNG but declared as JPEG.
     * Requirement 2.4: Content type mismatch should be rejected
     */
    public function testScanFileContentMismatchPngDeclaredAsJpeg()
    {
        $tempFile = $this->createTempFileWithContent($this->getPngContent());
        try {
            $file = $this->createUploadedFile('fake.jpg', 'image/jpeg', 1024, $tempFile);
            $this->assertFalse($this->validator->scanFileContent($file));
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that scanFileContent rejects when content is plain text but declared as image.
     * Requirement 2.4: Disguised file should be rejected
     */
    public function testScanFileContentTextDisguisedAsImage()
    {
        $tempFile = $this->createTempFileWithContent('<?php echo "malicious"; ?>');
        try {
            $file = $this->createUploadedFile('malware.jpg', 'image/jpeg', 1024, $tempFile);
            $this->assertFalse($this->validator->scanFileContent($file));
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that scanFileContent returns false when temp file does not exist.
     * Requirement 2.4: Fail-safe when file is missing
     */
    public function testScanFileContentMissingTempFile()
    {
        $file = $this->createUploadedFile('test.jpg', 'image/jpeg', 1024, '/nonexistent/path/file.tmp');
        $this->assertFalse($this->validator->scanFileContent($file));
    }

    /**
     * Test that scanFileContent returns false when temp file path is empty.
     * Requirement 2.4: Fail-safe when tempName is empty
     */
    public function testScanFileContentEmptyTempName()
    {
        $file = $this->createUploadedFile('test.jpg', 'image/jpeg', 1024, '');
        $file->tempName = '';
        $this->assertFalse($this->validator->scanFileContent($file));
    }

    /**
     * Test that scanFileContent returns false when declared MIME type is empty.
     * Requirement 2.4: Fail-safe when declared type is empty
     */
    public function testScanFileContentEmptyDeclaredType()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('test.jpg', '', 1024, $tempFile);
            $this->assertFalse($this->validator->scanFileContent($file));
        } finally {
            @unlink($tempFile);
        }
    }

    // =========================================================================
    // Full Validate Method Tests
    // =========================================================================

    /**
     * Test that a valid file passes all validation checks.
     */
    public function testValidateValidFile()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('photo.jpg', 'image/jpeg', filesize($tempFile), $tempFile);
            $result = $this->validator->validate($file);

            $this->assertInstanceOf(ValidationResult::class, $result);
            $this->assertTrue($result->isValid());
            $this->assertEmpty($result->getErrors());
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that an invalid MIME type causes validation failure.
     */
    public function testValidateInvalidMimeType()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('script.jpg', 'application/x-php', filesize($tempFile), $tempFile);
            $result = $this->validator->validate($file);

            $this->assertFalse($result->isValid());
            $this->assertNotEmpty($result->getErrors());
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that an invalid extension causes validation failure.
     */
    public function testValidateInvalidExtension()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('script.php', 'image/jpeg', filesize($tempFile), $tempFile);
            $result = $this->validator->validate($file);

            $this->assertFalse($result->isValid());
            $this->assertNotEmpty($result->getErrors());
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that an oversized file causes validation failure.
     */
    public function testValidateOversizedFile()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('photo.jpg', 'image/jpeg', 20971520, $tempFile); // 20MB declared
            $result = $this->validator->validate($file);

            $this->assertFalse($result->isValid());
            $this->assertNotEmpty($result->getErrors());
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that a double extension file causes validation failure.
     */
    public function testValidateDoubleExtensionFile()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('malware.php.jpg', 'image/jpeg', filesize($tempFile), $tempFile);
            $result = $this->validator->validate($file);

            $this->assertFalse($result->isValid());
            $this->assertNotEmpty($result->getErrors());
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that content mismatch causes validation failure via validate().
     * Requirement 2.4: Content scan integrated into validate()
     */
    public function testValidateContentMismatch()
    {
        // Create a JPEG file but declare it as PNG
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $file = $this->createUploadedFile('image.png', 'image/png', filesize($tempFile), $tempFile);
            $result = $this->validator->validate($file);

            $this->assertFalse($result->isValid());
            // Should contain a content mismatch error
            $hasContentError = false;
            foreach ($result->getErrors() as $error) {
                if (strpos($error, 'content does not match') !== false) {
                    $hasContentError = true;
                    break;
                }
            }
            $this->assertTrue($hasContentError, 'Validation should report content mismatch error');
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that multiple validation errors are collected.
     */
    public function testValidateMultipleErrors()
    {
        // Create a text file disguised as executable - invalid MIME type, invalid extension, oversized
        $tempFile = $this->createTempFileWithContent('not a real executable');
        try {
            $file = $this->createUploadedFile('malware.php.exe', 'application/x-executable', 20971520, $tempFile);
            $result = $this->validator->validate($file);

            $this->assertFalse($result->isValid());
            // Should have at least 3 errors: size, MIME type, extension (double ext and content mismatch also)
            $this->assertGreaterThanOrEqual(3, count($result->getErrors()));
        } finally {
            @unlink($tempFile);
        }
    }

    // =========================================================================
    // Secure Storage Path Tests (Requirement 2.6)
    // =========================================================================

    /**
     * Test that the default secure storage path is set.
     * Requirement 2.6
     */
    public function testGetSecureStoragePath()
    {
        $this->assertEquals('/var/uploads', $this->validator->getSecureStoragePath());
    }

    /**
     * Test that custom storage path can be configured.
     */
    public function testCustomSecureStoragePath()
    {
        $validator = new UploadValidator(['storagePath' => '/custom/uploads']);
        $this->assertEquals('/custom/uploads', $validator->getSecureStoragePath());
    }

    // =========================================================================
    // ValidationResult Tests
    // =========================================================================

    /**
     * Test ValidationResult for valid result.
     */
    public function testValidationResultValid()
    {
        $result = new ValidationResult(true);
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
        $this->assertNull($result->getFirstError());
    }

    /**
     * Test ValidationResult for invalid result.
     */
    public function testValidationResultInvalid()
    {
        $errors = ['Error 1', 'Error 2'];
        $result = new ValidationResult(false, $errors);
        $this->assertFalse($result->isValid());
        $this->assertCount(2, $result->getErrors());
        $this->assertEquals('Error 1', $result->getFirstError());
    }

    // =========================================================================
    // Allowed MIME Types and Extensions Configuration Tests
    // =========================================================================

    /**
     * Test that the default MIME type whitelist contains all expected types.
     */
    public function testDefaultAllowedMimeTypes()
    {
        $expectedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/x-rar-compressed',
        ];

        foreach ($expectedTypes as $type) {
            $this->assertContains($type, $this->validator->allowedMimeTypes, "Default whitelist should contain '$type'");
        }
    }

    /**
     * Test that the default extension whitelist contains all expected extensions.
     */
    public function testDefaultAllowedExtensions()
    {
        $expectedExtensions = [
            '.jpg', '.jpeg', '.png', '.gif', '.webp',
            '.pdf', '.doc', '.docx', '.zip', '.rar',
        ];

        foreach ($expectedExtensions as $ext) {
            $this->assertContains($ext, $this->validator->allowedExtensions, "Default whitelist should contain '$ext'");
        }
    }

    /**
     * Test that custom MIME types can be configured.
     */
    public function testCustomAllowedMimeTypes()
    {
        $validator = new UploadValidator([
            'allowedMimeTypes' => ['image/jpeg', 'image/png'],
        ]);

        $jpegFile = $this->createUploadedFile('test.jpg', 'image/jpeg');
        $this->assertTrue($validator->validateMimeType($jpegFile));

        $pdfFile = $this->createUploadedFile('test.pdf', 'application/pdf');
        $this->assertFalse($validator->validateMimeType($pdfFile));
    }

    /**
     * Test that custom extensions can be configured.
     */
    public function testCustomAllowedExtensions()
    {
        $validator = new UploadValidator([
            'allowedExtensions' => ['.jpg', '.png'],
        ]);

        $jpgFile = $this->createUploadedFile('test.jpg', 'image/jpeg');
        $this->assertTrue($validator->validateExtension($jpgFile));

        $pdfFile = $this->createUploadedFile('test.pdf', 'application/pdf');
        $this->assertFalse($validator->validateExtension($pdfFile));
    }
}
