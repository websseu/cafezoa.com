<?php
session_start();

// 로그인 세션 검사
if (empty($_SESSION['admin_auth'])) {
    header("Location: admin-login.php");
    exit;
}

// 세션 만료 검사
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > ($_SESSION['expire_time'] ?? 18000)) {
    session_unset();
    session_destroy();
    header("Location: admin-login.php?expired=1");
    exit;
}

require_once __DIR__ . '/../config.php';

// ===============================
// 1️⃣ 검색 키워드 및 지역 필터 처리
// ===============================
$q = trim($_GET['q'] ?? '');
$selectedRegion = trim($_GET['region'] ?? '');
$searchSql = '';
$params = [];
$paramTypes = '';

if ($q !== '') {
    $searchSql = "WHERE (s.name LIKE CONCAT('%', ?, '%') 
                  OR s.address LIKE CONCAT('%', ?, '%') 
                  OR s.city LIKE CONCAT('%', ?, '%'))";
    $params = [$q, $q, $q];
    $paramTypes = 'sss';
}

if ($selectedRegion !== '') {
    if ($searchSql) {
        $searchSql .= " AND s.region = ?";
    } else {
        $searchSql = "WHERE s.region = ?";
    }
    $params[] = $selectedRegion;
    $paramTypes .= 's';
}

// ===============================
// 2️⃣ 총 데이터 개수 조회
// ===============================
$countSql = "SELECT COUNT(*) as total FROM stores s $searchSql";
$countStmt = $mysqli->prepare($countSql);
if (!empty($params)) $countStmt->bind_param($paramTypes, ...$params);
$countStmt->execute();
$totalCount = $countStmt->get_result()->fetch_assoc()['total'];

// ===============================
// 2-1️⃣ 지역별 매장 개수 조회
// ===============================
$regionCounts = [];
$regionSql = "
    SELECT region, COUNT(*) as count
    FROM stores
    WHERE region IS NOT NULL AND region != ''
    GROUP BY region
    ORDER BY count DESC
";
$res = $mysqli->query($regionSql);
while ($row = $res->fetch_assoc()) $regionCounts[] = $row;

// ===============================
// 3️⃣ 매장 데이터 조회
// ===============================
$sql = "
    SELECT 
        s.*, 
        b.name AS brand_name, b.color AS brand_color
    FROM stores s
    LEFT JOIN brands b ON s.brand_id = b.id
    $searchSql
    ORDER BY s.id DESC
    LIMIT 100
";
$stmt = $mysqli->prepare($sql);
if (!empty($params)) $stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>매장 목록 | 관리자 페이지</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="/assets/img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="admin-site">
<header>
    <h1><a href="/admin/admin.php">cafezoa</a></h1>
</header>

