<?php

namespace App\Core;

class FileUpload
{
    /**
     * Maximum file size in bytes (5MB)
     */
    const MAX_FILE_SIZE = 5 * 1024 * 1024;
    
    /**
     * Allowed image MIME types
     */
    const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    /**
     * Allowed image extensions
     */
    const ALLOWED_IMAGE_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp'
    ];
    
    /**
     * Upload directory paths
     */
    const UPLOAD_PATHS = [
        'avatars' => 'public/images/avatars/',
        'destinations' => 'public/images/destinations/'
    ];
    
    /**
     * Image dimensions limits
     */
    const IMAGE_LIMITS = [
        'avatars' => ['max_width' => 800, 'max_height' => 800],
        'destinations' => ['max_width' => 1920, 'max_height' => 1080]
    ];
    
    protected $errors = [];
    
    /**
     * Upload and validate an image file
     * 
     * @param array $file $_FILES array element
     * @param string $type Upload type (avatars/destinations)
     * @param int $userId User ID for security logging
     * @return string|false Filename on success, false on failure
     */
    public function uploadImage($file, $type, $userId = null)
    {
        $this->errors = [];
        
        try {
            // Validate upload type
            if (!array_key_exists($type, self::UPLOAD_PATHS)) {
                $this->errors[] = 'Invalid upload type';
                return false;
            }
            
            // Check if file was uploaded
            if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
                $this->errors[] = 'No file uploaded';
                return false;
            }
            
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->errors[] = $this->getUploadErrorMessage($file['error']);
                return false;
            }
            
            // Validate file size
            if ($file['size'] > self::MAX_FILE_SIZE) {
                $this->errors[] = 'File size exceeds maximum limit of ' . $this->formatBytes(self::MAX_FILE_SIZE);
                return false;
            }
            
            // Get file info
            $originalName = $file['name'];
            $tempPath = $file['tmp_name'];
            $fileSize = $file['size'];
            
            // Validate file extension
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
                $this->errors[] = 'Invalid file type. Allowed types: ' . implode(', ', self::ALLOWED_IMAGE_EXTENSIONS);
                return false;
            }
            
            // Validate MIME type
            $mimeType = mime_content_type($tempPath);
            if (!in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
                $this->errors[] = 'Invalid MIME type detected';
                return false;
            }
            
            // Additional security: Check file signature (magic bytes)
            if (!$this->validateImageSignature($tempPath, $extension)) {
                $this->errors[] = 'File signature validation failed';
                return false;
            }
            
            // Validate image dimensions and content
            $imageInfo = getimagesize($tempPath);
            if ($imageInfo === false) {
                $this->errors[] = 'Invalid image file';
                return false;
            }
            
            list($width, $height, $imageType) = $imageInfo;
            
            // Check image dimensions
            $limits = self::IMAGE_LIMITS[$type];
            if ($width > $limits['max_width'] || $height > $limits['max_height']) {
                $this->errors[] = "Image dimensions too large. Maximum: {$limits['max_width']}x{$limits['max_height']}";
                return false;
            }
            
            // Check for minimum dimensions
            if ($width < 50 || $height < 50) {
                $this->errors[] = 'Image dimensions too small. Minimum: 50x50 pixels';
                return false;
            }
            
            // Scan for malicious content
            if (!$this->scanImageForThreats($tempPath)) {
                $this->errors[] = 'Image failed security scan';
                return false;
            }
            
            // Generate secure filename
            $filename = $this->generateSecureFilename($extension, $userId);
            
            // Ensure upload directory exists and is secure
            $uploadPath = self::UPLOAD_PATHS[$type];
            if (!$this->ensureSecureUploadDirectory($uploadPath)) {
                $this->errors[] = 'Upload directory setup failed';
                return false;
            }
            
            $fullPath = $uploadPath . $filename;
            
            // Process and sanitize image
            $processedImage = $this->processImage($tempPath, $imageType, $type);
            if (!$processedImage) {
                $this->errors[] = 'Image processing failed';
                return false;
            }
            
            // Save processed image
            if (!$this->saveProcessedImage($processedImage, $fullPath, $extension)) {
                $this->errors[] = 'Failed to save image';
                return false;
            }
            
            // Log successful upload
            if ($userId) {
                $logModel = new \App\Models\Log();
                $logModel::write('INFO', "Secure file upload completed", [
                    'user_id' => $userId,
                    'filename' => $filename,
                    'type' => $type,
                    'size' => $fileSize,
                    'dimensions' => "{$width}x{$height}"
                ], 'FileUpload');
            }
            
            return $filename;
            
        } catch (\Exception $e) {
            $this->errors[] = 'Upload processing error: ' . $e->getMessage();
            
            // Log security incident
            if ($userId) {
                $logModel = new \App\Models\Log();
                $logModel::write('ERROR', "File upload security error", [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                    'type' => $type
                ], 'Security');
            }
            
            return false;
        }
    }
    
    /**
     * Delete an uploaded file securely
     * 
     * @param string $filename
     * @param string $type
     * @param int $userId
     * @return bool
     */
    public function deleteFile($filename, $type, $userId = null)
    {
        try {
            if (!array_key_exists($type, self::UPLOAD_PATHS)) {
                return false;
            }
            
            // Validate filename format (security check)
            if (!preg_match('/^[a-f0-9]{32}_\d+\.[a-z]{3,4}$/', $filename)) {
                return false;
            }
            
            $filePath = self::UPLOAD_PATHS[$type] . $filename;
            
            if (file_exists($filePath)) {
                $deleted = unlink($filePath);
                
                if ($deleted && $userId) {
                    $logModel = new \App\Models\Log();
                    $logModel::write('INFO', "File deleted securely", [
                        'user_id' => $userId,
                        'filename' => $filename,
                        'type' => $type
                    ], 'FileUpload');
                }
                
                return $deleted;
            }
            
            return true; // File doesn't exist, consider it deleted
            
        } catch (\Exception $e) {
            if ($userId) {
                $logModel = new \App\Models\Log();
                $logModel::write('ERROR', "File deletion error", [
                    'user_id' => $userId,
                    'filename' => $filename,
                    'error' => $e->getMessage()
                ], 'Security');
            }
            
            return false;
        }
    }
    
    /**
     * Get upload errors
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Validate image file signature (magic bytes)
     * 
     * @param string $filePath
     * @param string $extension
     * @return bool
     */
    protected function validateImageSignature($filePath, $extension)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }
        
        $header = fread($handle, 8);
        fclose($handle);
        
        $signatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'gif' => ["\x47\x49\x46\x38\x37\x61", "\x47\x49\x46\x38\x39\x61"],
            'webp' => ["\x52\x49\x46\x46"]
        ];
        
        if (!isset($signatures[$extension])) {
            return false;
        }
        
        foreach ($signatures[$extension] as $signature) {
            if (strpos($header, $signature) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Scan image for potential threats
     * 
     * @param string $filePath
     * @return bool
     */    protected function scanImageForThreats($filePath)
    {
        return $this->scanForMaliciousContent($filePath);
    }
    
    /**
     * Enhanced malicious content scanning
     * 
     * @param string $filePath
     * @return bool
     */
    protected function scanForMaliciousContent($filePath)
    {
        try {
            // Read file content
            $content = file_get_contents($filePath);
            if ($content === false) {
                return false;
            }
            
            // Check for suspicious code patterns
            $suspiciousPatterns = [
                // PHP code injection
                '<?php', '<?=', '<%', '%>',
                // JavaScript injection
                '<script', '</script>', 'javascript:', 'vbscript:',
                // Event handlers
                'onload=', 'onerror=', 'onclick=', 'onmouseover=',
                'onfocus=', 'onblur=', 'onchange=', 'onsubmit=',
                // Dangerous functions
                'eval(', 'exec(', 'system(', 'shell_exec(', 'passthru(',
                'base64_decode(', 'gzinflate(', 'str_rot13(',
                // SQL injection attempts
                'union select', 'drop table', 'insert into',
                // Other suspicious content
                'document.cookie', 'document.write', 'window.location',
                'fromCharCode', 'String.fromCharCode'
            ];
            
            foreach ($suspiciousPatterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    return false;
                }
            }
            
            // Check for binary patterns that shouldn't be in images
            $binaryPatterns = [
                "\x00PE\x00\x00", // PE executable header
                "\x4D\x5A",       // DOS executable header
                "\x7F\x45\x4C\x46", // ELF header
                "\xCA\xFE\xBA\xBE", // Java class file
                "\x50\x4B\x03\x04", // ZIP file (potential polyglot)
            ];
            
            foreach ($binaryPatterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    return false;
                }
            }
            
            // Check for polyglot files (files that are valid in multiple formats)
            if ($this->detectPolyglotFile($content)) {
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            // If scanning fails, err on the side of caution
            return false;
        }
    }
    
    /**
     * Detect polyglot files (files valid in multiple formats)
     * 
     * @param string $content
     * @return bool
     */
    protected function detectPolyglotFile($content)
    {
        // Check for HTML/JavaScript polyglots in image files
        if (preg_match('/<html|<!doctype|<script/i', $content)) {
            return true;
        }
        
        // Check for embedded archives
        if (strpos($content, 'PK') === 0 || strpos($content, 'Rar!') === 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove EXIF data from images
     * 
     * @param string $filePath
     * @param int $imageType
     * @return bool
     */
    protected function removeEXIF($filePath, $imageType)
    {
        try {
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    // Load image without EXIF
                    $image = imagecreatefromjpeg($filePath);
                    if ($image === false) {
                        return false;
                    }
                    
                    // Save image without EXIF data
                    $result = imagejpeg($image, $filePath, 90);
                    imagedestroy($image);
                    return $result;
                    
                case IMAGETYPE_PNG:
                    // PNG files can contain metadata in text chunks
                    $image = imagecreatefrompng($filePath);
                    if ($image === false) {
                        return false;
                    }
                    
                    // Disable PNG metadata preservation
                    imagesavealpha($image, true);
                    $result = imagepng($image, $filePath, 9);
                    imagedestroy($image);
                    return $result;
                    
                case IMAGETYPE_WEBP:
                    // WebP can contain EXIF data
                    $image = imagecreatefromwebp($filePath);
                    if ($image === false) {
                        return false;
                    }
                    
                    $result = imagewebp($image, $filePath, 90);
                    imagedestroy($image);
                    return $result;
                    
                case IMAGETYPE_GIF:
                    // GIF files typically don't contain EXIF data
                    // But we'll still reprocess to be safe
                    $image = imagecreatefromgif($filePath);
                    if ($image === false) {
                        return false;
                    }
                    
                    $result = imagegif($image, $filePath);
                    imagedestroy($image);
                    return $result;
                    
                default:
                    return false;
            }
            
        } catch (\Exception $e) {
            return false;
        }
    }
      /**
     * Process and sanitize image
     * 
     * @param string $tempPath
     * @param int $imageType
     * @param string $type
     * @return resource|\GdImage|false
     */
    protected function processImage($tempPath, $imageType, $type)
    {
        // First, remove EXIF data from the original file
        if (!$this->removeEXIF($tempPath, $imageType)) {
            // If EXIF removal fails, continue with processing but log it
            error_log("Failed to remove EXIF data from uploaded image");
        }
        
        // Load the cleaned image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($tempPath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($tempPath);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($tempPath);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($tempPath);
                break;
            default:
                return false;
        }
        
        if (!$image) {
            return false;
        }
        
        // Create clean image without any potential metadata
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Create clean image without metadata
        $cleanImage = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG and GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($cleanImage, false);
            imagesavealpha($cleanImage, true);
            $transparent = imagecolorallocatealpha($cleanImage, 255, 255, 255, 127);
            imagefill($cleanImage, 0, 0, $transparent);
        }
        
        // Copy image data to clean image (removes any embedded metadata)
        imagecopyresampled($cleanImage, $image, 0, 0, 0, 0, $width, $height, $width, $height);
        imagedestroy($image);
        
        return $cleanImage;
    }
      /**
     * Save processed image
     * 
     * @param resource|\GdImage $image
     * @param string $path
     * @param string $extension
     * @return bool
     */
    protected function saveProcessedImage($image, $path, $extension)
    {
        $success = false;
        
        switch (strtolower($extension)) {
            case 'jpg':
            case 'jpeg':
                $success = imagejpeg($image, $path, 90);
                break;
            case 'png':
                $success = imagepng($image, $path, 8);
                break;
            case 'gif':
                $success = imagegif($image, $path);
                break;
            case 'webp':
                $success = imagewebp($image, $path, 90);
                break;
        }
        
        imagedestroy($image);
        
        if ($success) {
            // Set secure file permissions
            chmod($path, 0644);
        }
        
        return $success;
    }
    
    /**
     * Generate secure filename
     * 
     * @param string $extension
     * @param int $userId
     * @return string
     */
    protected function generateSecureFilename($extension, $userId = null)
    {
        $hash = md5(uniqid(mt_rand(), true));
        $timestamp = time();
        return $hash . '_' . $timestamp . '.' . $extension;
    }
    
    /**
     * Ensure upload directory exists and is secure
     * 
     * @param string $path
     * @return bool
     */
    protected function ensureSecureUploadDirectory($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                return false;
            }
        }
        
        // Create .htaccess for additional security
        $htaccessPath = $path . '.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Secure file upload directory\n";
            $htaccessContent .= "Options -Indexes\n";
            $htaccessContent .= "Options -ExecCGI\n";
            $htaccessContent .= "AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi\n";
            $htaccessContent .= "RemoveHandler .php .phtml .php3 .php4 .php5 .php6\n";
            $htaccessContent .= "<Files ~ \"\\.(php|phtml|php3|php4|php5|php6)$\">\n";
            $htaccessContent .= "    deny from all\n";
            $htaccessContent .= "</Files>\n";
            
            file_put_contents($htaccessPath, $htaccessContent);
        }
        
        return true;
    }
    
    /**
     * Get upload error message
     * 
     * @param int $errorCode
     * @return string
     */
    protected function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds maximum upload size',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form maximum size',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
    
    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes
     * @return string
     */
    protected function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
