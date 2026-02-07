<?php

namespace common\components\security;

use Yii;
use yii\base\Component;
use yii\web\UploadedFile;

/**
 * UploadValidator Component
 *
 * Validates and securely handles file uploads by enforcing MIME type whitelists,
 * file extension whitelists, file size limits, and double extension detection.
 *
 * Implements Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.8 from backend-security-hardening spec.
 *
 * @property-read array $allowedMimeTypes
 * @property-read array $allowedExtensions
 * @property-read int $maxFileSize
 */
class UploadValidator extends Component
{
    /**
     * @var int Maximum file size in bytes (10MB)
     */
    public $maxFileSize = 10485760; // 10 * 1024 * 1024

    /**
     * @var array Whitelist of allowed MIME types
     * Requirement 2.1: Verify file type against a whitelist of allowed MIME types
     */
    public $allowedMimeTypes = [
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        // Archives
        'application/zip',
        'application/x-rar-compressed',
    ];

    /**
     * @var array Whitelist of allowed file extensions (lowercase, with leading dot)
     * Requirement 2.2: Verify file extension against a whitelist of allowed extensions
     */
    public $allowedExtensions = [
        '.jpg',
        '.jpeg',
        '.png',
        '.gif',
        '.webp',
        '.pdf',
        '.doc',
        '.docx',
        '.zip',
        '.rar',
    ];

    /**
     * @var string Base path for secure file storage (outside web root)
     */
    public $storagePath = '/var/uploads';

    /**
     * @var array Mapping of MIME type groups for content validation.
     * Maps detected content MIME types to groups of acceptable declared MIME types.
     * Used by scanFileContent() to allow related MIME types to match.
     */
    protected $mimeTypeGroups = [
        'image/jpeg' => ['image/jpeg'],
        'image/png' => ['image/png'],
        'image/gif' => ['image/gif'],
        'image/webp' => ['image/webp'],
        'application/pdf' => ['application/pdf'],
        'application/msword' => ['application/msword'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', // OOXML files are ZIP-based
        ],
        'application/zip' => [
            'application/zip',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/x-rar-compressed',
        ],
        'application/x-rar-compressed' => ['application/x-rar-compressed'],
    ];

    /**
     * Validate an uploaded file against all security rules.
     *
     * Runs MIME type, extension, file size, double extension, and content checks.
     *
     * @param UploadedFile $file The uploaded file to validate
     * @return ValidationResult Result containing success status and any error messages
     */
    public function validate(UploadedFile $file): ValidationResult
    {
        $errors = [];

        // 1. Validate file size (Requirement 2.3)
        if (!$this->validateFileSize($file)) {
            $errors[] = sprintf(
                'File size (%d bytes) exceeds the maximum allowed size of %d bytes.',
                $file->size,
                $this->maxFileSize
            );
        }

        // 2. Validate MIME type (Requirement 2.1)
        if (!$this->validateMimeType($file)) {
            $errors[] = sprintf(
                'File MIME type "%s" is not allowed. Allowed types: %s',
                $file->type,
                implode(', ', $this->allowedMimeTypes)
            );
        }

        // 3. Validate file extension (Requirement 2.2)
        if (!$this->validateExtension($file)) {
            $extension = $this->getFileExtension($file->name);
            $errors[] = sprintf(
                'File extension "%s" is not allowed. Allowed extensions: %s',
                $extension,
                implode(', ', $this->allowedExtensions)
            );
        }

        // 4. Check for double extensions (Requirement 2.8)
        if ($this->hasDoubleExtension($file->name)) {
            $errors[] = sprintf(
                'File "%s" contains multiple extensions, which is not allowed.',
                $file->name
            );
        }

        // 5. Scan file content to verify it matches declared MIME type (Requirement 2.4)
        if (!$this->scanFileContent($file)) {
            $errors[] = sprintf(
                'File content does not match the declared MIME type "%s".',
                $file->type
            );
        }

        return new ValidationResult(empty($errors), $errors);
    }

    /**
     * Validate the MIME type of an uploaded file against the whitelist.
     * Requirement 2.1: Verify file type against a whitelist of allowed MIME types
     *
     * @param UploadedFile $file The uploaded file
     * @return bool True if the MIME type is allowed
     */
    public function validateMimeType(UploadedFile $file): bool
    {
        $mimeType = $file->type;

        if (empty($mimeType)) {
            return false;
        }

        return in_array($mimeType, $this->allowedMimeTypes, true);
    }

