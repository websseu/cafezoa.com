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
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>전국 스타벅스 현황 | 관리자 페이지</title>
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
            <a href="/admin/admin.php">← 목록으로 돌아가기</a>
        </div>

        <!-- 스타벅스 지역별 개수 표시 -->
        <div class="stats-section">
            <h2>☕ 전국 스타벅스 현황</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="center">날짜</th>
                            <th class="center">전체</th>
                            <th class="center">서울</th>
                            <th class="center">경기</th>
                            <th class="center">부산</th>
                            <th class="center">대구</th>
                            <th class="center">인천</th>
                            <th class="center">광주</th>
                            <th class="center">대전</th>
                            <th class="center">울산</th>
                            <th class="center">세종</th>
                            <th class="center">경남</th>
                            <th class="center">경북</th>
                            <th class="center">충남</th>
                            <th class="center">충북</th>
                            <th class="center">강원</th>
                            <th class="center">전북</th>
                            <th class="center">전남</th>
                            <th class="center">제주</th>
                        </tr>
                    </thead>
                    <tbody id="stats-tbody">
                        <tr>
                            <td colspan="18" class="center" style="padding: 40px; color: #999;">
                                데이터를 불러오는 중...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // 날짜를 YYYY-MM-DD 형식으로 변환
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // 테이블 행 생성 함수
        function createTableRow(data, isLatest = false) {
            const tr = document.createElement('tr');
            const rowStyle = isLatest ? 'background: #f0fdf4;' : '';
            
            tr.innerHTML = `
                <td class="center" style="${rowStyle}">${data['날짜']}</td>
                <td class="center" style="color: #00704A; ${rowStyle}">${data['전체'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['서울'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['경기'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['부산'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['대구'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['인천'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['광주'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['대전'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['울산'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['세종'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['경남'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['경북'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['충남'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['충북'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['강원'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['전북'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['전남'].toLocaleString()}</td>
                <td class="center" style="${rowStyle}">${data['제주'].toLocaleString()}</td>
            `;
            
            return tr;
        }

        // "아직 없다" 메시지 행 생성
        function createNoDataRow(dateStr) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="center">${dateStr}</td>
                <td colspan="18" class="center">
                    📅 ${dateStr} 데이터가 아직 업데이트되지 않았습니다.
                </td>
            `;
            return tr;
        }

        // 스타벅스 지역별 개수 데이터 불러오기
        async function loadStarbucksStats() {
            const tbody = document.getElementById('stats-tbody');
            
            // 시작 날짜 (2025-10-16)
            const startDate = new Date('2025-10-16');
            const today = new Date();
            
            
            tbody.innerHTML = ''; // 로딩 메시지 제거
            
            if (today < startDate) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="18" class="center" style="padding: 40px; color: #856404; background: #fff3cd;">
                            📅 데이터 수집은 2025-10-16부터 시작됩니다.
                        </td>
                    </tr>
                `;
                return;
            }
            
            // 10-16부터 오늘까지 모든 날짜 포함 (16일 포함되게 수정)
            const datesToTry = [];
            for (let d = new Date(today); d >= startDate; d.setDate(d.getDate() - 1)) {
                datesToTry.push(new Date(d.getTime())); // 복제해서 16일 포함
            }
            
            
            for (let i = 0; i < datesToTry.length; i++) {
                const date = datesToTry[i];
                const dateStr = formatDate(date);
                const isLatest = i === 0; // 오늘 데이터 강조
                
                const url = `https://websseu.github.io/data_starbucks/location/count/starbucks-count_${dateStr}.json`;
                
                try {
                    const response = await fetch(url);
                    if (response.ok) {
                        const data = await response.json();
                        tbody.appendChild(createTableRow(data, isLatest));
                    } else {
                        tbody.appendChild(createNoDataRow(dateStr));
                    }
                } catch (error) {
                    console.error(`❌ ${dateStr} 데이터 로드 에러:`, error);
                    tbody.appendChild(createNoDataRow(dateStr));
                }
            }
            
            console.log('✅ 모든 데이터 로드 완료');
        }
        
        document.addEventListener('DOMContentLoaded', loadStarbucksStats);
    </script>
</body>
</html>
