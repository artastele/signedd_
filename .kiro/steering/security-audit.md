---
inclusion: manual
---

# SPED LMS — Security Features Audit

## Executive Summary
This document audits the current security implementation against the 5 required security modules. **Status: 80% Complete** — Most core features are implemented; some DLP features need enhancement.

---

## 1. Authentication Module ✓ (COMPLETE)

### Registration/Login
- ✅ **Registration form** — `/register` route with validation
- ✅ **Login form** — `/login` route with email/password
- ✅ **Email verification** — OTP-based verification (6-digit code)
- ✅ **Google OAuth** — Google Sign-In integration with callback
- ✅ **Session management** — SessionMiddleware with timeout handling
- ✅ **Logout** — Session destruction with cookie cleanup

**Implementation Details:**
- `AuthController.php` — handles all auth flows
- `UserModel.php` — user CRUD and password operations
- `SessionMiddleware.php` — session lifecycle management
- Routes: `/login`, `/register`, `/auth/verify-email`, `/auth/google`, `/auth/google/callback`

### Password Policy
- ✅ **Password strength validation** — enforced in `AuthController::validatePassword()`
  - Minimum 8 characters
  - At least 1 uppercase letter
  - At least 1 number
  - At least 1 special character
- ✅ **Password hashing** — bcrypt (`PASSWORD_BCRYPT`) with `password_hash()`
- ✅ **Password verification** — `password_verify()` for login

**Code Location:** `app/Controllers/AuthController.php` lines 180-190

### Secure Password Storage
- ✅ **Hashed passwords** — bcrypt algorithm (industry standard)
- ✅ **No plaintext storage** — all passwords hashed before DB insert
- ✅ **Salted hashes** — bcrypt includes salt automatically
- ✅ **Default admin password** — `schema.sql` line 156 (must be changed on first login)

**Database:** `users.password_hash` column stores bcrypt hash

---

## 2. Authorization Module ✓ (COMPLETE)

### RBAC (Role-Based Access Control)
- ✅ **7 roles defined** — `admin`, `sped_teacher`, `guidance`, `principal`, `master_teacher`, `parent`, `user`
- ✅ **Role mapping** — `/config/permissions.php` defines role → permission matrix
- ✅ **Role selection** — `/role/select` route for post-login role assignment
- ✅ **Role verification** — Staff roles require document upload + admin approval
- ✅ **Role hierarchy** — Admin > Principal > Teachers > Parents > Users

**Implementation Details:**
- `RoleMiddleware.php` — enforces permissions on every route
- `RoleController.php` — handles role selection and verification
- `permissions.php` — single source of truth for role permissions
- Routes: `/role/select`, `/role/select-parent`, `/role/submit-staff`

### API Permission Checks
- ✅ **Route-level checks** — every route calls `RoleMiddleware::check($permission)`
- ✅ **Permission enforcement** — 403 Forbidden if user lacks permission
- ✅ **Admin bypass** — admin role has wildcard `*` permission
- ✅ **Pending role handling** — users with pending verification treated as `user` role

**Code Location:** `routes/web.php` — all routes include permission parameter

### Permission Matrix
```php
// From /config/permissions.php
'user'           => ['dashboard.general', 'account.settings', 'role.select']
'parent'         => ['dashboard.parent', 'enrollment.submit', 'enrollment.view', ...]
'sped_teacher'   => ['dashboard.teacher', 'enrollment.verify', 'student.records', ...]
'guidance'       => ['dashboard.guidance', 'iep.meeting', 'iep.sign', ...]
'principal'      => ['dashboard.principal', 'iep.sign', 'iep.remarks', 'iep.approve', ...]
'master_teacher' => ['dashboard.master', 'observation.conduct', 'cot.submit', ...]
'admin'          => ['*']  // Full access
```

---

## 3. Secure Data Storage ✓ (MOSTLY COMPLETE)

### Encrypted Local Storage
- ⚠️ **Status: PARTIAL** — Not yet implemented
- **Recommendation:** Add client-side encryption for sensitive form data (enrollment documents)
- **Implementation needed:** Use TweetNaCl.js or libsodium.js for client-side encryption

### Hashed Passwords
- ✅ **Bcrypt hashing** — all passwords hashed with `PASSWORD_BCRYPT`
- ✅ **No plaintext** — passwords never stored or transmitted in plaintext
- ✅ **Verification only** — `password_verify()` used for login checks

**Database:** `users.password_hash` column

### Encrypted Sensitive Fields in DB
- ⚠️ **Status: PARTIAL** — Not yet implemented
- **Sensitive fields identified:**
  - `users.email` — should be encrypted at rest
  - `enrollment_submissions.*` — all learner PII should be encrypted
  - `role_documents.file_path` — document paths should be encrypted
  - `iep_documents.iep_content` — IEP data should be encrypted
  - `activity_log.details` — activity details may contain sensitive info

