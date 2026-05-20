---
inclusion: manual
---

# SPED LMS — Security Modules Implementation Guide

## Overview
This document describes the 4 security modules implemented to protect the SPED LMS system:
- **Module 3:** Encrypted Sensitive Fields
- **Module 4:** CSRF Protection
- **Module 5:** Login Rate Limiting
- **Module 6:** DLP (Data Loss Prevention)

---

## Security Module 3 — Encrypted Sensitive Fields

### Purpose
Encrypt sensitive personally identifiable information (PII) at rest in the database to protect against data breaches.

### Implementation

#### EncryptionHelper (`app/Helpers/EncryptionHelper.php`)
- **Algorithm:** AES-256-CBC (industry standard)
- **Key Management:** 32-byte key from `ENCRYPTION_KEY` in `.env`
- **IV:** Random initialization vector for each encryption

#### Methods
```php
EncryptionHelper::encrypt($plaintext)           // Encrypt data
EncryptionHelper::decrypt($ciphertext)          // Decrypt data
EncryptionHelper::encryptFields($data, $fields) // Encrypt array fields
EncryptionHelper::decryptFields($data, $fields) // Decrypt array fields
EncryptionHelper::generateToken($length)        // Generate secure token
EncryptionHelper::hash($value)                  // One-way hash
```

#### Usage Example
```php
// Encrypt a single field
$encrypted = EncryptionHelper::encrypt('sensitive@email.com');

// Decrypt
$plaintext = EncryptionHelper::decrypt($encrypted);

// Encrypt multiple fields
$data = [
    'email' => 'user@example.com',
    'phone' => '09123456789',
    'name' => 'John Doe'
];
$encrypted = EncryptionHelper::encryptFields($data, ['email', 'phone']);
```

#### Sensitive Fields to Encrypt
- `users.email` — user email addresses
- `users.contact_number` — phone numbers
- `enrollment_submissions.*` — all learner PII
- `role_documents.file_path` — document paths
- `iep_documents.iep_content` — IEP data (JSON)
- `activity_log.details` — activity details

#### Database Changes
- `encryption_audit` table — tracks encryption/decryption operations
- No schema changes to existing tables (encryption is transparent)

#### Configuration
```env
ENCRYPTION_KEY=your-32-character-encryption-key-here
```

---

## Security Module 4 — CSRF Protection

### Purpose
Prevent Cross-Site Request Forgery (CSRF) attacks by validating tokens on state-changing requests.

### Implementation

#### CSRFHelper (`app/Helpers/CSRFHelper.php`)
- **Token Generation:** 32-byte random tokens
- **Token Storage:** `csrf_tokens` table
- **Token Expiry:** 1 hour
- **Token Reuse:** One-time use only

#### Methods
```php
CSRFHelper::generateToken()      // Generate new token
CSRFHelper::getToken()           // Get or create token for session
CSRFHelper::validateToken($token) // Validate token
CSRFHelper::verify()             // Verify token from POST/PUT/DELETE
CSRFHelper::cleanupExpiredTokens() // Clean up old tokens
```

#### Usage in Views
```php
<!-- Add hidden CSRF token to all forms -->
<form method="POST" action="/action">
    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(CSRFHelper::getToken()); ?>">
    <!-- form fields -->
</form>
```

#### Usage in Controllers
```php
// At start of POST/PUT/DELETE handler
try {
    CSRFHelper::verify();
} catch (Exception $e) {
    $_SESSION['error'] = 'Security validation failed';
    header('Location: /login');
    exit;
}
```

#### Token Submission Methods
- **POST data:** `_csrf_token` parameter
- **HTTP header:** `X-CSRF-TOKEN` header

#### Database Changes
- `csrf_tokens` table — stores tokens with session/user info
- Automatic cleanup of expired tokens

#### Security Features
- Tokens tied to session ID
- One-time use only
- Automatic expiry after 1 hour
- Failed validation logged to activity log
- 403 Forbidden on validation failure

---

## Security Module 5 — Login Rate Limiting

### Purpose
Prevent brute force attacks by limiting login attempts per IP and email address.

### Implementation

#### RateLimitHelper (`app/Helpers/RateLimitHelper.php`)
- **Max attempts per email:** 5 failed attempts per 15 minutes
- **Max attempts per IP:** 10 failed attempts per 15 minutes
- **Lockout duration:** 15 minutes
- **Attempt window:** 15 minutes

#### Methods
```php
RateLimitHelper::checkLoginAttempts($email, $ip)      // Check if rate limited
RateLimitHelper::recordLoginAttempt($email, $success) // Record attempt
RateLimitHelper::clearLoginAttempts($email)           // Clear on success
RateLimitHelper::checkRegistrationAttempts($email)    // Check registration limit
RateLimitHelper::recordRegistrationAttempt($email)    // Record registration
RateLimitHelper::getRemainingAttempts($email)         // Get remaining attempts
RateLimitHelper::cleanupOldRecords()                  // Clean up old records
```

