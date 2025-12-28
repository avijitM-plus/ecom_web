<?php
require_once 'config.php';

try {
    $stmt = $pdo->query('SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent Users in Database:\n";
    echo "========================\n\n";
    
    if (empty($users)) {
        echo "No users found in database.\n";
    } else {
        foreach ($users as $user) {
            echo "ID: " . $user['id'] . "\n";
            echo "Name: " . $user['full_name'] . "\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Created: " . $user['created_at'] . "\n";
            echo "------------------------\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
