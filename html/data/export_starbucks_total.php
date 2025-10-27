<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: text/plain; charset=utf-8');

// ===============================
// 데이터베이스에서 데이터 가져오기
// ===============================
$query = "
    SELECT 
        store_code,
        name,
        address,
        region as city,
        city as district,
        latitude,
        longitude,
        parking_available
    FROM stores 
    WHERE brand_id = 1
    ORDER BY region, city, name
";

$result = $mysqli->query($query);

if (!$result) {
    die("❌ 쿼리 실행 실패: " . $mysqli->error . "\n");
}

$stores = [];
$count = 0;

while ($row = $result->fetch_assoc()) {
    // latitude와 longitude를 문자열로 변환
    $row['latitude'] = (string)$row['latitude'];
    $row['longitude'] = (string)$row['longitude'];
    
    $stores[] = $row;
    $count++;
}

// ===============================
// JSON 데이터 생성
// ===============================
$jsonData = [
    'kind' => 'Korea Starbucks',
    'date' => date('Y-m-d'),
    'location' => '전국(total)',
    'count' => $count,
    'item' => $stores
];

// ===============================
// JSON 파일로 저장
// ===============================
$jsonFile = __DIR__ . '/starbucks-total.json';
$jsonString = json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

if (file_put_contents($jsonFile, $jsonString) === false) {
    die("❌ JSON 파일 저장 실패\n");
}

echo "✅ starbucks-total.json 파일이 생성되었습니다.\n";
echo "📊 총 {$count}개 매장 데이터가 포함되었습니다.\n";
echo "📁 파일 위치: {$jsonFile}\n";
?>