#### Usage in AuthController
```php
// Check rate limit before login
$rateLimit = RateLimitHelper::checkLoginAttempts($email, $ipAddress);
if (!$rateLimit['allowed']) {
    $_SESSION['error'] = $rateLimit['message'];
    RateLimitHelper::recordLoginAttempt($email, false, $ipAddress);
    header('Location: /login');
    exit;
}

// On successful login
RateLimitHelper::recordLoginAttempt($email, true, $ipAddress);
RateLimitHelper::clearLoginAttempts($email);
```

#### Database Changes
- `rate_limit_log` table — tracks all login/registration attempts
- Indexed by email and IP for fast lookups
- Automatic cleanup of records older than 24 hours

#### Rate Limit Thresholds
| Attempt Type | Per Email | Per IP | Window |
|---|---|---|---|
| Login | 5 failed | 10 failed | 15 min |
| Registration | 3 attempts | 10 attempts | 15 min |

#### Response on Rate Limit
```
"Too many login attempts. Please try again in 15 minutes."
```

---

## Security Module 6 — DLP (Data Loss Prevention)

### Purpose
Prevent unauthorized data exfiltration through screenshots, copying, printing, and exports.

### Implementation

#### DLPHelper (`app/Helpers/DLPHelper.php`)
- **Watermarking:** User/timestamp/IP watermark on sensitive pages
- **Screenshot blocking:** Detect and block screenshot attempts
- **Copy blocking:** Disable copy/paste on sensitive pages
- **Print blocking:** Disable printing of sensitive documents
- **Export blocking:** Disable download/export functionality

#### Methods
```php
DLPHelper::isEnabled($feature)           // Check if feature enabled
DLPHelper::getSetting($key, $default)    // Get DLP setting
DLPHelper::generateWatermark($user)      // Generate watermark text
DLPHelper::getWatermarkHTML($text)       // Get watermark HTML
DLPHelper::getProtectionScript()         // Get JavaScript protections
DLPHelper::getProtectionCSS()            // Get CSS protections
DLPHelper::isSensitivePage($pageType)    // Check if page is sensitive
DLPHelper::logEvent($event, $pageType)   // Log DLP event
DLPHelper::updateSetting($key, $value)   // Update DLP setting
```

#### Usage in Views
```php
<!-- Add watermark to sensitive pages -->
<?php echo DLPHelper::getWatermarkHTML(); ?>

<!-- Add DLP protections -->
<?php echo DLPHelper::getProtectionScript(); ?>

<!-- Add DLP CSS -->
<style>
    <?php echo DLPHelper::getProtectionCSS(); ?>
</style>

<!-- Apply DLP class to protected content -->
<div class="dlp-protected">
    Sensitive content here
</div>
```

#### Database Changes
- `dlp_settings` table — configurable DLP policies
- Default settings inserted on schema creation

#### DLP Settings
| Setting | Default | Description |
|---------|---------|-------------|
| `dlp_enable_watermark` | true | Enable watermark overlay |
| `dlp_enable_screenshot_block` | true | Block screenshot attempts |
| `dlp_enable_copy_block` | true | Block copy/paste |
| `dlp_enable_print_block` | true | Block printing |
| `dlp_enable_export_block` | true | Block exports |
| `dlp_watermark_format` | `{user} \| {timestamp} \| {ip}` | Watermark format |
| `dlp_sensitive_pages` | `iep,assessment,student_records` | Sensitive page types |

#### Watermark Format Variables
- `{user}` — Current user name
- `{email}` — Current user email
- `{timestamp}` — Current date/time
- `{ip}` — User IP address

#### Protected Pages
- IEP documents (Process 5)
- Assessment records (Process 3)
- Student records (Process 2)
- Activity logs (admin only)

#### Client-Side Protections
1. **Screenshot blocking:**
   - Detect PrintScreen key
   - Detect Ctrl+PrintScreen
   - Detect Shift+Ctrl+S (Chrome)
   - Show alert on attempt

2. **Copy blocking:**
   - Disable copy event
   - Disable cut event
   - Disable paste event
   - Disable text selection
   - Disable drag-and-drop

3. **Print blocking:**
   - Detect Ctrl+P
   - Detect Cmd+P (Mac)
   - Hide content in print media query
   - Show alert on attempt

4. **Export blocking:**
   - Remove download attributes
   - Detect Ctrl+S
   - Show alert on attempt

#### Watermark Example
```
John Doe | 2026-05-04 14:30:45 | 192.168.1.100
```

---

## Database Schema Changes

### New Tables

#### `encryption_audit`
```sql
CREATE TABLE encryption_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    action ENUM('encrypted', 'decrypted') NOT NULL,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_performed_at (performed_at)
);
```

#### `csrf_tokens`
```sql
CREATE TABLE csrf_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);
```

#### `rate_limit_log`
```sql
CREATE TABLE rate_limit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    ip_address VARCHAR(45),
    attempt_type ENUM('login', 'registration', 'password_reset') NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_time (email, attempted_at),
    INDEX idx_ip_time (ip_address, attempted_at),
    INDEX idx_attempted_at (attempted_at)
);
```

