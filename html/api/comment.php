<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST 메서드만 지원합니다.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$store_code = $data['store_code'] ?? '';
$nickname = $data['nickname'] ?? '익명';
$content = $data['content'] ?? '';
$rating = $data['rating'] ?? 0;

if (empty($store_code) || empty($content) || $rating === 0) {
    echo json_encode(['success' => false, 'message' => '필수 항목을 모두 입력해주세요.']);
    exit;
}

try {   
    $stmt = $mysqli->prepare("INSERT INTO comments (store_code, nickname, content, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('sssi', $store_code, $nickname, $content, $rating);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true, 'message' => '댓글이 작성되었습니다.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

