<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: text/plain; charset=utf-8');

$jsonFile = __DIR__ . '/data_jeolbuk.json';
if (!file_exists($jsonFile)) {
    die("❌ JSON 파일을 찾을 수 없습니다: {$jsonFile}\n");
}

$jsonData = file_get_contents($jsonFile);
$data = json_decode($jsonData, true);
if (!$data || !isset($data['item'])) {
    die("❌ JSON 파싱 실패 또는 데이터 구조가 올바르지 않습니다.\n");
}

$brand_id = 1;
$total = count($data['item']);
$inserted = 0;
$updated = 0;

echo "📦 총 {$total}개 매장 데이터를 불러왔습니다.\n";

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

    $parking = $store['parking_info'] ?? [];
    $parking_available = trim($parking['available'] ?? '');
    $parking_fee       = trim($parking['fee_type'] ?? '');
    $parking_capacity  = trim((string)($parking['capacity'] ?? ''));
    $parking_detail    = trim($parking['detail'] ?? '');

    $store_code = '1' . str_pad(strval(mt_rand(0, 9999999)), 7, '0', STR_PAD_LEFT);

    $check = $mysqli->prepare("SELECT id FROM stores WHERE name = ? AND address = ? LIMIT 1");
    $check->bind_param('ss', $name, $address);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
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
            $desc, $region, $city, $phone, $hours, $closed,
            $lat, $lng, $services, $parking_available, $parking_fee, $parking_capacity, $parking_detail,
            $images, $store_id
        );

        if ($update->execute()) {
            echo "🔁 업데이트 완료: {$name}\n";
            $updated++;
        } else {
            echo "⚠️ 업데이트 실패: {$name} - {$update->error}\n";
        }
    } else {
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
            $brand_id, $store_code, $name, $desc, $address,
            $region, $city, $phone, $hours, $closed,
            $lat, $lng, $services, $parking_available, $parking_fee, $parking_capacity, 
            $parking_detail, $images
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
