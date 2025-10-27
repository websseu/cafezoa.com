<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$store_code = $_GET['store_code'] ?? '';

if (empty($store_code)) {
    echo json_encode(['success' => false, 'message' => 'Store code is required']);
    exit;
}

try {
    // 조회수 증가
    $stmt = $mysqli->prepare("UPDATE stores SET view_count = view_count + 1 WHERE store_code = ?");
    $stmt->bind_param('s', $store_code);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

