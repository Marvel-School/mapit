<?php

namespace App\Core;

/**
 * Advanced FileUpload with Intelligent Security Scanning
 * 
 * This class provides layered security that balances protection with usability:
 * 1. Image validation - ensures files are actually valid images
 * 2. Content analysis - scans for actual threats, not coincidental patterns
 * 3. Context-aware detection - understands difference between code and pixel data
 * 4. Configurable security levels - adapts to different use cases
 */
class SmartFileUpload //Absurd. Waarom hebben jullie code nodig dat uploads extra scant en kijkt of het code is?
//Waar gebruik je dit?
{
    protected $uploadPath;
    protected $allowedTypes;
    protected $maxFileSize;
    protected $lastError = null;
    protected $lastScanError = null;
    protected $securityLevel = 'balanced'; // strict, balanced, permissive
    
    public function __construct($uploadPath = 'storage/uploads/')
    {
        $this->uploadPath = rtrim($uploadPath, '/') . '/';
        $this->allowedTypes = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp']
        ];
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        
        // Ensure upload directory exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Set security level
     * 
     * @param string $level strict|balanced|permissive
     */
    public function setSecurityLevel($level)
    {
        $validLevels = ['strict', 'balanced', 'permissive'];
        if (in_array($level, $validLevels)) {
            $this->securityLevel = $level;
        }
    }
    
    /**
     * Upload and process image with intelligent security
     */
    public function uploadImage($file, $subDirectory = '')
    {
        $this->lastError = null;
        $this->lastScanError = null;
        
        // Basic upload validation
        if (!$this->validateUpload($file)) {
            return ['success' => false, 'error' => $this->lastError];
        }
        
        // Image-specific validation
        if (!$this->validateImage($file['tmp_name'])) {
            return ['success' => false, 'error' => $this->lastError];
        }
        
        // Intelligent security scan
        if (!$this->intelligentSecurityScan($file['tmp_name'], $file['name'])) {
            return ['success' => false, 'error' => $this->lastScanError];
        }
        
        // Generate secure filename
        $filename = $this->generateSecureFilename($file['name']);
        $targetDir = $this->uploadPath . ($subDirectory ? rtrim($subDirectory, '/') . '/' : '');
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $targetPath = $targetDir . $filename;
        
        // Create clean image copy (removes metadata, normalizes format)
        if (!$this->createCleanImageCopy($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => $this->lastError];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $targetPath,
            'url' => '/' . $targetPath
        ];
    }
    
    /**
     * Validate basic upload requirements
     */
    protected function validateUpload($file)
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->lastError = 'Upload failed: ' . $this->getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE);
            return false;
        }
        
        if ($file['size'] > $this->maxFileSize) {
            $this->lastError = 'File too large. Maximum size: ' . number_format($this->maxFileSize / 1024 / 1024, 1) . 'MB';
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate that file is actually a proper image
     */
    protected function validateImage($filePath)
    {
        // Get image information
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            $this->lastError = 'Invalid image file or corrupted data';
            return false;
        }
        
        // Check dimensions
        if ($imageInfo[0] < 50 || $imageInfo[1] < 50) {
            $this->lastError = 'Image too small. Minimum size: 50x50 pixels';
            return false;
        }
        
        // Check MIME type
        $mimeType = $imageInfo['mime'];
        if (!array_key_exists($mimeType, $this->allowedTypes)) {
            $this->lastError = 'Unsupported image type: ' . $mimeType;
            return false;
        }
          // Verify image can be loaded (additional corruption check)
        $testImage = null;
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $testImage = @\imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $testImage = @\imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $testImage = @\imagecreatefromgif($filePath);
                break;
        }
        
        if (!$testImage) {
            $this->lastError = 'Image file is corrupted or invalid';
            return false;
        }
        
        \imagedestroy($testImage);
        return true;
    }
    
    /**
     * Intelligent security scanning that understands context
     */
    protected function intelligentSecurityScan($filePath, $filename)
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            $this->lastScanError = 'Could not read file for security scan';
            return false;
        }
        
        // Level 1: Check for actual code execution threats
        if (!$this->scanForExecutionThreats($content)) {
            return false; // Critical threat found
        }
        
        // Level 2: Check for injection attempts (based on security level)
        if ($this->securityLevel === 'strict') {
            if (!$this->scanForInjectionAttempts($content)) {
                return false;
            }
        }
        
        // Level 3: Check for suspicious binary patterns (context-aware)
        if (!$this->scanForSuspiciousBinary($content, $filePath)) {
            return false;
        }
        
        // Level 4: Check for polyglot attempts
        if (!$this->scanForPolyglotAttempts($content)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Scan for actual code execution threats (always checked)
     */
    protected function scanForExecutionThreats($content)
    {
        $threats = [
            // Active server-side code
            '<?php' => 'PHP code execution',
            '<?=' => 'PHP short tag execution',
            '<script' => 'JavaScript execution',
            'javascript:' => 'JavaScript protocol execution',
            'vbscript:' => 'VBScript execution',
            
            // Dangerous PHP functions
            'eval(' => 'Code evaluation',
            'exec(' => 'Command execution',
            'system(' => 'System command',
            'shell_exec(' => 'Shell execution',
            'passthru(' => 'Command passthrough',
            'file_get_contents(' => 'File access',
            'file_put_contents(' => 'File writing',
            'include(' => 'File inclusion',
            'require(' => 'File requirement',
            
            // Event handlers that could execute code
            'onload=' => 'JavaScript event handler',
            'onerror=' => 'JavaScript error handler',
            'onmouseover=' => 'JavaScript mouse handler',
        ];
        
        foreach ($threats as $pattern => $description) {
            if (stripos($content, $pattern) !== false) {
                // Additional context check - make sure it's not just coincidental
                if ($this->isLikelyCodePattern($content, $pattern)) {
                    $this->lastScanError = "Active threat detected: $description";
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Check if pattern is likely actual code vs coincidental bytes
     */
    protected function isLikelyCodePattern($content, $pattern)
    {
        $pos = stripos($content, $pattern);
        if ($pos === false) return false;
        
        // Get context around the pattern
        $contextStart = max(0, $pos - 20);
        $contextEnd = min(strlen($content), $pos + strlen($pattern) + 20);
        $context = substr($content, $contextStart, $contextEnd - $contextStart);
        
        // Look for code-like indicators around the pattern
        $codeIndicators = [
            // Function calls
            '(', ')', '{', '}', ';',
            // Variable indicators
            '$', '=',
            // String delimiters
            '"', "'",
            // HTML tag structure
            '</', '>',
            // Common code words
            'function', 'var ', 'if ', 'echo', 'print'
        ];
        
        $indicatorCount = 0;
        foreach ($codeIndicators as $indicator) {
            if (strpos($context, $indicator) !== false) {
                $indicatorCount++;
            }
        }
        
        // If multiple code indicators around the pattern, likely real code
        return $indicatorCount >= 3;
    }
    
    /**
     * Scan for injection attempts (strict mode only)
     */
    protected function scanForInjectionAttempts($content)
    {
        $injectionPatterns = [
            'union select' => 'SQL injection',
            'drop table' => 'SQL injection',
            'insert into' => 'SQL injection',
            'update set' => 'SQL injection',
            'delete from' => 'SQL injection',
        ];
        
        foreach ($injectionPatterns as $pattern => $description) {
            if (stripos($content, $pattern) !== false) {
                $this->lastScanError = "Potential injection attempt: $description";
                return false;
            }
        }
        
        return true;
    }    /**
     * Context-aware binary pattern scanning
     */
    protected function scanForSuspiciousBinary($content, $filePath)
    {
        // First, check if this is a valid image by checking file signature
        $imageSignatures = [
            "\xFF\xD8\xFF" => 'JPEG',
            "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A" => 'PNG',
            "\x47\x49\x46\x38" => 'GIF',
            "RIFF" => 'RIFF/WebP'
        ];
        
        $isValidImage = false;
        foreach ($imageSignatures as $signature => $type) {
            if (strpos($content, $signature) === 0) {
                $isValidImage = true;
                break;
            }
        }
        
        // If it's a valid image, only check for embedded scripts, not binary patterns
        if ($isValidImage) {
            return $this->scanImageForEmbeddedThreats($content);
        }
        
        // For non-images, check if file starts with executable signatures
        $executableSignatures = [
            "\x4D\x5A" => 'Windows executable header',
            "\x7F\x45\x4C\x46" => 'Linux executable header',
            "\xCA\xFE\xBA\xBE" => 'Java bytecode header',
        ];
        
        // Only check first 8 bytes for executable headers
        $fileHeader = substr($content, 0, 8);
        
        foreach ($executableSignatures as $signature => $description) {
            if (strpos($fileHeader, $signature) === 0) {
                $this->lastScanError = "$description detected at file start";
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Scan image files specifically for embedded threats
     */
    protected function scanImageForEmbeddedThreats($content)
    {
        // Only check for script injection in images, not binary patterns
        $scriptThreats = [
            '<?php' => 'PHP script embedded in image',
            '<?=' => 'PHP short tag in image',
            '<script' => 'JavaScript embedded in image',
            'eval(' => 'Eval function in image',
            'system(' => 'System function in image',
        ];
        
        foreach ($scriptThreats as $pattern => $description) {
            if (stripos($content, $pattern) !== false) {
                // Make sure it looks like actual script code, not just coincidental bytes
                if ($this->looksLikeEmbeddedScript($content, $pattern)) {
                    $this->lastScanError = $description;
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Check if script pattern in image looks like actual embedded code
     */
    protected function looksLikeEmbeddedScript($content, $pattern)
    {
        $pos = stripos($content, $pattern);
        if ($pos === false) return false;
        
        // Get 100 bytes around the pattern for context
        $start = max(0, $pos - 50);
        $context = substr($content, $start, 100);
        
        // Look for multiple script indicators in the context
        $scriptIndicators = ['(', ')', ';', '$', '=', 'echo', 'print', 'function'];
        $indicatorCount = 0;
        
        foreach ($scriptIndicators as $indicator) {
            if (stripos($context, $indicator) !== false) {
                $indicatorCount++;
            }
        }
        
        // If we find multiple script indicators, it's likely real code
        return $indicatorCount >= 3;
    }
    
    /**
     * Check if binary pattern indicates a real executable vs coincidental image data
     */
    protected function isLikelyExecutableFile($content, $pattern, $pos)
    {
        // If pattern is at the very beginning (first 64 bytes), likely real executable
        if ($pos < 64) {
            // But check if we have a valid image header first
            $imageHeaders = [
                "\xFF\xD8\xFF" => 'JPEG',
                "\x89\x50\x4E\x47" => 'PNG',
                "\x47\x49\x46\x38" => 'GIF',
                "\x52\x49\x46\x46" => 'RIFF/WebP'
            ];
            
            foreach ($imageHeaders as $header => $type) {
                if (strpos($content, $header) === 0) {
                    // Valid image header at start, so binary pattern is likely coincidental
                    return false;
                }
            }
            
            // No valid image header and binary pattern at start = suspicious
            return true;
        }
        
        // If pattern is deeper in file (>1KB), check surrounding context
        if ($pos > 1024) {
            // Get context around the pattern
            $contextStart = max(0, $pos - 50);
            $contextEnd = min(strlen($content), $pos + 50);
            $context = substr($content, $contextStart, $contextEnd - $contextStart);
            
            // Look for executable-specific structures around the pattern
            $executableIndicators = [
                'This program', 'DOS mode', 'PE\x00\x00', '.exe', '.dll',
                'kernel32', 'user32', 'ntdll', 'WinExec', 'LoadLibrary'
            ];
            
            foreach ($executableIndicators as $indicator) {
                if (stripos($context, $indicator) !== false) {
                    return true; // Found executable-specific content
                }
            }
        }
        
        // Pattern found but no strong executable indicators = likely coincidental
        return false;
    }
    
    /**
     * Scan for polyglot file attempts
     */
    protected function scanForPolyglotAttempts($content)
    {
        // Check for HTML structure in image files
        if (preg_match('/<html|<!doctype/i', $content)) {
            $this->lastScanError = 'HTML polyglot detected';
            return false;
        }
        
        // Check for archive signatures that shouldn't be in images
        $archiveSignatures = [
            "\x50\x4B\x03\x04" => 'ZIP archive',
            "\x52\x61\x72\x21" => 'RAR archive',
            "\x1F\x8B" => 'GZIP archive',
        ];
        
        foreach ($archiveSignatures as $signature => $type) {
            if (strpos($content, $signature) === 0) { // At beginning of file
                $this->lastScanError = "$type polyglot detected";
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Create a clean copy of the image (removes metadata, normalizes format)
     */
    protected function createCleanImageCopy($sourcePath, $targetPath)
    {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            $this->lastError = 'Cannot process image';
            return false;
        }
          // Create image resource
        $sourceImage = null;
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $sourceImage = \imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = \imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = \imagecreatefromgif($sourcePath);
                break;
            default:
                $this->lastError = 'Unsupported image format';
                return false;
        }
        
        if (!$sourceImage) {
            $this->lastError = 'Failed to load image';
            return false;
        }
          // Create clean copy (this removes any embedded metadata/threats)
        $success = false;
        $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $success = \imagejpeg($sourceImage, $targetPath, 95);
                break;
            case 'png':
                $success = \imagepng($sourceImage, $targetPath, 8);
                break;
            case 'gif':
                $success = \imagegif($sourceImage, $targetPath);
                break;
        }
        
        \imagedestroy($sourceImage);
        
        if (!$success) {
            $this->lastError = 'Failed to save clean image';
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate secure filename
     */
    protected function generateSecureFilename($originalName)
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $hash = md5(uniqid('', true) . $originalName);
        $timestamp = time();
        
        return "{$hash}_{$timestamp}.{$extension}";
    }
    
    /**
     * Get upload error message
     */
    protected function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
    
    /**
     * Get last error message
     */
    public function getLastError()
    {
        return $this->lastError;
    }
      /**
     * Get last security scan error
     */
    public function getLastScanError()
    {
        return $this->lastScanError;
    }
    
    /**
     * Simple upload method for compatibility with existing controllers
     * Returns filename on success, false on failure
     */
    public function uploadImageSimple($file, $prefix = '')
    {
        $result = $this->uploadImage($file, '');
        
        if ($result['success']) {
            return $result['filename'];
        } else {
            $this->lastError = $result['error'];
            return false;
        }
    }
}