#### `dlp_settings`
```sql
CREATE TABLE dlp_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);
```

---

## Configuration

### Environment Variables
```env
# Encryption
ENCRYPTION_KEY=your-32-character-encryption-key-here

# CSRF (automatic, no config needed)

# Rate Limiting (automatic, no config needed)

# DLP (configured in database, no env vars needed)
```

### Generate Encryption Key
```bash
# Generate a random 32-character key
php -r "echo bin2hex(random_bytes(16));"
```

---

## Security Best Practices

### For Developers
1. Always use `EncryptionHelper` for sensitive data
2. Always verify CSRF tokens on state-changing requests
3. Always check rate limits on authentication endpoints
4. Always apply DLP protections to sensitive pages
5. Never log sensitive data in plaintext
6. Never commit `.env` file to version control

### For Administrators
1. Rotate `ENCRYPTION_KEY` regularly
2. Monitor `rate_limit_log` for brute force attempts
3. Review `activity_log` for suspicious activities
4. Adjust DLP settings based on security requirements
5. Keep database backups encrypted
6. Use HTTPS in production (set `session.cookie_secure = 1`)

### For Users
1. Use strong, unique passwords
2. Never share CSRF tokens
3. Report suspicious login attempts
4. Don't attempt to bypass DLP protections
5. Keep browser and OS updated

---

## Testing

### Test CSRF Protection
```php
// Test 1: Valid token should pass
$token = CSRFHelper::getToken();
$_POST['_csrf_token'] = $token;
CSRFHelper::verify(); // Should pass

// Test 2: Invalid token should fail
$_POST['_csrf_token'] = 'invalid_token';
CSRFHelper::verify(); // Should throw exception
```

### Test Rate Limiting
```php
// Test 1: Check rate limit
$result = RateLimitHelper::checkLoginAttempts('user@example.com');
echo $result['allowed']; // true or false

// Test 2: Record attempts
for ($i = 0; $i < 6; $i++) {
    RateLimitHelper::recordLoginAttempt('user@example.com', false);
}
$result = RateLimitHelper::checkLoginAttempts('user@example.com');
echo $result['allowed']; // false (rate limited)
```

### Test Encryption
```php
// Test 1: Encrypt and decrypt
$plaintext = 'sensitive@email.com';
$encrypted = EncryptionHelper::encrypt($plaintext);
$decrypted = EncryptionHelper::decrypt($encrypted);
echo $plaintext === $decrypted; // true
```

### Test DLP
```php
// Test 1: Check if page is sensitive
echo DLPHelper::isSensitivePage('iep'); // true
echo DLPHelper::isSensitivePage('dashboard'); // false

// Test 2: Generate watermark
$watermark = DLPHelper::generateWatermark('John Doe');
echo $watermark; // "John Doe | 2026-05-04 14:30:45 | 192.168.1.100"
```

---

## Troubleshooting

### Encryption Issues
- **Error:** "ENCRYPTION_KEY not set in .env file"
  - **Solution:** Add `ENCRYPTION_KEY` to `.env` file

- **Error:** "Decryption failed"
  - **Solution:** Ensure `ENCRYPTION_KEY` hasn't changed; old data won't decrypt

### CSRF Issues
- **Error:** "CSRF token validation failed"
  - **Solution:** Ensure token is included in form; check token hasn't expired

- **Error:** "Token already used"
  - **Solution:** Tokens are one-time use; generate new token for each form

### Rate Limiting Issues
- **Error:** "Too many login attempts"
  - **Solution:** Wait 15 minutes or clear attempts from database

- **Error:** "Rate limit check failed"
  - **Solution:** Check database connection; rate limiting fails open (allows attempt)

### DLP Issues
- **Screenshot blocking not working**
  - **Solution:** Some browsers may not support all protections; use server-side controls

- **Watermark not visible**
  - **Solution:** Check `dlp_enable_watermark` setting; check CSS z-index

---

## Performance Considerations

### Encryption
- Encryption/decryption adds ~1-2ms per operation
- Use encryption only for sensitive fields
- Consider caching decrypted values in session

### CSRF
- Token generation adds ~0.5ms per request
- Token validation adds ~1ms per request
- Automatic cleanup runs on demand (not scheduled)

### Rate Limiting
- Rate limit check adds ~2-3ms per request
- Database queries indexed for fast lookups
- Automatic cleanup runs on demand

### DLP
- Watermark rendering adds ~0.5ms
- JavaScript protections add ~1-2ms
- No server-side performance impact

---

## Compliance

### Standards Covered
- **OWASP Top 10 2021:**
  - A01:2021 – Broken Access Control (CSRF)
  - A02:2021 – Cryptographic Failures (Encryption)
  - A07:2021 – Identification and Authentication Failures (Rate Limiting)

- **NIST SP 800-63B:**
  - Authentication and Lifecycle Management
  - Cryptographic Mechanisms

- **DepEd Standards:**
  - Data Protection and Privacy
  - Information Security