<main>
    <div class="admin-count">
        <a href="/admin/admin-count.php">전국 스타벅스 현황</a>
    </div>

    <form class="search" method="get" action="">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="매장명, 주소, 지역으로 검색">
        <button type="submit">검색</button>
    </form>

    <div class="date-wrap">
        <?php if ($selectedRegion !== ''): ?>
            <?= htmlspecialchars($selectedRegion) ?> 지역: <strong><?= number_format($totalCount) ?></strong>개
            <a href="/admin/admin.php" style="margin-left:10px;color:#999;font-size:13px;">[전체보기]</a>
        <?php elseif ($q !== ''): ?>
            검색 결과: <strong><?= number_format($totalCount) ?></strong>개 (전체 중 검색됨)
        <?php else: ?>
            전체 매장: <strong><?= number_format($totalCount) ?></strong>개
        <?php endif; ?>
    </div>

    <?php if (!empty($regionCounts)): ?>
        <div class="region-count">
            <?php foreach ($regionCounts as $region): ?>
                <a href="?region=<?= urlencode($region['region']) ?>"
                   class="<?= $selectedRegion === $region['region'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($region['region']) ?>(<?= number_format($region['count']) ?>개)
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th class="center">ID</th>
                <th>브랜드</th>
                <th>스토어코드</th>
                <th>매장명</th>
                <th>지역</th>
                <th>도시</th>
                <th>전화번호</th>
                <th>주소</th>
                <th class="center">주차</th>
                <th class="center">대수</th>
                <th class="center">조회수</th>
                <th class="center">댓글수</th>
                <th class="center">관리</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="center"><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['brand_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['store_code'] ?? '') ?></td>
                        <td><a href="/store.php?id=<?= $row['id'] ?>" target="_blank"><?= htmlspecialchars($row['name'] ?? '') ?></a></td>
                        <td><?= htmlspecialchars($row['region'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['city'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                        <td><?= nl2br(htmlspecialchars($row['address'] ?? '')) ?></td>
                        <td class="center"><?= htmlspecialchars($row['parking_fee'] ?? '-') ?></span></td>
                        <td class="center"><?= htmlspecialchars($row['parking_capacity'] ?? '-') ?></td>
                        <td class="center"><?= number_format($row['view_count'] ?? 0) ?></td>
                        <td class="center"><?= number_format($row['comment_count'] ?? 0) ?></td>
                        <td class="center">
                            <button class="btn-view" onclick='openViewModal(<?= json_encode($row) ?>)'>자세히</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="12" class="center">검색 결과가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- ==================== -->
<!-- 자세히 보기 모달 -->
<!-- ==================== -->
<div id="view-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
    <div style="background:#fff; border-radius:10px; padding:25px; width:800px; max-height:90vh; overflow-y:auto; box-shadow:0 5px 15px rgba(0,0,0,0.3); position:relative;">
        <h2 style="margin-bottom:15px; font-size:20px;">매장 정보</h2>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:14px;">
            <div><strong>매장명:</strong> <span id="view-name"></span></div>
            <div><strong>스토어코드:</strong> <span id="view-store-code"></span></div>
            <div><strong>지역:</strong> <span id="view-region"></span></div>
            <div><strong>도시:</strong> <span id="view-city"></span></div>
            <div style="grid-column:1 / span 2;"><strong>주소:</strong> <span id="view-address"></span></div>
            <div><strong>위도:</strong> <span id="view-latitude"></span></div>
            <div><strong>경도:</strong> <span id="view-longitude"></span></div>
            <div style="grid-column:1 / span 2;"><strong>전화번호:</strong> <span id="view-phone"></span></div>
            <div style="grid-column:1 / span 2;"><strong>영업시간:</strong> <span id="view-hours"></span></div>
            <div style="grid-column:1 / span 2;"><strong>휴무:</strong> <span id="view-closed"></span></div>
            <div style="grid-column:1 / span 2;"><strong>서비스:</strong> <span id="view-services"></span></div>
            <div><strong>주차 가능:</strong> <span id="view-parking-available"></span></div>
            <div><strong>주차 요금:</strong> <span id="view-parking-fee"></span></div>
            <div><strong>주차 대수:</strong> <span id="view-parking-capacity"></span></div>
            <div style="grid-column:1 / span 2;"><strong>주차 상세:</strong> <span id="view-parking-detail"></span></div>
        </div>

        <hr style="margin:20px 0;">

        <div id="view-images-section">
            <h3 style="margin-bottom:10px; font-size:16px;">매장 이미지</h3>
            <div id="view-images" style="display:flex; flex-wrap:wrap; gap:10px;"></div>
        </div>

        <div style="text-align:right; margin-top:20px;">
            <button onclick="closeViewModal()" style="background:#777; color:#fff; border:none; padding:8px 16px; border-radius:5px;">닫기</button>
        </div>
    </div>
</div>

<script>

/* 자세히 보기 모달*/
function openViewModal(data) {
    document.getElementById('view-name').textContent = data.name || '-';
    document.getElementById('view-store-code').textContent = data.store_code || '-';
    document.getElementById('view-region').textContent = data.region || '-';
    document.getElementById('view-city').textContent = data.city || '-';
    document.getElementById('view-address').innerHTML = (data.address || '-').replace(/\n/g, '<br>');
    document.getElementById('view-latitude').textContent = data.latitude || '-';
    document.getElementById('view-longitude').textContent = data.longitude || '-';
    document.getElementById('view-phone').textContent = data.phone || '-';
    document.getElementById('view-hours').innerHTML = (data.hours || '-').replace(/\n/g, '<br>');
    document.getElementById('view-closed').textContent = data.closed || '-';
    document.getElementById('view-services').innerHTML = (data.services || '-').replace(/\n/g, '<br>');
    document.getElementById('view-parking-available').textContent =
        data.parking_available === 'Yes' ? '가능' :
        (data.parking_available === 'No' ? '불가능' : (data.parking_available || '-'));
    document.getElementById('view-parking-fee').textContent = data.parking_fee || '-';
    document.getElementById('view-parking-capacity').textContent = data.parking_capacity || '-';
    document.getElementById('view-parking-detail').innerHTML = (data.parking_detail || '-').replace(/\n/g, '<br>');

    // ===== 이미지 표시 =====
    const imageContainer = document.getElementById('view-images');
    imageContainer.innerHTML = '';
    if (data.images && data.images.trim() !== '') {
        let imgs = [];
        if (data.images.trim().startsWith('[')) {
            try { imgs = JSON.parse(data.images); } catch (e) { imgs = []; }
        } else if (data.images.includes(',')) {
            imgs = data.images.split(',').map(i => i.trim());
        } else {
            imgs = [data.images.trim()];
        }

        if (imgs.length > 0) {
            imgs.forEach(src => {
                const img = document.createElement('img');
                img.src = src.startsWith('http') ? src : 'https://image.istarbucks.co.kr' + src;
                img.alt = data.name || '매장 이미지';
                img.style.width = '120px';
                img.style.height = '120px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '6px';
                img.style.border = '1px solid #eee';
                imageContainer.appendChild(img);
            });
        } else {
            imageContainer.innerHTML = '<p style="color:#999;">이미지가 없습니다.</p>';
        }
    } else {
        imageContainer.innerHTML = '<p style="color:#999;">이미지가 없습니다.</p>';
    }

    document.getElementById('view-modal').style.display = 'flex';
}

function closeViewModal() {
    document.getElementById('view-modal').style.display = 'none';
}
</script>

</body>
</html>
