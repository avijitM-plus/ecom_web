<?php
require_once 'config.php';

try {
    $sql = "ALTER TABLE users 
            ADD COLUMN phone VARCHAR(20) DEFAULT NULL,
            ADD COLUMN address VARCHAR(255) DEFAULT NULL,
            ADD COLUMN city VARCHAR(100) DEFAULT NULL,
            ADD COLUMN postal_code VARCHAR(20) DEFAULT NULL,
            ADD COLUMN country VARCHAR(100) DEFAULT NULL";
    
    $pdo->exec($sql);
    echo "Successfully added address columns to users table.";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>
