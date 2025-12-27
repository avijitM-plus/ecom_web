# RoboMart PHP Authentication System - Setup Guide

## Prerequisites

Before setting up the authentication system, ensure you have the following installed:

1. **PHP** (version 7.4 or higher)
   - Check version: `php -v`
   - Download from: https://www.php.net/downloads

2. **MySQL** (version 5.7 or higher) or **MariaDB**
   - Check version: `mysql --version`
   - Download MySQL: https://dev.mysql.com/downloads/
   - Or use XAMPP/WAMP which includes both PHP and MySQL

3. **Web Server** (Apache, Nginx, or PHP built-in server)
   - For development, you can use PHP's built-in server

## Installation Steps

### Step 1: Database Setup

1. **Start MySQL Server**
   ```bash
   # On Windows with XAMPP
   # Start XAMPP Control Panel and start MySQL
   
   # On Linux/Mac
   sudo systemctl start mysql
   ```

2. **Create Database**
   
   **Option A: Using MySQL Command Line**
   ```bash
   mysql -u root -p
   ```
   
   Then run:
   ```sql
   source database.sql
   ```
   
   **Option B: Using phpMyAdmin**
   - Open phpMyAdmin (usually at http://localhost/phpmyadmin)
   - Click "Import" tab
   - Select `database.sql` file
   - Click "Go"

3. **Verify Database Creation**
   ```bash
   mysql -u root -p -e "USE robomart_db; SHOW TABLES;"
   ```
   
   You should see:
   ```
   +------------------------+
   | Tables_in_robomart_db  |
   +------------------------+
   | users                  |
   | user_sessions          |
   +------------------------+
   ```

### Step 2: Configure Database Connection

1. Open `config.php`

2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');     // Usually 'localhost'
   define('DB_USER', 'root');          // Your MySQL username
   define('DB_PASS', '');              // Your MySQL password
   define('DB_NAME', 'robomart_db');   // Database name
   ```

3. **Common Configurations:**
   - **XAMPP/WAMP**: Usually `root` with empty password
   - **Production**: Use strong credentials and never use `root`

### Step 3: File Permissions (Linux/Mac only)

```bash
# Make sure PHP can read the files
chmod 644 *.php
chmod 755 .
```

### Step 4: Start the Application

**Option A: Using PHP Built-in Server (Recommended for Development)**

```bash
# Navigate to your project directory
cd "c:\Users\Pc\OneDrive - duet.ac.bd\duet\CSE-3-1\web"

# Start PHP server on port 8000
php -S localhost:8000
```

Then open your browser and go to:
- http://localhost:8000/register.php
- http://localhost:8000/login.php

**Option B: Using XAMPP/WAMP**

1. Copy your project folder to:
   - XAMPP: `C:\xampp\htdocs\web`
   - WAMP: `C:\wamp64\www\web`

2. Start Apache and MySQL from XAMPP/WAMP Control Panel

3. Open browser and go to:
   - http://localhost/web/register.php
   - http://localhost/web/login.php

## Testing the System

### 1. Test Registration

1. Navigate to `http://localhost:8000/register.php`
2. Fill out the form:
   - Full Name: Test User
   - Email: test@example.com
   - Password: Test123!@# (must meet requirements)
   - Confirm Password: Test123!@#
   - Check "Terms of Service"
3. Click "Create Account"
4. You should see a success message

### 2. Verify Database Entry

```bash
mysql -u root -p -e "SELECT id, full_name, email, created_at FROM robomart_db.users;"
```

You should see your test user with a hashed password (NOT plain text).

### 3. Test Login

1. Navigate to `http://localhost:8000/login.php`
2. Enter credentials:
   - Email: test@example.com
   - Password: Test123!@#
3. Click "Sign In"
4. You should be redirected to `account.php` showing your account dashboard

### 4. Test Session Protection

1. After logging in, you should be on `account.php`
2. Try opening `account.php` in a new incognito/private window
3. You should be redirected to `login.php` (not logged in)

### 5. Test Logout

1. While logged in, click "Sign Out" or navigate to `logout.php`
2. You should be redirected to home page
3. Try accessing `account.php` again - you should be redirected to login

## File Structure

```
web/
├── config.php              # Database configuration
├── functions.php           # Utility functions
├── database.sql            # Database schema
├── register.php            # Registration page
├── login.php               # Login page
├── logout.php              # Logout script
├── account.php             # Protected account page
├── index.html              # Home page
└── SETUP.md               # This file
```

## Security Features

✅ **Password Hashing**: Uses bcrypt with cost factor 12
✅ **SQL Injection Protection**: PDO prepared statements
✅ **XSS Protection**: Input sanitization and output escaping
✅ **Session Security**: Session regeneration on login
✅ **CSRF Protection**: Can be enhanced with tokens
✅ **Session Timeout**: 1 hour inactivity timeout

## Troubleshooting

### Error: "Database connection failed"

**Solution:**
1. Check MySQL is running
2. Verify credentials in `config.php`
3. Ensure database `robomart_db` exists
4. Check PHP PDO MySQL extension is enabled:
   ```bash
   php -m | grep pdo_mysql
   ```

### Error: "Call to undefined function password_hash()"

**Solution:**
- Upgrade to PHP 5.5 or higher
- Check: `php -v`

### Error: "Headers already sent"

**Solution:**
- Ensure no output before `<?php` tags
- Check for BOM in files (use UTF-8 without BOM)
- No whitespace before `<?php`

### Sessions not working

**Solution:**
1. Check session directory is writable:
   ```bash
   php -i | grep session.save_path
   ```
2. Ensure cookies are enabled in browser
3. Check `session_start()` is called in `config.php`

### Password validation too strict

**Solution:**
- Edit `functions.php`
- Modify `validate_password()` function
- Adjust `PASSWORD_MIN_LENGTH` in `config.php`

## Next Steps

### Recommended Enhancements

1. **Email Verification**
   - Add email verification on registration
   - Prevent login until email is verified

2. **Password Reset**
   - Implement "Forgot Password" functionality
   - Send reset link via email

3. **HTTPS**
   - Use HTTPS in production
   - Never transmit passwords over HTTP

4. **Rate Limiting**
   - Prevent brute force attacks
   - Limit login attempts

5. **Two-Factor Authentication**
   - Add 2FA for enhanced security
   - Use TOTP or SMS codes

6. **Remember Me**
   - Enhance current cookie-based implementation
   - Store tokens in `user_sessions` table

## Production Deployment

⚠️ **Before deploying to production:**

1. Change database credentials in `config.php`
2. Use environment variables for sensitive data
3. Enable HTTPS/SSL
4. Set `display_errors = Off` in php.ini
5. Use strong, unique passwords
6. Regular security updates
7. Implement backup strategy
8. Add CSRF protection
9. Set secure cookie flags
10. Implement logging and monitoring

## Support

For issues or questions:
- Check the troubleshooting section above
- Review PHP error logs
- Check MySQL error logs
- Verify all prerequisites are met

## License

This authentication system is part of the RoboMart e-commerce platform.
