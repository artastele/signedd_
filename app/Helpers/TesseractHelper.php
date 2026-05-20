<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 Part II
// Last modified: 2026-05-07
// Part of: SPED LMS — Tesseract OCR Helper

class TesseractHelper {
    
    /**
     * Extract text from image using Tesseract OCR
     * 
     * @param string $imagePath Path to image file
     * @return array ['success' => bool, 'text' => string, 'error' => string]
     */
    public static function extractText($imagePath) {
        require_once __DIR__ . '/../../config/tesseract.php';
        
        // Check if Tesseract is enabled
        if (!TESSERACT_ENABLED) {
            return [
                'success' => false,
                'text' => '',
                'error' => 'Tesseract OCR is not installed. Please install Tesseract to use AI auto-fill.'
            ];
        }
        
        // Check if file exists
        if (!file_exists($imagePath)) {
            return [
                'success' => false,
                'text' => '',
                'error' => 'Image file not found'
            ];
        }
        
        // Convert PDF to image if needed
        $fileType = mime_content_type($imagePath);
        if ($fileType === 'application/pdf') {
            $imagePath = self::convertPdfToImage($imagePath);
            if (!$imagePath) {
                return [
                    'success' => false,
                    'text' => '',
                    'error' => 'Failed to convert PDF to image. Please upload JPG or PNG instead.'
                ];
            }
        }
        
        // Create temporary output file
        $outputFile = sys_get_temp_dir() . '/tesseract_' . uniqid();
        
        // Build Tesseract command
        $command = sprintf(
            '%s "%s" "%s" -l %s --psm %d --oem %d 2>&1',
            TESSERACT_PATH,
            $imagePath,
            $outputFile,
            TESSERACT_LANG,
            TESSERACT_PSM,
            TESSERACT_OEM
        );
        
        // Execute Tesseract
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        // Read extracted text
        $textFile = $outputFile . '.txt';
        if (file_exists($textFile)) {
            $extractedText = file_get_contents($textFile);
            unlink($textFile); // Clean up
            
            return [
                'success' => true,
                'text' => $extractedText,
                'error' => ''
            ];
        } else {
            return [
                'success' => false,
                'text' => '',
                'error' => 'Tesseract failed to extract text: ' . implode("\n", $output)
            ];
        }
    }
    
    /**
     * Parse extracted text into PDSP domain structure
     * 
     * @param string $text Extracted text from OCR
     * @return array Array of domains with parsed data
     */
    public static function parsePDSPText($text) {
        $domains = [];
        
        // Define domain keywords to look for
        $domainKeywords = [
            'Perceptuo-Cognitive' => ['perceptuo', 'cognitive', 'perception'],
            'Psychosocial' => ['psychosocial', 'social'],
            'Socio-Emotional' => ['socio-emotional', 'emotional', 'socio emotional'],
            'Psychomotor' => ['psychomotor', 'motor', 'movement'],
            'Daily Living Skills' => ['daily living', 'living skills', 'daily skills'],
            'Communication and Language' => ['communication', 'language', 'speech']
        ];
        
        // Performance level keywords
        $performanceLevels = [
            'beginning' => ['beginning', 'beginner', '74%', '74 %', 'below'],
            'developing' => ['developing', '75%', '79%', '75-79'],
            'approaching' => ['approaching', 'proficiency', '80%', '84%', '80-84'],
            'proficient' => ['proficient', '85%', '89%', '85-89'],
            'advanced' => ['advanced', '90%', 'above', '90-100']
        ];
        
        // Split text into lines
        $lines = explode("\n", $text);
        $currentDomain = null;
        $buffer = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if line contains a domain name
            foreach ($domainKeywords as $domainName => $keywords) {
                foreach ($keywords as $keyword) {
                    if (stripos($line, $keyword) !== false) {
                        // Save previous domain if exists
                        if ($currentDomain && !empty($buffer)) {
                            $domains[] = self::parseDomainBuffer($currentDomain, $buffer);
                            $buffer = [];
                        }
                        $currentDomain = $domainName;
                        break 2;
                    }
                }
            }
            
            // Add line to buffer
            if ($currentDomain) {
                $buffer[] = $line;
            }
        }
        
