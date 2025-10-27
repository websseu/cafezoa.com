<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: text/plain; charset=utf-8');

// ===============================
// 1️⃣ JSON 파일 로드
// ===============================
$jsonFile = __DIR__ . '/data_chungbuk.json';
if (!file_exists($jsonFile)) {
    die("❌ JSON 파일을 찾을 수 없습니다: {$jsonFile}\n");
}

$jsonData = file_get_contents($jsonFile);
$data = json_decode($jsonData, true);
if (!$data || !isset($data['item'])) {
    die("❌ JSON 파싱 실패 또는 데이터 구조가 올바르지 않습니다.\n");
}

// ===============================
// 2️⃣ 기본 변수 설정
// ===============================
$brand_id = 1; // 스타벅스
$total = count($data['item']);
$inserted = 0;
$updated = 0;

echo "📦 총 {$total}개 매장 데이터를 불러왔습니다.\n";

// ===============================
// 3️⃣ 매장 데이터 반복 삽입
// ===============================
foreach ($data['item'] as $store) {
    $name        = trim($store['name'] ?? '');
    $desc        = trim($store['description'] ?? '');
    $address     = trim($store['address'] ?? '');
    $region      = trim($store['region'] ?? '');
    $city        = trim($store['city'] ?? '');
    $phone       = trim($store['phone'] ?? '');
    $services    = trim(str_replace(',', ', ', $store['services'] ?? ''));
    $hours       = isset($store['hours']) ? implode(', ', $store['hours']) : '';
    $closed      = trim($store['closed'] ?? '');
    $lat         = floatval($store['latitude'] ?? 0);
    $lng         = floatval($store['longitude'] ?? 0);
    $images      = isset($store['images']) ? implode(', ', $store['images']) : '';

    // 주차 정보 - 그대로 사용
    $parking = $store['parking_info'] ?? [];
    $parking_available = trim($parking['available'] ?? '');
    $parking_fee       = trim($parking['fee_type'] ?? '');
    $parking_capacity  = trim((string)($parking['capacity'] ?? ''));
    $parking_detail    = trim($parking['detail'] ?? '');

    // ===============================
    // 4️⃣ 매장 코드 생성
    // ===============================
    $store_code = '1' . str_pad(strval(mt_rand(0, 9999999)), 7, '0', STR_PAD_LEFT);

    // ===============================
    // 5️⃣ 중복 체크: 이름+주소 기준으로 존재 여부 확인
    // ===============================
    $check = $mysqli->prepare("SELECT id FROM stores WHERE name = ? AND address = ? LIMIT 1");
    $check->bind_param('ss', $name, $address);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // ✅ UPDATE
        $existing = $result->fetch_assoc();
        $store_id = $existing['id'];

        $update = $mysqli->prepare("
            UPDATE stores SET
                description = ?, region = ?, city = ?, phone = ?, hours = ?, closed = ?,
                latitude = ?, longitude = ?, services = ?, 
                parking_available = ?, parking_fee = ?, parking_capacity = ?, parking_detail = ?,
                images = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $update->bind_param(
            'ssssssddssssssi',
            $desc,
            $region,
            $city,
            $phone,
            $hours,
            $closed,
            $lat,
            $lng,
            $services,
            $parking_available,
            $parking_fee,
            $parking_capacity,
            $parking_detail,
            $images,
            $store_id
        );

        if ($update->execute()) {
            echo "🔁 업데이트 완료: {$name}\n";
            $updated++;
        } else {
            echo "⚠️ 업데이트 실패: {$name} - {$update->error}\n";
        }
    } else {
        // ✅ INSERT
        $stmt = $mysqli->prepare("
            INSERT INTO stores (
                brand_id, store_code, name, description, address,
                region, city, phone, hours, closed,
                latitude, longitude, services,
                parking_available, parking_fee, parking_capacity, parking_detail,
                images
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ");
        $stmt->bind_param(
            'isssssssssddssssss',
            $brand_id,
            $store_code,
            $name,
            $desc,
            $address,
            $region,
            $city,
            $phone,
            $hours,
            $closed,
            $lat,
            $lng,
            $services,
            $parking_available,
            $parking_fee,
            $parking_capacity, 
            $parking_detail,
            $images
        );

        if ($stmt->execute()) {
            echo "✅ 신규 등록: {$name}\n";
            $inserted++;
        } else {
            echo "❌ 등록 실패: {$name} - {$stmt->error}\n";
        }
    }
}

echo "\n📊 요약\n";
echo "➕ 신규 등록: {$inserted}개\n";
echo "🔁 업데이트: {$updated}개\n";
?>
