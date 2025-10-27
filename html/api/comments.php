<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$store_code = $_GET['store_code'] ?? '';

if (empty($store_code)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $mysqli->prepare("SELECT * FROM comments WHERE store_code = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $store_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
    
    echo json_encode($comments);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