        // Save last domain
        if ($currentDomain && !empty($buffer)) {
            $domains[] = self::parseDomainBuffer($currentDomain, $buffer);
        }
        
        // If no domains found, create a generic structure
        if (empty($domains)) {
            return self::createGenericStructure($text);
        }
        
        return $domains;
    }
    
    /**
     * Parse domain buffer into structured data
     */
    private static function parseDomainBuffer($domainName, $buffer) {
        $text = implode(' ', $buffer);
        
        // Try to extract key information
        $subDomain = '';
        $skillsDescription = '';
        $mastered = false;
        $educationalRecommendation = '';
        $q1Level = 'beginning';
        $q2Level = 'beginning';
        
        // Look for "mastered" or "not mastered"
        if (stripos($text, 'mastered') !== false) {
            $mastered = stripos($text, 'not mastered') === false;
        }
        
        // Try to detect performance levels
        $performanceLevels = [
            'beginning' => ['beginning', 'beginner', '74%', 'below'],
            'developing' => ['developing', '75%', '79%'],
            'approaching' => ['approaching', '80%', '84%'],
            'proficient' => ['proficient', '85%', '89%'],
            'advanced' => ['advanced', '90%', 'above']
        ];
        
        foreach ($performanceLevels as $level => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($text, $keyword) !== false) {
                    $q1Level = $level;
                    $q2Level = $level;
                    break 2;
                }
            }
        }
        
        // Use the text as skills description
        $skillsDescription = substr($text, 0, 500); // Limit length
        
        return [
            'domain_name' => $domainName,
            'sub_domain' => $subDomain ?: 'General',
            'skills_description' => $skillsDescription,
            'mastered' => $mastered,
            'educational_recommendation' => $educationalRecommendation ?: 'Continue practice and reinforcement',
            'q1_level' => $q1Level,
            'q2_level' => $q2Level
        ];
    }
    
    /**
     * Create generic structure when no domains detected
     */
    private static function createGenericStructure($text) {
        $domains = [];
        $domainNames = [
            'Perceptuo-Cognitive',
            'Psychosocial',
            'Socio-Emotional',
            'Psychomotor',
            'Daily Living Skills',
            'Communication and Language'
        ];
        
        // Split text roughly into 6 parts
        $textLength = strlen($text);
        $chunkSize = max(100, intval($textLength / 6));
        
        foreach ($domainNames as $index => $domainName) {
            $start = $index * $chunkSize;
            $chunk = substr($text, $start, $chunkSize);
            
            if (!empty(trim($chunk))) {
                $domains[] = [
                    'domain_name' => $domainName,
                    'sub_domain' => 'General',
                    'skills_description' => trim($chunk),
                    'mastered' => false,
                    'educational_recommendation' => 'Review and assess',
                    'q1_level' => 'beginning',
                    'q2_level' => 'beginning'
                ];
            }
        }
        
        return $domains;
    }
    
    /**
     * Convert PDF to image (requires ImageMagick or GhostScript)
     */
    private static function convertPdfToImage($pdfPath) {
        // Try ImageMagick first
        $outputImage = sys_get_temp_dir() . '/pdf_' . uniqid() . '.png';
        
        $command = sprintf(
            'magick convert -density 300 "%s[0]" -quality 100 "%s" 2>&1',
            $pdfPath,
            $outputImage
        );
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($outputImage)) {
            return $outputImage;
        }
        
        // Try GhostScript as fallback
        $command = sprintf(
            'gs -dNOPAUSE -dBATCH -sDEVICE=png16m -r300 -sOutputFile="%s" "%s" 2>&1',
            $outputImage,
            $pdfPath
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($outputImage)) {
            return $outputImage;
        }
        
        return false;
    }
}
