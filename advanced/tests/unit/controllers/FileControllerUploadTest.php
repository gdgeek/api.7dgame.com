<?php

namespace tests\unit\controllers;

use api\modules\v1\controllers\FileController;
use common\components\security\UploadValidator;
use common\components\security\ValidationResult;
use PHPUnit\Framework\TestCase;
use yii\web\UploadedFile;

/**
 * FileController Upload Integration Tests
 *
 * Tests the integration of UploadValidator into the FileController's
 * upload action, including validation failure audit logging and
 * structured error response format.
 *
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.8, 2.9
 *
 * @group file-controller
 * @group security
 * @group upload
 */
class FileControllerUploadTest extends TestCase
{
    /**
     * @var FileController
     */
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new FileController('file', \Yii::$app);
    }

    // =========================================================================
    // Helper methods
    // =========================================================================

    /**
     * Create a mock UploadedFile with the given properties.
     *
     * @param string $name Original filename
     * @param string $type MIME type
     * @param int $size File size in bytes
     * @param string|null $tempName Temporary file path
     * @param int $error PHP upload error code
     * @return UploadedFile
     */
    protected function createUploadedFile(
        string $name = 'test.jpg',
        string $type = 'image/jpeg',
        int $size = 1024,
        ?string $tempName = null,
        int $error = UPLOAD_ERR_OK
    ): UploadedFile {
        $file = new UploadedFile();
        $file->name = $name;
        $file->type = $type;
        $file->size = $size;
        $file->tempName = $tempName ?? sys_get_temp_dir() . '/test_upload_' . uniqid();
        $file->error = $error;
        return $file;
    }

    /**
     * Create a temporary file with real content for finfo_file() testing.
     *
     * @param string $content Binary content to write
     * @return string Path to the temporary file
     */
    protected function createTempFileWithContent(string $content): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'upload_ctrl_test_');
        file_put_contents($tempFile, $content);
        return $tempFile;
    }

    /**
     * Get minimal valid JPEG content (JFIF header).
     * @return string
     */
    protected function getJpegContent(): string
    {
        return "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9";
    }

    /**
     * Get minimal valid PNG content.
     * @return string
     */
    protected function getPngContent(): string
    {
        return "\x89PNG\r\n\x1A\n" .
            "\x00\x00\x00\x0D" . "IHDR" .
            "\x00\x00\x00\x01" .
            "\x00\x00\x00\x01" .
            "\x08\x02" .
            "\x00\x00\x00" .
            "\x90\x77\x53\xDE" .
            "\x00\x00\x00\x00" . "IEND" . "\xAE\x42\x60\x82";
    }

    // =========================================================================
    // UploadValidator Integration Tests
    // =========================================================================

    /**
     * Test that the controller has an UploadValidator accessible.
     */
    public function testGetUploadValidatorReturnsInstance()
    {
        $validator = $this->controller->getUploadValidator();
        $this->assertInstanceOf(UploadValidator::class, $validator);
    }

    /**
     * Test that a custom UploadValidator can be injected.
     */
    public function testSetUploadValidatorInjection()
    {
        $customValidator = new UploadValidator(['maxFileSize' => 5242880]);
        $this->controller->setUploadValidator($customValidator);

        $validator = $this->controller->getUploadValidator();
        $this->assertSame($customValidator, $validator);
        $this->assertEquals(5242880, $validator->maxFileSize);
    }

    // =========================================================================
    // Error Response Format Tests
    // =========================================================================

    /**
     * Test that formatErrorResponse returns the correct structure.
     * Validates the structured error response format for upload failures.
     */
    public function testFormatErrorResponseStructure()
    {
        $response = $this->controller->formatErrorResponse(
            'UPLOAD_VALIDATION_FAILED',
            'File validation failed.',
            ['MIME type not allowed', 'File too large']
        );

        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('code', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('errors', $response);

        $this->assertFalse($response['success']);
        $this->assertEquals('UPLOAD_VALIDATION_FAILED', $response['code']);
        $this->assertEquals('File validation failed.', $response['message']);
        $this->assertCount(2, $response['errors']);
        $this->assertEquals('MIME type not allowed', $response['errors'][0]);
        $this->assertEquals('File too large', $response['errors'][1]);
    }

    /**
     * Test that formatErrorResponse with no file code returns correct structure.
     */
    public function testFormatErrorResponseNoFile()
    {
        $response = $this->controller->formatErrorResponse(
            'UPLOAD_NO_FILE',
            'No file was uploaded.',
            ['No file found in the request.']
        );

        $this->assertFalse($response['success']);
        $this->assertEquals('UPLOAD_NO_FILE', $response['code']);
        $this->assertCount(1, $response['errors']);
    }

    /**
     * Test that formatErrorResponse with empty errors array works.
     */
    public function testFormatErrorResponseEmptyErrors()
    {
        $response = $this->controller->formatErrorResponse(
            'UPLOAD_ERROR',
            'An error occurred.',
            []
        );

        $this->assertFalse($response['success']);
        $this->assertEmpty($response['errors']);
    }

    // =========================================================================
    // Success Response Format Tests
    // =========================================================================

    /**
     * Test that formatSuccessResponse returns the correct structure.
     */
    public function testFormatSuccessResponseStructure()
    {
        $file = $this->createUploadedFile('photo.jpg', 'image/jpeg', 2048);
        $safeFilename = 'abc123def456.jpg';

        $response = $this->controller->formatSuccessResponse($file, $safeFilename);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('data', $response);

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('filename', $response['data']);
        $this->assertArrayHasKey('original_name', $response['data']);
        $this->assertArrayHasKey('size', $response['data']);
        $this->assertArrayHasKey('type', $response['data']);

        $this->assertEquals('abc123def456.jpg', $response['data']['filename']);
        $this->assertEquals('photo.jpg', $response['data']['original_name']);
        $this->assertEquals(2048, $response['data']['size']);
        $this->assertEquals('image/jpeg', $response['data']['type']);
    }

    // =========================================================================
    // Validation Integration Tests (using UploadValidator directly)
    // =========================================================================

    /**
     * Test that the controller's validator rejects files with disallowed MIME types.
     * Requirement 2.1: Verify file type against a whitelist of allowed MIME types
     */
    public function testUploadValidatorRejectsDisallowedMimeType()
    {
        $validator = $this->controller->getUploadValidator();
        $file = $this->createUploadedFile('script.jpg', 'application/x-php');

        $this->assertFalse($validator->validateMimeType($file));
    }

    /**
     * Test that the controller's validator rejects files with disallowed extensions.
     * Requirement 2.2: Verify file extension against a whitelist of allowed extensions
     */
    public function testUploadValidatorRejectsDisallowedExtension()
    {
        $validator = $this->controller->getUploadValidator();
        $file = $this->createUploadedFile('malware.exe', 'image/jpeg');

        $this->assertFalse($validator->validateExtension($file));
    }

    /**
     * Test that the controller's validator rejects oversized files.
     * Requirement 2.3: Enforce a maximum file size limit of 10MB
     */
    public function testUploadValidatorRejectsOversizedFile()
    {
        $validator = $this->controller->getUploadValidator();
        $file = $this->createUploadedFile('large.jpg', 'image/jpeg', 20971520); // 20MB

        $this->assertFalse($validator->validateFileSize($file));
    }

    /**
     * Test that the controller's validator rejects double extension files.
     * Requirement 2.8: Reject files with double extensions
     */
    public function testUploadValidatorRejectsDoubleExtension()
    {
        $validator = $this->controller->getUploadValidator();

        $this->assertTrue($validator->hasDoubleExtension('malware.php.jpg'));
        $this->assertTrue($validator->hasDoubleExtension('script.exe.png'));
    }

    /**
     * Test that the controller's validator accepts valid files.
     * Requirements 2.1, 2.2, 2.3
     */
    public function testUploadValidatorAcceptsValidFile()
    {
        $tempFile = $this->createTempFileWithContent($this->getJpegContent());
        try {
            $validator = $this->controller->getUploadValidator();
            $file = $this->createUploadedFile('photo.jpg', 'image/jpeg', filesize($tempFile), $tempFile);

            $result = $validator->validate($file);
            $this->assertTrue($result->isValid());
            $this->assertEmpty($result->getErrors());
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Test that the controller's validator returns multiple errors for a file
     * with multiple issues.
     */
    public function testUploadValidatorCollectsMultipleErrors()
    {
        $tempFile = $this->createTempFileWithContent('not a real image');
        try {
            $validator = $this->controller->getUploadValidator();
            // Invalid MIME type, invalid extension, double extension, oversized, content mismatch
            $file = $this->createUploadedFile(
                'malware.php.exe',
                'application/x-executable',
                20971520,
                $tempFile
            );

            $result = $validator->validate($file);
            $this->assertFalse($result->isValid());
            $this->assertGreaterThanOrEqual(3, count($result->getErrors()));
        } finally {
            @unlink($tempFile);
        }
    }

    // =========================================================================
    // Error Code Tests
    // =========================================================================

    /**
     * Test that all expected error codes are used in the response format.
     */
    public function testErrorCodesAreWellDefined()
    {
        $expectedCodes = [
            'UPLOAD_NO_FILE',
            'UPLOAD_ERROR',
            'UPLOAD_VALIDATION_FAILED',
            'UPLOAD_STORAGE_ERROR',
            'UPLOAD_SAVE_ERROR',
        ];

        foreach ($expectedCodes as $code) {
            $response = $this->controller->formatErrorResponse($code, 'Test message', ['Test error']);
            $this->assertEquals($code, $response['code']);
        }
    }

    // =========================================================================
    // Audit Logging Tests (Requirement 2.9)
    // =========================================================================

    /**
     * Test that the controller has the logUploadFailure method accessible.
     * Requirement 2.9: Log failed upload validation with user information
     *
     * We verify the method exists and can be called via reflection since it's protected.
     */
    public function testLogUploadFailureMethodExists()
    {
        $reflection = new \ReflectionClass($this->controller);
        $this->assertTrue($reflection->hasMethod('logUploadFailure'));

        $method = $reflection->getMethod('logUploadFailure');
        $this->assertTrue($method->isProtected());
    }

    /**
     * Test that logUploadFailure can be invoked without errors.
     * Requirement 2.9: Verify audit logging doesn't throw exceptions
     */
    public function testLogUploadFailureExecutesWithoutError()
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('logUploadFailure');
        $method->setAccessible(true);

        // Should not throw any exception
        $method->invoke(
            $this->controller,
            'malware.php.jpg',
            ['MIME type not allowed', 'Double extension detected'],
            'validation_failed'
        );

        // If we reach here, the method executed without error
        $this->assertTrue(true);
    }

    // =========================================================================
    // PHP Upload Error Message Tests
    // =========================================================================

    /**
     * Test that PHP upload error codes are mapped to human-readable messages.
     */
    public function testGetPhpUploadErrorMessages()
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('getPhpUploadErrorMessage');
        $method->setAccessible(true);

        // Test known error codes
        $this->assertStringContainsString('maximum upload size', $method->invoke($this->controller, UPLOAD_ERR_INI_SIZE));
        $this->assertStringContainsString('partially uploaded', $method->invoke($this->controller, UPLOAD_ERR_PARTIAL));
        $this->assertStringContainsString('No file was uploaded', $method->invoke($this->controller, UPLOAD_ERR_NO_FILE));

        // Test unknown error code
        $this->assertEquals('Unknown upload error.', $method->invoke($this->controller, 999));
    }

    // =========================================================================
    // Controller Configuration Tests
    // =========================================================================

    /**
     * Test that the controller removes the default 'create' action.
     * This ensures uploads go through our secure actionUpload instead.
     */
    public function testCreateActionIsRemoved()
    {
        $actions = $this->controller->actions();
        $this->assertArrayNotHasKey('create', $actions);
    }

    /**
     * Test that the controller has the upload action method.
     */
    public function testUploadActionExists()
    {
        $this->assertTrue(method_exists($this->controller, 'actionUpload'));
    }

    /**
     * Test that the controller model class is correctly set.
     */
    public function testModelClassIsSet()
    {
        $this->assertEquals('api\modules\v1\models\File', $this->controller->modelClass);
    }
}