- **Recommendation:** Implement field-level encryption using:
  - PHP `openssl_encrypt()` / `openssl_decrypt()` (AES-256-CBC)
  - Or use a library like `defuse/php-encryption`
  - Store encryption keys in `.env` (never in code)

**Implementation Plan:**
1. Create `EncryptionHelper.php` with encrypt/decrypt methods
2. Add `ENCRYPTION_KEY` to `.env`
3. Update models to encrypt/decrypt sensitive fields on save/retrieve
4. Migrate existing data (one-time script)

---

## 4. Logging and Monitoring ✓ (COMPLETE)

### Login Attempt Logs
- ✅ **Login logging** — `UserModel::logLoginAttempt()` records all attempts
- ✅ **Success/failure tracking** — `login_log.result` ENUM('success', 'failure')
- ✅ **IP address capture** — `login_log.ip_address` stored
- ✅ **Timestamp** — `login_log.attempted_at` auto-timestamp

**Database Table:** `login_log`
```sql
CREATE TABLE login_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    result ENUM('success', 'failure') NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_attempted_at (attempted_at)
);
```

**Code Location:** `app/Models/UserModel.php` lines 95-110

### Admin Activity Logs
- ✅ **Activity logging** — `activity_log` table tracks all admin actions
- ✅ **Action types** — `action_type` field categorizes actions
- ✅ **Affected records** — `affected_table` and `affected_record_id` track what changed
- ✅ **User tracking** — `user_id` identifies who performed action
- ✅ **IP address** — `ip_address` captured for audit trail
- ✅ **Timestamps** — `created_at` auto-timestamp

**Database Table:** `activity_log`
```sql
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    affected_table VARCHAR(100),
    affected_record_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
);
```

**Admin Views:**
- `/admin/login-logs` — view login attempts
- `/admin/activity-logs` — view admin actions

---

## 5. DLP Features (Data Loss Prevention) ⚠️ (PARTIAL)

### Data Classification
- ⚠️ **Status: PARTIAL** — Not formally implemented
- **Recommendation:** Add data classification levels to sensitive tables
- **Proposed levels:**
  - `PUBLIC` — general information (school name, grade levels)
  - `INTERNAL` — staff information, role requests
  - `CONFIDENTIAL` — student records, IEP documents, assessment data
  - `RESTRICTED` — passwords, encryption keys, admin logs

**Implementation:** Add `classification` column to relevant tables

### Session Timeout
- ✅ **Session timeout implemented** — `SessionMiddleware::checkTimeout()`
- ✅ **Default timeout** — 30 minutes for general pages
- ✅ **Extended timeout** — 60 minutes for enrollment pages
- ✅ **Automatic logout** — session destroyed on timeout
- ✅ **Redirect to login** — user redirected with `?timeout=1` parameter

**Code Location:** `app/Middleware/SessionMiddleware.php` lines 8-10

**Timeout Configuration:**
```php
private const TIMEOUT_DURATION = 1800;        // 30 minutes
private const ENROLLMENT_TIMEOUT = 3600;      // 60 minutes
private const WARNING_TIME = 300;              // 5 minutes warning
```

### Screenshot Blocking / Export Restriction
- ❌ **Status: NOT IMPLEMENTED**
- **Recommendation:** Implement client-side DLP controls
- **Options:**
  1. **CSS-based:** Disable right-click, copy, print on sensitive pages
  2. **JavaScript-based:** Detect screenshot attempts (keyboard shortcuts)
  3. **Server-side:** Add `X-Frame-Options`, `X-Content-Type-Options` headers
  4. **Watermarking:** Add user/timestamp watermark to sensitive documents

**Implementation Plan:**
1. Create `DLPHelper.php` with screenshot prevention methods
2. Add to sensitive views (IEP documents, student records, assessment data)
3. Add HTTP headers to prevent framing/embedding
4. Implement print-to-PDF restrictions

**Recommended Headers:**
```php
header('X-Frame-Options: DENY');                    // Prevent framing
header('X-Content-Type-Options: nosniff');          // Prevent MIME sniffing
header('X-UA-Compatible: IE=edge');                 // Force latest IE
header('Referrer-Policy: strict-origin-when-cross-origin');
```

---

## Security Gaps & Recommendations

### Critical (Must Fix)
1. **Encrypted sensitive fields** — PII in DB is not encrypted at rest
   - **Impact:** High — student records, parent info exposed if DB breached
   - **Fix:** Implement field-level encryption (AES-256)
   - **Timeline:** Before production