    /**
     * Validate the file extension against the whitelist.
     * Requirement 2.2: Verify file extension against a whitelist of allowed extensions
     *
     * @param UploadedFile $file The uploaded file
     * @return bool True if the extension is allowed
     */
    public function validateExtension(UploadedFile $file): bool
    {
        $extension = $this->getFileExtension($file->name);

        if ($extension === '') {
            return false;
        }

        return in_array($extension, $this->allowedExtensions, true);
    }

    /**
     * Validate the file size against the maximum limit.
     * Requirement 2.3: Enforce a maximum file size limit of 10MB
     *
     * @param UploadedFile $file The uploaded file
     * @return bool True if the file size is within the limit
     */
    public function validateFileSize(UploadedFile $file): bool
    {
        return $file->size >= 0 && $file->size <= $this->maxFileSize;
    }

    /**
     * Scan file content to verify the actual content type matches the declared MIME type.
     * Requirement 2.4: Scan the file content to verify it matches the declared MIME type
     *
     * Uses finfo_file() to detect the actual content type of the file and compares
     * it against the declared MIME type from the upload. This prevents attackers from
     * disguising malicious files by changing the extension or declared MIME type.
     *
     * @param UploadedFile $file The uploaded file to scan
     * @return bool True if the actual content matches the declared MIME type
     */
    public function scanFileContent(UploadedFile $file): bool
    {
        // If the temp file doesn't exist or is not readable, fail validation
        if (empty($file->tempName) || !is_file($file->tempName) || !is_readable($file->tempName)) {
            return false;
        }

        $declaredType = $file->type;
        if (empty($declaredType)) {
            return false;
        }

        // Use finfo to detect the actual content type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            // If finfo cannot be opened, fail-safe: reject the file
            return false;
        }

        $detectedType = finfo_file($finfo, $file->tempName);
        finfo_close($finfo);

        if ($detectedType === false) {
            return false;
        }

        // Direct match
        if ($detectedType === $declaredType) {
            return true;
        }

        // Check if the detected type is compatible with the declared type
        // Some file formats (e.g., OOXML .docx) are detected as application/zip
        if (isset($this->mimeTypeGroups[$detectedType])) {
            return in_array($declaredType, $this->mimeTypeGroups[$detectedType], true);
        }

        return false;
    }

    /**
     * Check if a filename contains double (multiple) extensions.
     * Requirement 2.8: Reject files with double extensions (e.g., file.php.jpg)
     *
     * A file has double extensions if the base name (without the final extension)
     * still contains a dot, indicating an additional extension.
     *
     * @param string $filename The original filename
     * @return bool True if the filename has double extensions
     */
    public function hasDoubleExtension(string $filename): bool
    {
        // Get the base filename without path
        $basename = basename($filename);

        // Remove the last extension
        $withoutLastExt = pathinfo($basename, PATHINFO_FILENAME);

        // If the remaining part still contains a dot, it has double extensions
        return strpos($withoutLastExt, '.') !== false;
    }

    /**
     * Generate a safe, random filename to prevent path traversal attacks.
     * Requirement 2.5: Generate a random filename to prevent path traversal attacks
     *
     * @param string $originalName The original filename
     * @return string A safe, hashed filename with the original extension
     */
    public function generateSafeFilename(string $originalName): string
    {
        $extension = $this->getFileExtension($originalName);
        $hash = hash('sha256', uniqid('', true) . microtime(true));

        return $hash . $extension;
    }

    /**
     * Get the secure storage path for uploaded files.
     * Requirement 2.6: Place files in a dedicated upload directory outside the web root
     *
     * @return string The secure storage path
     */
    public function getSecureStoragePath(): string
    {
        return $this->storagePath;
    }

    /**
     * Extract the lowercase file extension (with leading dot) from a filename.
     *
     * Normalizes path separators to handle both Unix and Windows-style paths,
     * then extracts only the basename before determining the extension.
     *
     * @param string $filename The filename
     * @return string The lowercase extension with leading dot, or empty string if none
     */
    protected function getFileExtension(string $filename): string
    {
        // Normalize backslashes to forward slashes, then get basename
        $normalized = str_replace('\\', '/', $filename);
        $basename = basename($normalized);

        $extension = pathinfo($basename, PATHINFO_EXTENSION);

        if ($extension === '') {
            return '';
        }

        return '.' . strtolower($extension);
    }
}
