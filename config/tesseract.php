<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 Part II
// Last modified: 2026-05-07
// Part of: SPED LMS — Tesseract OCR Configuration

// Tesseract OCR Configuration
// Free, open-source OCR engine - no API costs!

/**
 * Find Tesseract installation
 */
function findTesseract() {
    // Manual path (set by setup script or manually)
    $manualPath = 'C:\Program Files\Tesseract-OCR\tesseract.exe';
    
    if (file_exists($manualPath)) {
        return $manualPath;
    }
    
    // Try common Windows paths
    $windowsPaths = [
        'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
        'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
    ];
    
    foreach ($windowsPaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // Try PATH (works if Tesseract is in system PATH)
    return 'tesseract';
}

/**
 * Check if Tesseract is installed and working
 */
function isTesseractInstalled() {
    $tesseractPath = findTesseract();
    
    // Test if tesseract command works
    $output = [];
    $returnCode = 0;
    
    if (file_exists($tesseractPath)) {
        // Full path exists, test it
        exec("\"$tesseractPath\" --version 2>&1", $output, $returnCode);
    } else {
        // Try as command (in PATH)
        exec('tesseract --version 2>&1', $output, $returnCode);
    }
    
    return ($returnCode === 0);
}

// Set Tesseract path
define('TESSERACT_PATH', findTesseract());

// Check if Tesseract is available
define('TESSERACT_ENABLED', isTesseractInstalled());

// Tesseract language (default: English)
// For better handwriting recognition, you can add multiple languages
// Example: 'eng+fil' for English and Filipino
define('TESSERACT_LANG', 'eng');

// Tesseract PSM (Page Segmentation Mode)
// 3 = Fully automatic page segmentation (default)
// 6 = Assume a single uniform block of text
// 11 = Sparse text. Find as much text as possible in no particular order
define('TESSERACT_PSM', 3);

// Tesseract OEM (OCR Engine Mode)
// 3 = Default, based on what is available (recommended)
define('TESSERACT_OEM', 3);

// Debug info (only for development)
if (defined('DEBUG_TESSERACT') && DEBUG_TESSERACT) {
    error_log("Tesseract Path: " . TESSERACT_PATH);
    error_log("Tesseract Enabled: " . (TESSERACT_ENABLED ? 'Yes' : 'No'));
}
