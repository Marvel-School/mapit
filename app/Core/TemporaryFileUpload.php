<?php
/**
 * Temporary FileUpload Override for Coincidental Binary Patterns
 * This allows images with coincidental MZ patterns when they're valid images
 */

namespace App\Core;

class TemporaryFileUpload extends FileUpload {
    
    protected function scanForMaliciousContent($content) {
        // First, validate this is actually a proper image
        $tempFile = tempnam(sys_get_temp_dir(), 'img_validate');
        file_put_contents($tempFile, $content);
        $imageInfo = getimagesize($tempFile);
        unlink($tempFile);
        
        if (!$imageInfo) {
            $this->lastScanError = "Invalid image file";
            return false;
        }
        
        // Check for actual malicious patterns (not coincidental binary data)
        $realThreats = [
            '<?php'     => 'PHP code execution',
            '<?='       => 'PHP short tag execution', 
            '<script'   => 'JavaScript execution',
            'javascript:' => 'JavaScript protocol',
            'vbscript:' => 'VBScript execution',
            'eval('     => 'Code evaluation',
            'system('   => 'System command execution',
            'exec('     => 'Command execution',
            'shell_exec(' => 'Shell execution',
            'passthru(' => 'Command passthrough',
            'SELECT '   => 'SQL injection attempt',
            'INSERT '   => 'SQL injection attempt',
            'UPDATE '   => 'SQL injection attempt', 
            'DELETE '   => 'SQL injection attempt',
            'DROP '     => 'SQL injection attempt',
            'UNION '    => 'SQL injection attempt',
        ];
        
        foreach ($realThreats as $pattern => $description) {
            if (stripos($content, $pattern) !== false) {
                $this->lastScanError = $description;
                return false;
            }
        }
        
        // For binary patterns like MZ, ELF, etc., check if they're at suspicious positions
        $binaryPatterns = [
            "\x4D\x5A"           => 'Windows executable header',
            "\x7F\x45\x4C\x46"  => 'Linux executable header',
            "\xCA\xFE\xBA\xBE"  => 'Java bytecode header',
        ];
        
        foreach ($binaryPatterns as $pattern => $description) {
            $pos = strpos($content, $pattern);
            if ($pos !== false && $pos < 1000) {
                // Pattern found near beginning - likely real executable
                $this->lastScanError = "$description found at suspicious position $pos";
                return false;
            }
        }
        
        // Check for server-side template patterns with context
        if (strpos($content, '<%') !== false) {
            // Look for actual template code indicators
            $templateIndicators = ['%>', 'eval', 'Response.', 'Request.', 'Server.'];
            $hasTemplateCode = false;
            
            foreach ($templateIndicators as $indicator) {
                if (stripos($content, $indicator) !== false) {
                    $hasTemplateCode = true;
                    break;
                }
            }
            
            if ($hasTemplateCode) {
                $this->lastScanError = "Server-side template code detected";
                return false;
            }
        }
        
        // Passed all security checks
        return true;
    }
}
