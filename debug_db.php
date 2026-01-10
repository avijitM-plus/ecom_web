<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Debug</h1>";

// Check extensions
echo "<h2>Extensions</h2>";
$extensions = get_loaded_extensions();
echo "PDO Loaded: " . (in_array('PDO', $extensions) ? 'Yes' : 'No') . "<br>";
echo "pdo_mysql Loaded: " . (in_array('pdo_mysql', $extensions) ? 'Yes' : 'No') . "<br>";

// Check Connection
echo "<h2>Connection Attempt</h2>";
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'robomart_db';

try {
    echo "Attempting connection to $host with user $user...<br>";
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "Connected to MySQL server successfully.<br>";
    
    echo "Attempting to select database $db...<br>";
    $pdo->exec("USE $db");
    echo "Database selected successfully.<br>";
    
    // List tables
    echo "<h3>Tables:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "<br>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color:red'><strong>Error:</strong> " . $e->getMessage() . "</div>";
    echo "Code: " . $e->getCode() . "<br>";
}
?>
