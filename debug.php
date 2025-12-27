<?php
/**
 * Database Diagnostic Tool
 * Check database connection and user data
 */

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>RoboMart Database Diagnostic</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f4f4f4;padding:10px;border-radius:5px;}</style>";

// Test 1: Check if config.php exists
echo "<h2>1. Configuration File Check</h2>";
if (file_exists('config.php')) {
    echo "<p class='success'>✓ config.php exists</p>";
} else {
    echo "<p class='error'>✗ config.php NOT found</p>";
    exit;
}

// Test 2: Try to include config
try {
    require_once 'config.php';
    echo "<p class='success'>✓ config.php loaded successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error loading config.php: " . $e->getMessage() . "</p>";
    exit;
}

// Test 3: Check PDO connection
echo "<h2>2. Database Connection Check</h2>";
if (isset($pdo)) {
    echo "<p class='success'>✓ PDO connection object exists</p>";
    
    try {
        $pdo->query("SELECT 1");
        echo "<p class='success'>✓ Database connection is active</p>";
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
        exit;
    }
} else {
    echo "<p class='error'>✗ PDO connection object not found</p>";
    exit;
}

// Test 4: Check database and tables
echo "<h2>3. Database Structure Check</h2>";
try {
    // Check database
    $stmt = $pdo->query("SELECT DATABASE()");
    $db = $stmt->fetchColumn();
    echo "<p class='success'>✓ Connected to database: <strong>$db</strong></p>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ 'users' table exists</p>";
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        echo "<p class='info'>ℹ Total users in database: <strong>$count</strong></p>";
        
        // Show table structure
        echo "<h3>Users Table Structure:</h3>";
        $stmt = $pdo->query("DESCRIBE users");
        echo "<pre>";
        while ($row = $stmt->fetch()) {
            echo sprintf("%-20s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Null']);
        }
        echo "</pre>";
        
    } else {
        echo "<p class='error'>✗ 'users' table does NOT exist</p>";
        echo "<p class='info'>Please import database.sql</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Database query failed: " . $e->getMessage() . "</p>";
}

// Test 5: List all users (without passwords)
echo "<h2>4. Registered Users</h2>";
try {
    $stmt = $pdo->query("SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Created At</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ No users registered yet</p>";
        echo "<p>Try registering at: <a href='register.php'>register.php</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Error fetching users: " . $e->getMessage() . "</p>";
}

// Test 6: Check session
echo "<h2>5. Session Check</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='success'>✓ Session is active</p>";
    echo "<p class='info'>Session ID: " . session_id() . "</p>";
    
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        echo "<p class='success'>✓ User is logged in</p>";
        echo "<p class='info'>User ID: " . htmlspecialchars($_SESSION['user_id'] ?? 'N/A') . "</p>";
        echo "<p class='info'>Email: " . htmlspecialchars($_SESSION['email'] ?? 'N/A') . "</p>";
        echo "<p class='info'>Name: " . htmlspecialchars($_SESSION['full_name'] ?? 'N/A') . "</p>";
    } else {
        echo "<p class='info'>ℹ No user currently logged in</p>";
    }
} else {
    echo "<p class='error'>✗ Session is not active</p>";
}

// Test 7: PHP Info
echo "<h2>6. PHP Configuration</h2>";
echo "<p class='info'>PHP Version: <strong>" . phpversion() . "</strong></p>";
echo "<p class='info'>PDO MySQL Driver: <strong>" . (extension_loaded('pdo_mysql') ? 'Installed ✓' : 'NOT Installed ✗') . "</strong></p>";

echo "<hr>";
echo "<h2>Quick Actions</h2>";
echo "<p><a href='register.php'>→ Go to Registration</a></p>";
echo "<p><a href='login.php'>→ Go to Login</a></p>";
echo "<p><a href='account.php'>→ Go to Account (requires login)</a></p>";
echo "<p><a href='logout.php'>→ Logout</a></p>";
?>
