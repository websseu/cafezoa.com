<?php
require_once __DIR__ . '/../config.php';

$code = $_GET['code'] ?? '';
header('Content-Type: application/json; charset=utf-8');

$stmt = $mysqli->prepare("SELECT * FROM stores WHERE store_code = ?");
$stmt->bind_param('s', $code);
$stmt->execute();
$result = $stmt->get_result();
$store = $result->fetch_assoc();

if (!$store) {
    echo json_encode(['error' => 'Store not found']);
    exit;
}

echo json_encode($store, JSON_UNESCAPED_UNICODE);
