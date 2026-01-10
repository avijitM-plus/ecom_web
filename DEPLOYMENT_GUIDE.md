# Deployment Guide

If you are hosting this application on a web server (like 000webhost, InfinityFree, Namecheap, etc.), the default "localhost" settings will not work. You must update the configuration to match your hosting provider's details.

## 1. Database Setup
1.  **Export your local database**: Go to `localhost/phpmyadmin`, select `robomart_db`, and click **Export**.
2.  **Create a database on your host**:
    *   Go to your hosting control panel (cPanel, etc.).
    *   Find "MySQL Databases".
    *   Create a new database (e.g., `myusername_robomart`).
    *   Create a new user and password.
    *   **Add the user to the database** and grant all privileges.
3.  **Import the database**:
    *   Open "phpMyAdmin" on your host.
    *   Select your new database.
    *   Click **Import** and upload your exported SQL file (or `database.sql`).

## 2. Configuration (`config.php`)
You need to tell the application how to connect to the new database.

Open `config.php` on your file manager and update these lines with your host's details:

```php
// EXAMPLE - Replace with YOUR real details
define('DB_HOST', 'localhost');             // Often 'localhost', but sometimes specific like 'sql123.host.com'
define('DB_USER', 'id123456_myuser');       // Your hosting database username
define('DB_PASS', 'MySuperSecretPass!23');  // Your hosting database password
define('DB_NAME', 'id123456_robomart');     // Your hosting database name

define('SITE_URL', 'http://your-website.com'); // Your actual website URL
```

## 3. Common Issues

### "Database Connection Error"
*   Double-check the **DB_USER** and **DB_PASS**.
*   Ensure the user has been **added** to the database in your control panel.
*   Check if **DB_HOST** is correct (some free hosts use a URL instead of 'localhost').

### "404 Not Found" or Broken Links
*   Ensure `SITE_URL` in `config.php` matches your domain name exactly.

### "Access Denied for user..."
*   This means your username or password is incorrect. Verify them in your hosting control panel.
