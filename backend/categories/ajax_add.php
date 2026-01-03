<?php
require_once '../../config.php';
require_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit;
}

// Get JSON Input or POST
$input = json_decode(file_get_contents('php://input'), true);
$name = isset($input['name']) ? trim($input['name']) : (isset($_POST['name']) ? trim($_POST['name']) : '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}

// Generate Slug
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

// Check uniqueness (if exists, return existing?)
$stmt = $pdo->prepare("SELECT id, name FROM categories WHERE slug = ?");
$stmt->execute([$slug]);
if ($row = $stmt->fetch()) {
    echo json_encode(['success' => true, 'category' => $row, 'existing' => true]); // Return existing
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $stmt->execute([$name, $slug]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'category' => ['id' => $id, 'name' => $name]]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
