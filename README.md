# RoboMart - E-commerce Platform

**RoboMart** is a comprehensive, PHP-based e-commerce solution designed for robotics, electronics, and IoT component stores. It offers a modern, responsive frontend for shoppers and a robust backend for administrators.

## 🚀 Features

### For Shoppers:
- **Dynamic Storefront**: Featured products, new arrivals, and latest tech news
- **Advanced Product Catalog**: Filter by Category, Price Range, and search capabilities
- **User Accounts**: Secure Registration with Email Verification, Login (including Google OAuth), Profile Management, and Order History
- **Shopping Experience**: Real-time Cart, Wishlist, and streamlined Checkout process (Credit Card/COD)
- **Product Reviews**: Rate and review purchased products
- **Responsive Design**: Optimized for mobile and desktop using Tailwind CSS

### For Administrators:
- **Interactive Dashboard**: Real-time statistics on Sales, Orders, and Users
- **Product Management**: Create, Edit, and Delete products with Multi-Category support
- **Order Management**: View, Search, and Update order statuses (Pending, Shipped, Completed)
- **User Management**: Manage customer accounts and roles
- **Blog System**: Content management system for posting news and updates
- **Category Management**: Dynamic control over store categories
- **Coupon System**: Create and manage discount codes
- **Shipping Management**: Configure shipping zones and rates
- **Inventory Tracking**: Monitor stock levels and changes

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+, MySQL/MariaDB (PDO)
- **Frontend**: HTML5, Tailwind CSS, JavaScript
- **Email**: PHPMailer for transactional emails
- **OAuth**: Google Sign-In integration
- **Admin Panel**: Bootstrap 5 based admin template

## ⚡ Quick Start

### 1. Requirements
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer (for dependencies)

### 2. Database Setup
Import the schema into your MySQL database:
```bash
mysql -u root -p -e "CREATE DATABASE robomart_db;"
mysql -u root -p robomart_db < database/database.sql
```

### 3. Install Dependencies
```bash
composer install
```

### 4. Configuration
Update `config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'robomart_db');
```

### 5. Email Configuration (Optional)
For email verification and order invoices, configure SMTP in `config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_PORT', 587);
define('SMTP_FROM_EMAIL', 'noreply@robomart.com');
define('SMTP_FROM_NAME', 'RoboMart');
```

### 6. Google Login Setup (Optional)
To enable "Sign in with Google":
1. Obtain OAuth 2.0 Client ID and Secret from Google Cloud Console
2. Add `http://localhost:8000/google-callback.php` to authorized redirect URIs
3. Update `includes/google-config.php` with your credentials

### 7. Run the Server
Use the PHP built-in server for development:
```bash
php -S localhost:8000
```
Visit **http://localhost:8000** in your browser.

## 🔑 Default Credentials

| Role      | Email                  | Password     |
|-----------|------------------------|--------------|
| **Admin** | `admin@robomart.com`   | `Admin123!`  |
| **User**  | `user@test.com`        | `password123`|

> **Note**: Change these passwords immediately in a production environment.

## 📁 Project Structure

```
robomart/
├── backend/           # Admin panel (dashboard, products, orders, users, etc.)
├── database/          # SQL schema and migrations
├── includes/          # Shared components (header, footer, google-config)
├── vendor/            # Composer dependencies
├── config.php         # Database and app configuration
├── functions.php      # Core business logic and helper functions
├── index.php          # Main storefront
├── products.php       # Product catalog
├── product-details.php# Single product view with reviews
├── cart.php           # Shopping cart
├── checkout.php       # Checkout process
├── login.php          # User login
├── register.php       # User registration with email verification
├── verify.php         # Email verification page
├── account.php        # User account dashboard
└── styles.css         # Custom styles
```

## 🔒 Security Features

- Password hashing with `password_hash()` (bcrypt)
- Prepared statements for all database queries (SQL injection prevention)
- XSS protection with `htmlspecialchars()`
- CSRF protection on forms
- Email verification for new accounts
- Session-based authentication

## 📧 Email Features

- **Email Verification**: New users receive a 6-digit verification code
- **Order Invoices**: Automatic invoice emails after successful orders
- **Uses PHPMailer**: Reliable SMTP email delivery

## 📝 License

This project is for educational purposes.

---
Made with ❤️ for DUET CSE
