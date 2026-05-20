<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Encryption Helper for Sensitive Data

class EncryptionHelper {
    private static $algorithm = 'AES-256-CBC';
    private static $encryptionKey = null;

    /**
     * Initialize encryption key from environment
     */
    private static function initKey() {
        if (self::$encryptionKey === null) {
            $key = getenv('ENCRYPTION_KEY');
            
            if (empty($key)) {
                throw new Exception('ENCRYPTION_KEY not set in .env file');
            }
            
            // Ensure key is exactly 32 bytes for AES-256
            if (strlen($key) < 32) {
                $key = hash('sha256', $key, true);
            } else {
                $key = substr($key, 0, 32);
            }
            
            self::$encryptionKey = $key;
        }
    }

    /**
     * Encrypt sensitive data
     * 
     * @param string $plaintext Data to encrypt
     * @return string Base64-encoded encrypted data with IV
     */
    public static function encrypt($plaintext) {
        if (empty($plaintext)) {
            return '';
        }

        self::initKey();

        // Generate random IV
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$algorithm));
        
        // Encrypt data
        $encrypted = openssl_encrypt($plaintext, self::$algorithm, self::$encryptionKey, 0, $iv);
        
        if ($encrypted === false) {
            throw new Exception('Encryption failed: ' . openssl_error_string());
        }

        // Combine IV and encrypted data, then base64 encode
        $combined = $iv . $encrypted;
        return base64_encode($combined);
    }

    /**
     * Decrypt sensitive data
     * 
     * @param string $ciphertext Base64-encoded encrypted data with IV
     * @return string Decrypted plaintext
     */
    public static function decrypt($ciphertext) {
        if (empty($ciphertext)) {
            return '';
        }

        self::initKey();

        // Decode from base64
        $combined = base64_decode($ciphertext, true);
        
        if ($combined === false) {
            throw new Exception('Invalid base64 data');
        }

        // Extract IV and encrypted data
        $ivLength = openssl_cipher_iv_length(self::$algorithm);
        $iv = substr($combined, 0, $ivLength);
        $encrypted = substr($combined, $ivLength);

        // Decrypt data
        $plaintext = openssl_decrypt($encrypted, self::$algorithm, self::$encryptionKey, 0, $iv);
        
        if ($plaintext === false) {
            throw new Exception('Decryption failed: ' . openssl_error_string());
        }

        return $plaintext;
    }

    /**
     * Check if a string is encrypted (base64 with IV)
     * 
     * @param string $data Data to check
     * @return bool True if appears to be encrypted
     */
    public static function isEncrypted($data) {
        if (empty($data)) {
            return false;
        }

        // Try to decode as base64
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return false;
        }

        // Check if decoded length is reasonable (IV + encrypted data)
        $ivLength = openssl_cipher_iv_length(self::$algorithm);
        return strlen($decoded) > $ivLength;
    }

    /**
     * Encrypt array of fields
     * 
     * @param array $data Array with keys to encrypt
     * @param array $fieldsToEncrypt List of field names to encrypt
     * @return array Array with encrypted fields
     */
    public static function encryptFields($data, $fieldsToEncrypt) {
        $encrypted = $data;
        
        foreach ($fieldsToEncrypt as $field) {
            if (isset($encrypted[$field]) && !empty($encrypted[$field])) {
                $encrypted[$field] = self::encrypt($encrypted[$field]);
            }
        }
        
        return $encrypted;
    }

    /**
     * Decrypt array of fields
     * 
     * @param array $data Array with encrypted fields
     * @param array $fieldsToDecrypt List of field names to decrypt
     * @return array Array with decrypted fields
     */
    public static function decryptFields($data, $fieldsToDecrypt) {
        $decrypted = $data;
        
        foreach ($fieldsToDecrypt as $field) {
            if (isset($decrypted[$field]) && !empty($decrypted[$field])) {
                try {
                    $decrypted[$field] = self::decrypt($decrypted[$field]);
                } catch (Exception $e) {
                    // If decryption fails, return original (might not be encrypted)
                    error_log('Decryption error for field ' . $field . ': ' . $e->getMessage());
                }
            }
        }
        
        return $decrypted;
    }

    /**
     * Generate a secure random token
     * 
     * @param int $length Token length in bytes
     * @return string Hex-encoded random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /**
     * Hash a value for comparison (one-way)
     * 
     * @param string $value Value to hash
     * @return string SHA-256 hash
     */
    public static function hash($value) {
        return hash('sha256', $value);
    }
}
