# SPED LMS — Setup and Testing Guide

## XAMPP Setup Instructions

### Prerequisites
- XAMPP installed (with Apache and MySQL)
- PHP 7.4 or higher
- Composer installed

---

## Step 1: Database Setup

### 1.1 Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Verify both services are running (green indicators)

### 1.2 Create Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on **"New"** in the left sidebar
3. Database name: `sped_lms`
4. Collation: `utf8mb4_unicode_ci`
5. Click **"Create"**

### 1.3 Import Schema
**Option A: Using phpMyAdmin**
1. Select the `sped_lms` database
2. Click on **"Import"** tab
3. Click **"Choose File"** and select: `config/schema.sql`
4. Click **"Go"** at the bottom
5. Wait for success message

**Option B: Using Command Line**
```bash
# Navigate to your project directory
cd C:/xampp/htdocs/Signedd

# Import schema
C:/xampp/mysql/bin/mysql -u root -p sped_lms < config/schema.sql
# Press Enter when prompted for password (default is empty)
```

---

## Step 2: Install PHP Dependencies

Open Command Prompt or Terminal in your project directory:

```bash
# Navigate to project
cd C:/xampp/htdocs/Signedd

# Install dependencies using Composer
composer install
```

If you don't have Composer installed globally, use the included composer.phar:
```bash
php composer.phar install
```

---

## Step 3: Configure Environment

The `.env` file has been created with XAMPP defaults. Review and update if needed:

### Required Updates:
1. **Email Settings** (if you want to test email notifications):
   - Update `MAIL_USERNAME` with your Gmail address
   - Update `MAIL_PASSWORD` with your Gmail App Password
   - [How to get Gmail App Password](https://support.google.com/accounts/answer/185833)

2. **Encryption Key** (for production):
   - Generate a secure key: `openssl rand -base64 32`
   - Replace `ENCRYPTION_KEY` value

3. **Google OAuth** (optional, for Google login):
   - Get credentials from [Google Cloud Console](https://console.cloud.google.com/)
   - Update `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`

---

## Step 4: Set File Permissions

Ensure these directories are writable:

```bash
# Windows (run as Administrator in Command Prompt)
icacls logs /grant Everyone:F
icacls public/uploads /grant Everyone:F /T
```

Or manually:
1. Right-click on `logs` folder → Properties → Security
2. Edit permissions → Add "Everyone" → Full Control
3. Repeat for `public/uploads` folder

---

## Step 5: Access the Application

1. Open your browser
2. Navigate to: `http://localhost/Signedd/public/`
3. You should see the login page

### Default Admin Account
- **Email:** `admin@spedlms.local`
- **Password:** `password` (default Laravel hash)

**⚠️ IMPORTANT:** Change the admin password immediately after first login!

---

## Step 6: Verify Installation

### 6.1 Check Database Connection
Create a test file: `test-db.php` in the project root:

```php
<?php
require_once 'config/db.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    echo "✓ Database connection successful!\n";
    echo "✓ Users table exists\n";
    echo "✓ Found {$result['count']} user(s)\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
```

Run it:
```bash
php test-db.php
```

Expected output:
```
✓ Database connection successful!
✓ Users table exists
✓ Found 1 user(s)
```

### 6.2 Check PHP Extensions
```bash
php -m
```

Verify these extensions are enabled:
- ✓ PDO
- ✓ pdo_mysql
- ✓ mbstring
- ✓ openssl
- ✓ json
- ✓ fileinfo

### 6.3 Check Composer Autoload
```bash
php -r "require 'vendor/autoload.php'; echo 'Autoload OK';"
```

---

## Common Issues and Solutions

### Issue 1: "Access denied for user 'root'@'localhost'"
**Solution:** Update `.env` file with correct MySQL credentials
```env
DB_USER=root
DB_PASS=your_mysql_password
```

### Issue 2: "Database 'sped_lms' doesn't exist"
**Solution:** Create the database in phpMyAdmin first (Step 1.2)

### Issue 3: "Class 'Database' not found"
**Solution:** Run `composer install` to generate autoload files

### Issue 4: "Permission denied" on logs folder
**Solution:** Set folder permissions (Step 4)

### Issue 5: Port 80 already in use
**Solution:** 
1. Stop IIS or other web servers
2. Or change Apache port in XAMPP config
3. Update `APP_URL` in `.env` accordingly

### Issue 6: "Headers already sent" error
**Solution:** Check for whitespace before `<?php` tags in PHP files

---

## Testing Checklist

After setup, test these features:

### Authentication
- [ ] Can access login page
- [ ] Can register new user
- [ ] Can login with admin account
- [ ] Can logout successfully

### Database
- [ ] All tables created (check phpMyAdmin)
- [ ] Default admin user exists
- [ ] DLP settings populated

### File System
- [ ] Can upload files (test enrollment documents)
- [ ] Logs directory is writable
- [ ] Uploads directory is writable

### Email (Optional)
- [ ] Can send test email
- [ ] Email verification works
- [ ] Role request notifications work

---

## Next Steps

Once setup is complete:

1. **Change Admin Password**
   - Login as admin
   - Go to Profile → Change Password

2. **Configure System Settings**
   - Admin Dashboard → Settings
   - Review session timeout, login attempts, etc.

3. **Create Test Users**
   - Register as Parent
   - Register as SPED Teacher
   - Test role request workflow

4. **Review Documentation**
   - Read `DOCUMENTATION-INDEX.md` for feature guides
   - Check `CHANGELOG.md` for implemented features

---

## Development Mode

For development, you can enable error reporting:

Edit `public/index.php` (if exists) or create `.htaccess`:
```apache
php_flag display_errors on
php_value error_reporting E_ALL
```

**⚠️ Disable in production!**

---

## Support

If you encounter issues:
1. Check XAMPP error logs: `C:/xampp/apache/logs/error.log`
2. Check PHP error logs: `C:/xampp/php/logs/php_error_log`
3. Check application logs: `logs/activity.log`

---

**Last Updated:** 2026-05-05
**Version:** Initial Setup Guide
