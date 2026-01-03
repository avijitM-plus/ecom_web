# RoboMart - E-commerce Platform

**RoboMart** is a comprehensive, PHP-based e-commerce solution designed for robotics, electronics, and IoT component stores. It offers a modern, responsive frontend for shoppers and a robust backend for administrators.

## 🚀 Features

### for Shoppers:
*   **Dynamic Storefront**: Featured products, new arrivals, and latest tech news.
*   **Advanced Product Catalog**: Filter by Category, Price Range, and search capabilities.
*   **User Accounts**: Secure Registration, Login, Profile Management, and Order History.
*   **Shopping Experience**: Real-time Cart, Wishlist, and streamlined Checkout process (Credit Card/COD).
*   **Responsive Design**: Optimized for mobile and desktop using Tailwind CSS.

### for Administrators:
*   **Interactive Dashboard**: Real-time statistics on Sales, Orders, and Users.
*   **Product Management**: Create, Edit, and Delete products with Multi-Category support.
*   **Order Management**: View, Search, and Update order statuses (Pending, Shipped, Completed).
*   **User Management**: Manage customer accounts and roles.
*   **Blog System**: content management system for posting news and updates.
*   **Category Management**: dynamic control over store categories.

## 🛠️ Tech Stack

*   **Backend**: PHP (Vanilla), MySQL (PDO), Metis Bootstrap 5 Admin Template.
*   **Frontend**: HTML5, Tailwind CSS, JavaScript.
*   **Database**: MySQL/MariaDB.

## ⚡ Quick Start

For detailed installation instructions, please refer to [SETUP.md](SETUP.md).

### 1. Database Setup
Import the schema into your MySQL database:
```bash
mysql -u root -p -e "CREATE DATABASE robomart_db;"
mysql -u root -p robomart_db < database.sql
```

### 2. Configuration
Ensure `config.php` matches your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'robomart_db');
```

### 3. Run the Server
Use the PHP built-in server for development:
```bash
php -S localhost:8000
```
Visit **http://localhost:8000** in your browser.

## 🔑 Default Credentials

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@robomart.com` | `Admin123!` |
| **User** | `user@robomart.com` | `User123!` |

> **Note**: Change these passwords immediately in a production environment.

## 📁 Project Structure

*   `backend/`: Admin panel logic and views.
*   `functions.php`: Core business logic and helper functions.
*   `styles.css`: Custom overlays and styles.
*   `index.php`: Main storefront.
*   `products.php`: Product catalog.
