<?php
// Temporary FileUpload with relaxed security for debugging
// DO NOT use this in production - it's for debugging only

namespace App\Core;

class RelaxedFileUpload extends FileUpload //Onnodig
{
    /**
     * Override the security scan to be less strict for debugging
     */
    protected function scanForMaliciousContent($filePath)
    {
        try {
            $this->lastScanError = null;
            
            // Read file content
            $content = file_get_contents($filePath);
            if ($content === false) {
                $this->lastScanError = 'Could not read file content';
                return false;
            }
            
            // Only check for the most dangerous patterns
            $criticalPatterns = [
                '<?php',
                '<?=',
                '<script',
                'eval(',
                'exec(',
                'system(',
                'shell_exec('
            ];
            
            foreach ($criticalPatterns as $pattern) {
                if (stripos($content, $pattern) !== false) {
                    $this->lastScanError = "Critical pattern detected: '$pattern'";
                    return false;
                }
            }
            
            // Skip binary pattern checks for debugging
            // Skip polyglot checks for debugging
            
            return true; // Allow upload if no critical patterns found
            
        } catch (\Exception $e) {
            $this->lastScanError = "Scan exception: " . $e->getMessage();
            return false;
        }
    }
}
?>