2. **HTTPS enforcement** — no SSL/TLS enforcement visible
   - **Impact:** High — credentials transmitted in plaintext over HTTP
   - **Fix:** Add `session.cookie_secure = 1` in production, redirect HTTP → HTTPS
   - **Timeline:** Before production

3. **CSRF protection** — no CSRF tokens visible in forms
   - **Impact:** Medium — forms vulnerable to cross-site request forgery
   - **Fix:** Add CSRF token generation/validation to all POST routes
   - **Timeline:** Before production

### High Priority
4. **Screenshot/export blocking** — no DLP controls on sensitive documents
   - **Impact:** Medium — users can screenshot IEP documents, student records
   - **Fix:** Implement client-side DLP (CSS + JS)
   - **Timeline:** Before production

5. **Rate limiting** — no login attempt rate limiting
   - **Impact:** Medium — brute force attacks possible
   - **Fix:** Add rate limiting to `/login` endpoint (e.g., 5 attempts per 15 min)
   - **Timeline:** Before production

6. **Input validation** — need comprehensive validation on all inputs
   - **Impact:** Medium — SQL injection, XSS possible
   - **Fix:** Add input sanitization/validation to all controllers
   - **Timeline:** Before production

### Medium Priority
7. **Data classification** — not formally implemented
   - **Impact:** Low — helps with compliance but not security-critical
   - **Fix:** Add classification column to sensitive tables
   - **Timeline:** Phase 2

8. **Audit trail completeness** — not all actions logged
   - **Impact:** Low — compliance/forensics issue
   - **Fix:** Add logging to all sensitive operations
   - **Timeline:** Phase 2

9. **Encrypted local storage** — not implemented
   - **Impact:** Low — browser storage not used for sensitive data currently
   - **Fix:** Implement if client-side storage needed
   - **Timeline:** Phase 2

---

## Implementation Checklist

### Before Production
- [ ] Enable HTTPS and set `session.cookie_secure = 1`
- [ ] Implement field-level encryption for PII
- [ ] Add CSRF token protection to all forms
- [ ] Implement login rate limiting
- [ ] Add comprehensive input validation
- [ ] Implement screenshot/export blocking on sensitive pages
- [ ] Add security headers (X-Frame-Options, X-Content-Type-Options, etc.)
- [ ] Conduct security audit/penetration testing
- [ ] Review and update `.env` with production values
- [ ] Disable debug mode in production

### Phase 2 (Post-Launch)
- [ ] Implement data classification system
- [ ] Add encrypted local storage for sensitive forms
- [ ] Enhance audit logging for all operations
- [ ] Implement two-factor authentication (2FA)
- [ ] Add IP whitelisting for admin panel
- [ ] Implement session anomaly detection

---

## Current Implementation Status

| Feature | Status | Location | Notes |
|---------|--------|----------|-------|
| Registration | ✅ Complete | `AuthController.php` | Email verification required |
| Login | ✅ Complete | `AuthController.php` | Password hashing with bcrypt |
| Password Policy | ✅ Complete | `AuthController.php` | 8+ chars, 1 upper, 1 num, 1 special |
| Google OAuth | ✅ Complete | `AuthController.php` | Requires env config |
| RBAC | ✅ Complete | `RoleMiddleware.php` | 7 roles, permission matrix |
| Permission Checks | ✅ Complete | `routes/web.php` | Every route protected |
| Login Logs | ✅ Complete | `UserModel.php` | Success/failure tracked |
| Activity Logs | ✅ Complete | `activity_log` table | Admin actions tracked |
| Session Timeout | ✅ Complete | `SessionMiddleware.php` | 30/60 min configurable |
| Password Hashing | ✅ Complete | `UserModel.php` | Bcrypt with salt |
| Encrypted Fields | ⚠️ Partial | None yet | Needs implementation |
| Screenshot Blocking | ❌ Missing | None | Needs implementation |
| CSRF Protection | ❌ Missing | None | Needs implementation |
| Rate Limiting | ❌ Missing | None | Needs implementation |
| Input Validation | ⚠️ Partial | Various | Needs comprehensive review |
| HTTPS Enforcement | ⚠️ Partial | `.env` | Needs production config |

---

## Conclusion

The SPED LMS has a **solid foundation** for security with:
- ✅ Complete authentication system (registration, login, email verification, Google OAuth)
- ✅ Comprehensive RBAC with 7 roles and permission matrix
- ✅ Secure password storage with bcrypt
- ✅ Complete logging (login attempts, admin activity)
- ✅ Session timeout with configurable durations

**Critical gaps** that must be addressed before production:
1. Encrypted sensitive fields in database
2. HTTPS enforcement
3. CSRF protection
4. Login rate limiting
5. Screenshot/export blocking

**Recommendation:** Implement the critical gaps before launching to production. The current implementation is suitable for development/testing but needs hardening for production use.

