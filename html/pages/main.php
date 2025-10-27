<?php
    require_once __DIR__ . '/../config.php';

    $page_title   = "CafeZoa | 내 근처 카페 찾기";
    $page_desc    = "내 주변의 스타벅스, 이디야, 투썸 등 전국 주요 커피숍을 지도에서 한눈에 확인하세요. 위치, 주차, 영업시간까지 빠르게!";
    $page_keyword = "카페조아, CafeZoa, 내 근처 카페, 스타벅스 지도, 커피숍 찾기, 전국 카페, 이디야, 투썸, 블루보틀, 주차 가능 카페, 영업시간";

    require_once __DIR__ . '/../includes/site-head.php';
?>

<body class="site-main">
    <!-- 페이지 로딩 애니메이션 -->
    <!-- <div id="page-loader"></div> -->

    <!-- 왼쪽 패널 -->
    <aside class="aside-left">
        <header>
            <h1><a href="/">CafeZoa</a></h1>
        </header>

        <!-- 카페 목록 영역 -->
        <section class="cafe-group">
            <div id="cafeView" class="cafe-view">지도 불러오는 중...</div>
            <ul id="cafeList" class="cafe-list"></ul>
        </section>

        <footer>
            <small>ⓒ 2025 CafeZoa</small>
        </footer>  
    </aside>

    <!-- 지도 영역 -->
    <main class="main">
        <div id="map"></div>

        <!-- 줌 인디케이터 -->
        <div id="zoomIndicator">15</div>

        <!-- 줌 컨트롤 -->
        <div class="zoom-controls">
            <button id="zoomInBtn" title="확대" class="zoom-btn zoom-in"></button>
            <button id="zoomOutBtn" title="축소" class="zoom-btn zoom-out"></button>
        </div>

        <!-- 내 위치 버튼 -->
        <button id="myLocationBtn" title="내 위치로 이동"></button>
    </main>

    <!-- 오른쪽 패널 -->
    <aside class="aside-right">
        <button class="close-detail-btn">
            <span class="vh">닫기</span>
        </button>
        
        <div class="cafe-detail-container">
            <!-- 이미지 슬라이더 영역 -->
            <div class="swiper cafe-swiper">
                <div class="swiper-wrapper"></div>

                <!-- 페이지네이션 -->
                <div class="swiper-pagination"></div>
            </div>

            <!-- 카페 상세 정보 -->
            <div class="cafe-detail-content">
                <div class="cafe-detail-header">
                    <h2 class="cafe-name">..</h2>
                    <span class="distance-badge">0km</span>
                </div>
                
                <!-- 카페 정보 -->
                <div class="cafe-info-list"></div>

                <!-- 카페 댓글 -->
                <div class="cafe-review-list">
                    <div class="review-header">
                        <h3>리뷰(<span class="comments-count">4</span>)</h3>
                    </div>
                    <div class="review-item">
                        <div class="review-left">
                            <div class="review-user">
                                <div class="avatar">
                                    <img src="/assets/img/emoji/014.png" alt="이모지">
                                </div>
                                <span class="name">김스타</span>
                            </div>
                        </div>
                        <div class="review-right">
                            <div class="review-content">
                                정말 좋은 매장이에요! 직원분들이 친절하시고 커피 맛도 최고입니다.
                            </div>
                            <div class="review-meta">
                                <span class="stars">★★★★★</span>
                                <span class="date">12시간 전</span>
                            </div>
                        </div>
                    </div>

                    <div class="review-form review-form-item">
                        <div class="form-group">
                            <textarea 
                                placeholder="사진과 이름은 랜덤으로 설정됩니다. 간단한 리뷰 및 정보를 공유해주세요!😀" 
                                rows="3"
                                maxlength="300"
                                name="reviewName"
                            ></textarea>
                            <div class="char-count">0/300</div>
                        </div>
                        <div class="form-actions">
                            <div class="rating">
                                <span class="rating-label">평점:</span>
                                <div class="stars">
                                    <button type="button" class="star" data-rating="1">★</button>
                                    <button type="button" class="star" data-rating="2">★</button>
                                    <button type="button" class="star" data-rating="3">★</button>
                                    <button type="button" class="star" data-rating="4">★</button>
                                    <button type="button" class="star" data-rating="5">★</button>
                                </div>
                            </div>
                            <button type="button" class="submit-btn">리뷰 작성</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <script>
        // 기본 지도 설정
        const map = new naver.maps.Map('map', {
            center: new naver.maps.LatLng(37.4979, 127.0276), // 기본: 강남역 근처
            zoom: 15,
            mapTypeControl: true
        });

        // 전역 변수들
        let markers = [];
        let allStores = [];
        let visibleWindows = [];
        let markerWindows = new Map(); // 마커별 인포윈도우 추적
        let myLocation = null; // 내 위치 저장

        // 랜덤 닉네임 생성
        function generateRandomNickname() {
            const surnames = ['김', '이', '박', '최', '정', '강', '조', '윤', '장', '황'];
            const firstNames = ['상연', '가연', '철수', '영희', '민수', '지은', '수진', '현우', '서연', '민준'];
            const randomSurname = surnames[Math.floor(Math.random() * surnames.length)];
            const randomFirstName = firstNames[Math.floor(Math.random() * firstNames.length)];
            return randomSurname + randomFirstName;
        }

        // 랜덤 이모지 번호 생성 (001~030)
        function getRandomEmojiNumber() {
            const num = Math.floor(Math.random() * 30) + 1;
            return String(num).padStart(3, '0');
        }

        // 닉네임 기반 일관된 이모지 번호 생성
        function getEmojiByNickname(nickname) {
            // 간단한 해시 함수로 닉네임을 숫자로 변환
            let hash = 0;
            for (let i = 0; i < nickname.length; i++) {
                const char = nickname.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32bit integer
            }
            // 음수 방지하고 001~030 범위로 매핑
            const num = (Math.abs(hash) % 30) + 1;
            return String(num).padStart(3, '0');
        }

        // 매장 데이터 로드
        async function loadStores() {
            try {
                const response = await fetch('../data/starbucks-total.json');
                const data = await response.json();
                allStores = data.item || [];
                
                // 지도에 마커 표시
                showStores();
                
                // 카페 목록 업데이트
                updateCafeList();
                
                // 로딩 애니메이션 숨기기
                const pageLoader = document.getElementById('page-loader');
                if (pageLoader) {
                    pageLoader.style.display = 'none';
                }
                
            } catch (error) {
                console.error('매장 데이터 로드 실패:', error);
                document.getElementById('cafeView').innerHTML = '데이터 로드 실패';
                
                // 에러 발생 시에도 로딩 애니메이션 숨기기
                const pageLoader = document.getElementById('page-loader');
                if (pageLoader) {
                    pageLoader.style.display = 'none';
                }
            }
        }

        // 매장 마커 표시
        function showStores() {
            // 기존 마커 제거
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            const zoom = map.getZoom();
            
            if (zoom <= 7) {
                // 전국 단위 표시 (서울 234, 경기 434)
                showNationalLevel();
            } else if (zoom <= 11) {
                // 시/도 단위 표시
                showCityLevel();
            } else if (zoom <= 13) {
                // 시군구 단위 표시
                showDistrictLevel();
            } else {
                // 개별 매장 표시
                showIndividualStores();
            }
        }

        // 전국 단위 표시
        function showNationalLevel() {
            // 전국 중심점 (서울 근처)
            const centerPosition = new naver.maps.LatLng(37.5665, 126.9780);
            const totalCount = allStores.length;
            
            const marker = new naver.maps.Marker({
                position: centerPosition,
                map: map,
                title: `스타벅스 전국 (${totalCount}개)`,
                icon: {
                    content: `<div class="markerIcon2">스타벅스 전국(${totalCount})</div>`,
                    anchor: new naver.maps.Point(12, 12)
                }
            });
            
            markers.push(marker);
        }

        // 시/도 단위 표시
        function showCityLevel() {
            const cityStats = {};
            
            // 모든 도시별 매장 수 집계 (거리 제한 없음)
            allStores.forEach(store => {
                const city = store.city;
                if (!cityStats[city]) {
                    cityStats[city] = { count: 0, lat: parseFloat(store.latitude), lng: parseFloat(store.longitude) };
                }
                cityStats[city].count++;
            });

            Object.keys(cityStats).forEach(city => {
                const stats = cityStats[city];
                const position = new naver.maps.LatLng(stats.lat, stats.lng);
                
                const marker = new naver.maps.Marker({
                    position: position,
                    map: map,
                    title: `${city} (${stats.count}개)`,
                    icon: {
                        content: `<div class="markerIcon2">${city}(${stats.count})</div>`,
                        anchor: new naver.maps.Point(12, 12)
                    }
                });
                
                // 시/도 마커 클릭 시 줌 12로 이동
                naver.maps.Event.addListener(marker, 'click', function() {
                    map.setCenter(position);
                    map.setZoom(12);
                });
                
                markers.push(marker);
            });
        }

        // 시군구 단위 표시
        function showDistrictLevel() {
            const center = map.getCenter();
            const districtStats = {};
            
            // 현재 화면에 보이는 지역의 시군구별 매장 수 집계
            allStores.forEach(store => {
                const district = store.district;
                const storePosition = new naver.maps.LatLng(parseFloat(store.latitude), parseFloat(store.longitude));
                const distance = calculateDistance(center, storePosition);
                
                if (distance <= 100) { // 100km 반경으로 확대
                    if (!districtStats[district]) {
                        districtStats[district] = { count: 0, lat: parseFloat(store.latitude), lng: parseFloat(store.longitude) };
                    }
                    districtStats[district].count++;
                }
            });

            Object.keys(districtStats).forEach(district => {
                const stats = districtStats[district];
                const position = new naver.maps.LatLng(stats.lat, stats.lng);
                
                const marker = new naver.maps.Marker({
                    position: position,
                    map: map,
                    title: `${district} (${stats.count}개)`,
                    icon: {
                        content: `<div class="markerIcon2">${district}(${stats.count})</div>`,
                        anchor: new naver.maps.Point(12, 12)
                    }
                });
                
                // 시군구 마커 클릭 시 줌 14로 이동
                naver.maps.Event.addListener(marker, 'click', function() {
                    map.setCenter(position);
                    map.setZoom(14);
                });
                
                markers.push(marker);
            });
        }

        // 개별 매장 표시
        function showIndividualStores() {
            const center = map.getCenter();
            const zoom = map.getZoom();
            
            // 줌 레벨에 따른 반경 설정 (km)
            function getRadiusByZoom(zoom) {
                if (zoom >= 16) return 2;
                if (zoom === 15) return 3;
                if (zoom === 14) return 5;
                if (zoom === 13) return 20;
                if (zoom === 12) return 50;
                return 100;
            }
            
            const radiusKm = getRadiusByZoom(zoom);

            allStores.forEach(store => {
                if (store.latitude && store.longitude) {
                    const storePosition = new naver.maps.LatLng(
                        parseFloat(store.latitude), 
                        parseFloat(store.longitude)
                    );
                    
                    // 거리 계산 (km)
                    const distance = calculateDistance(center, storePosition);
                    
                    // 반경 내에 있는 매장만 표시
                    if (distance <= radiusKm) {
                        const marker = new naver.maps.Marker({
                            position: storePosition,
                            map: map,
                            title: store.name,
                            icon: {
                                content: `<div class="markerIcon starbucks"></div>`,
                                anchor: new naver.maps.Point(12, 12)
                            }
                        });
                        
                        // 마커 클릭 이벤트
                        naver.maps.Event.addListener(marker, 'click', function() {
                            showStoreInfo(store);
                        });
                        
                        markers.push(marker);
                    }
                }
            });
        }

        // 두 지점 간의 거리 계산 (km)
        function calculateDistance(pos1, pos2) {
            const R = 6371; // 지구 반지름 (km)
            const dLat = (pos2.lat() - pos1.lat()) * Math.PI / 180;
            const dLng = (pos2.lng() - pos1.lng()) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(pos1.lat() * Math.PI / 180) * Math.cos(pos2.lat() * Math.PI / 180) *
                    Math.sin(dLng/2) * Math.sin(dLng/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // 매장 정보 표시
        function showStoreInfo(store) {
            // 같은 마커에 대한 인포윈도우가 이미 열려있는지 확인
            const marker = markers.find(m => m.getTitle() === store.name);
            if (marker) {
                const existingWindow = markerWindows.get(store.store_code);
                if (existingWindow) {
                    // 이미 열려있으면 닫기
                    existingWindow.close();
                    markerWindows.delete(store.store_code);
                    return;
                }
            }
            
            const parkingClass = store.parking_available === 'Yes' ? 'parking-yes' : 'parking-no';
            
            const infoWindow = new naver.maps.InfoWindow({
                content: `
                    <div class="infoWindow" onclick="focusStore('${store.name}', '${store.store_code}')" data-store-code="${store.store_code}" style="cursor: pointer;">
                        <strong>${store.name}</strong>
                        <span class="address">${store.address}</span>
                        <div class="info-icons">
                            <span class="coffee"></span>
                            <span class="parking ${parkingClass}"></span>
                            <span class="more">상세 정보 보기</span>
                        </div>
                    </div>
                `,
                borderColor: "#00704A",
                borderWidth: 2,
                pixelOffset: new naver.maps.Point(0, 5)
            });
            
            markerWindows.set(store.store_code, infoWindow);
            visibleWindows.push(infoWindow);
            infoWindow.open(map, marker);
        }

        // 카페 목록 업데이트
        function updateCafeList() {
            const cafeView = document.getElementById('cafeView');
            const cafeList = document.getElementById('cafeList');
            const zoom = map.getZoom();
            // 지도 중심 기준 (보여지는 범위)
            const mapCenter = map.getCenter();
            
            // 줌 레벨에 따른 반경 설정
            function getRadiusByZoom(zoom) {
                if (zoom >= 16) return 2;
                if (zoom === 15) return 3;
                if (zoom === 14) return 5;
                if (zoom === 13) return 20;
                if (zoom === 12) return 50;
                return 100;
            }
            
            const radiusKm = getRadiusByZoom(zoom);
            
            // 반경 내 매장들 필터링 (지도 중심 기준)
            const nearbyStores = allStores.filter(store => {
                if (!store.latitude || !store.longitude) return false;
                const storePosition = new naver.maps.LatLng(
                    parseFloat(store.latitude), 
                    parseFloat(store.longitude)
                );
                const distance = calculateDistance(mapCenter, storePosition);
                return distance <= radiusKm;
            });
            
            // cafeView 업데이트
            cafeView.innerHTML = `반경 ${radiusKm}km 안에 <strong>${nearbyStores.length}</strong>개의 매장이 있음`;
            
            // 반경 내 매장들을 목록에 표시
            if (nearbyStores.length === 0) {
                // 매장이 없을 때 안내 메시지
                cafeList.innerHTML = `
                    <li class="no-cafe-message">
                        <span>현재 위치 주변에 매장이 없습니다.</span>
                        <span>지도를 드래그해서 원하는 위치를 선택해보세요!</span>
                    </li>
                `;
            } else {
                cafeList.innerHTML = nearbyStores.map(store => {
                    // 거리 계산 (내 위치 또는 지도 중심 기준)
                    const storePosition = new naver.maps.LatLng(parseFloat(store.latitude), parseFloat(store.longitude));
                    const distanceCenter = myLocation || mapCenter;
                    const distance = calculateDistance(distanceCenter, storePosition);
                    const distanceValue = distance.toFixed(1);
                    
                    return `
                    <li class="cafe-item">
                        <a href="javascript:void(0)" onclick="focusStore('${store.name}', '${store.store_code}')" data-store-code="${store.store_code}">
                            <div class="cafe-thumb">
                                <img src="/assets/img/brands/starbucks.webp" alt="스타벅스" loading="lazy">
                            </div>
                            <div class="cafe-info">
                                <strong class="name">
                                    ${store.name}점
                                    ${distanceValue ? `<span class="distance">${distanceValue}km</span>` : ""}
                                </strong>
                                <p class="address">${store.address}</p>
                            </div>
                        </a>
                    </li>
                    `;
                }).join('');
            }
        }

        // 매장으로 이동 및 infoWindow 표시
        function focusStore(storeName, storeCode) {
            const store = allStores.find(s => s.store_code === storeCode);
            if (!store) return;

            const position = new naver.maps.LatLng(parseFloat(store.latitude), parseFloat(store.longitude));
            // map.setCenter(position);  // 지도 중심을 매장 위치로 이동
            map.setZoom(15);         // 줌 레벨을 15로 설정

            // 인포윈도우 표시
            showStoreInfo(store);

            // 거리 계산 (내 위치 또는 지도 중심 기준)
            const center = myLocation || map.getCenter();
            const distance = calculateDistance(center, position);
            const distanceValue = distance.toFixed(1);

            // 오른쪽 패널 열기 (거리 정보와 함께)
            openRightPanel(storeCode, distanceValue);

            // 주소 변경 (새로고침 없이)
            // history.pushState({ storeCode }, '', `/?store=${storeCode}`);
            history.pushState({ storeCode }, '', `/starbucks/${storeCode}`);
        }

        // 오른쪽 패널 열기
        async function openRightPanel(storeCode, distance = "") {
            const panel = document.querySelector('.aside-right');
            const wrapper = panel.querySelector('.swiper-wrapper');
            const container = panel.querySelector('.cafe-detail-container');

            panel.classList.add('active');
            wrapper.innerHTML = '';

            try {
                // 서버에서 매장 상세정보 가져오기
                const res = await fetch(`/api/store-detail.php?code=${storeCode}`);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const store = await res.json();
                
                // 조회수 증가
                fetch(`/api/increment-view.php?store_code=${storeCode}`)
                    .catch(err => console.error('조회수 증가 오류:', err));

                // 패널에 내용 표시 (거리 정보 포함)
                renderStoreDetail(store, distance);
                
                // 댓글 폼 store_code 설정 및 댓글 목록 로드
                const reviewForm = document.querySelector('.review-form');
                if (reviewForm) {
                    reviewForm.dataset.storeCode = storeCode;
                    initCommentForm();
                }
                loadComments(storeCode);

            } catch (err) {
                console.error('상세 정보 로드 실패:', err);
                wrapper.innerHTML = `<div class="error">데이터를 불러올 수 없습니다.</div>`;
            }
        }

        // 오른쪽 패널 채우기
        function renderStoreDetail(store, distance = "") {
            const aside = document.querySelector(".aside-right");

            // ===========================
            // 상단 타이틀 + 거리
            // ===========================
            const nameEl = aside.querySelector(".cafe-name");
            const distanceBadge = aside.querySelector(".distance-badge");
            nameEl.textContent = store.name ? `${store.name}점` : "-";

            if (distance && distance !== "0") {
                distanceBadge.textContent = `${distance}km`;
                distanceBadge.style.display = "inline-block";
            } else {
                distanceBadge.style.display = "none";
            }

            // ===========================
            // 상세정보 (주소, 전화, 주차 등)
            // ===========================
            const infoList = aside.querySelector(".cafe-info-list");
            infoList.innerHTML = "";

            // 주소
            if (store.address) {
                const row = document.createElement("div");
                row.classList.add("info-row");
                row.innerHTML = `
                    <span class="label">주소</span>
                    <span class="value">${store.address}</span>
                `;
                infoList.appendChild(row);
            }

            // 전화번호
            if (store.phone) {
                const row = document.createElement("div");
                row.classList.add("info-row");
                row.innerHTML = `
                    <span class="label">전화번호</span>
                    <span class="value"><a href="tel:${store.phone}">${store.phone}</a></span>
                `;
                infoList.appendChild(row);
            }

            // 쉬는 날
            if (store.closed) {
                const row = document.createElement("div");
                row.classList.add("info-row");
                row.innerHTML = `
                    <span class="label">쉬는 날</span>
                    <span class="value">${store.closed}</span>
                `;
                infoList.appendChild(row);
            }

            // 영업시간
            if (store.hours) {
                const row = document.createElement("div");
                row.classList.add("info-row");
                
                // 오늘 요일 가져오기
                const today = new Date().getDay(); // 0: 일요일, 1: 월요일, ..., 6: 토요일
                const dayNames = ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'];
                const todayName = dayNames[today];
                
                // 쉼표를 기준으로 줄바꿈 처리하며 오늘 요일에 클래스 추가
                const hoursHTML = store.hours
                    .split(',')
                    .map(day => {
                        const trimmedDay = day.trim();
                        // 오늘 요일이 포함되어 있으면 클래스 추가
                        if (trimmedDay.includes(todayName)) {
                            return `<span class="today-hours">${trimmedDay}</span>`;
                        }
                        return trimmedDay;
                    })
                    .join('<br>');
                    
                row.innerHTML = `
                    <span class="label">영업시간</span>
                    <span class="value">${hoursHTML}</span>
                `;
                infoList.appendChild(row);
            }

            // 편의시설
            if (store.services) {
                const row = document.createElement("div");
                row.classList.add("info-row");
                row.innerHTML = `
                    <span class="label">편의시설</span>
                    <div class="value">${store.services}</div>
                `;
                infoList.appendChild(row);
            }

            // 주차 정보
            if (store.parking_available) {
                const row = document.createElement("div");
                row.classList.add("info-row");

                const label = document.createElement("span");
                label.classList.add("label");
                label.textContent = "주차";

                const value = document.createElement("span");
                value.classList.add("value");

                if (store.parking_available === "No") {
                    value.textContent = "주차 불가";
                    value.style.color = "#d32f2f";
                } else if (store.parking_available === "Yes") {
                    const feeType = store.parking_fee || "무료";
                    const capacity = store.parking_capacity ? `${store.parking_capacity}대` : "";
                    value.textContent = `주차 가능 (${feeType}${capacity ? ", " + capacity : ""})`;
                    value.style.color = "#00704A";
                } else {
                    value.textContent = "-";
                }

                row.appendChild(label);
                row.appendChild(value);
                infoList.appendChild(row);

                // 주차 상세
                if (store.parking_available === "Yes" && store.parking_detail) {
                    const detailRow = document.createElement("div");
                    detailRow.classList.add("info-row");
                    detailRow.innerHTML = `
                        <span class="label">주차 정보</span>
                        <div class="value">${store.parking_detail}</div>
                    `;
                    infoList.appendChild(detailRow);
                }
            }

            // 길찾기
            const navRow = document.createElement("div");
            navRow.classList.add("info-row");
            navRow.innerHTML = `
                <span class="label">길찾기</span>
                <div class="value map">
                    <a target="_blank" rel="noopener noreferrer" href="kakaomap://route?ep=${store.latitude},${store.longitude}&by=CAR" class="only-ios only-android">
                        <img src="/assets/img/brands/kakaomap.webp" alt="카카오맵">
                    </a>
                    <a target="_blank" rel="noopener noreferrer" href="https://map.naver.com/v5/search/${encodeURIComponent('스타벅스 ' + store.name + '점')}" class="only-desktop">
                        <img src="/assets/img/brands/navermap.webp" alt="네이버 지도 웹으로 열기">
                    </a>
                    <a target="_blank" rel="noopener noreferrer" href="nmap://search?query=${encodeURIComponent('스타벅스 ' + store.name + '점')}" class="only-ios only-android">
                        <img src="/assets/img/brands/navermap.webp" alt="네이버 지도 앱으로 열기">
                    </a>
                    <a target="_blank" rel="noopener noreferrer" href="tmap://route?goalname=${encodeURIComponent(store.name)}&goalx=${store.longitude}&goaly=${store.latitude}" class="only-ios only-android">
                        <img src="/assets/img/brands/tmap.webp" alt="티맵">
                    </a>
                    <a target="_blank" rel="noopener noreferrer" href="https://www.google.com/maps/dir/?api=1&destination=${store.latitude},${store.longitude}">
                        <img src="/assets/img/brands/googlemaps.webp" alt="구글맵">
                    </a>
                    <p class="only-ios only-android">해당 앱 설치 후 이용할 수 있습니다.</p>
                </div>
            `;
            
            infoList.appendChild(navRow);

            // ===========================
            // 이미지 슬라이더
            // ===========================
            const swiperWrapper = aside.querySelector(".swiper-wrapper");
            swiperWrapper.innerHTML = "";

            // 쉼표로 구분된 이미지 처리
            let imageList = [];
            if (store.images && typeof store.images === "string" && store.images.trim() !== "") {
                imageList = store.images.split(",").map(img => img.trim());
            }

            if (imageList.length === 0) {
                const slide = document.createElement("div");
                slide.classList.add("swiper-slide");
                const img = document.createElement("img");
                img.src = "/assets/img/noimage.png";
                img.alt = "이미지 없음";
                img.style.width = "100%";
                img.style.height = "100%";
                img.style.objectFit = "contain";
                slide.appendChild(img);
                swiperWrapper.appendChild(slide);
            } else {
                imageList.forEach(img => {
                    const fullUrl = img.startsWith("http")
                        ? img
                        : `https://image.istarbucks.co.kr${img}`;
                    const slide = document.createElement("div");
                    slide.classList.add("swiper-slide");
                    
                    const imgElement = document.createElement("img");
                    imgElement.src = "/assets/img/noimage.png"; // 로딩 전 기본 이미지
                    imgElement.dataset.src = fullUrl; // 실제 이미지 URL
                    imgElement.alt = store.name + " 이미지";
                    imgElement.style.width = "100%";
                    imgElement.style.height = "100%";
                    imgElement.style.objectFit = "cover";
                    
                    // 이미지 로드 성공 시 실제 이미지로 교체
                    const tempImg = new Image();
                    tempImg.onload = function() {
                        imgElement.src = fullUrl;
                    };
                    tempImg.onerror = function() {
                        // 이미지 로드 실패 시 noimage.png 유지
                        imgElement.src = "/assets/img/noimage.png";
                        imgElement.style.objectFit = "contain";
                    };
                    tempImg.src = fullUrl;
                    
                    slide.appendChild(imgElement);
                    swiperWrapper.appendChild(slide);
                });
            }

            // 기존 Swiper 초기화 제거
            if (window.cafeSwiper) {
                window.cafeSwiper.destroy(true, true);
            }

            // Swiper 다시 초기화
            setTimeout(() => {
                window.cafeSwiper = new Swiper(".cafe-swiper", {
                    loop: true,
                    slidesPerView: 1,
                    spaceBetween: 0,
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true,
                    },
                    autoplay: {
                        delay: 10000,
                        disableOnInteraction: false,
                    },
                    speed: 600,
                });
            }, 100);

            // 슬라이드 클릭 이동
            const swiperEl = aside.querySelector(".cafe-swiper");
            swiperEl.addEventListener("click", e => {
                const rect = swiperEl.getBoundingClientRect();
                const x = e.clientX - rect.left;

                if (x < rect.width * 0.4) {
                    window.cafeSwiper.slidePrev();
                    window.cafeSwiper.autoplay.start();
                } else if (x > rect.width * 0.6) {
                    window.cafeSwiper.slideNext();
                    window.cafeSwiper.autoplay.start();
                }
            });

            // 패널 활성화
            aside.classList.add("active");

            console.log(`✅ ${store.name} 상세정보 표시 완료 (${imageList.length}장)`);
        }

        // 줌 인디케이터
        function updateZoomIndicator() {
            const zoom = map.getZoom();
            document.getElementById('zoomIndicator').textContent = zoom;
        }

        // 줌 컨트롤 기능
        function initZoomControls() {
            const zoomInBtn = document.getElementById('zoomInBtn');
            const zoomOutBtn = document.getElementById('zoomOutBtn');

            zoomInBtn.addEventListener('click', function() {
                const currentZoom = map.getZoom();
                if (currentZoom < 21) {
                    map.setZoom(currentZoom + 1);
                }
            });

            zoomOutBtn.addEventListener('click', function() {
                const currentZoom = map.getZoom();
                if (currentZoom > 1) {
                    map.setZoom(currentZoom - 1);
                }
            });
        }

        // 내 위치 기능
        function initMyLocation() {
            const myLocationBtn = document.getElementById('myLocationBtn');

            myLocationBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            // 내 위치 저장
                            myLocation = new naver.maps.LatLng(lat, lng);
                            map.setCenter(myLocation);
                            map.setZoom(17);
                            
                            // 내 위치 마커 표시
                            const myMarker = new naver.maps.Marker({
                                position: myLocation,
                                map: map,
                                icon: {
                                    content: `<div class="myLocationIcon"><div></div><div></div><div></div><div></div></div>`,
                                    anchor: new naver.maps.Point(12, 12)
                                }
                            });
                            
                            // 3초 후 마커 제거
                            setTimeout(() => {
                                myMarker.setMap(null);
                            }, 3000);
                        },
                        function(error) {
                            alert('위치 정보를 가져올 수 없습니다: ' + error.message);
                        }
                    );
                } else {
                    alert('이 브라우저에서는 위치 정보를 지원하지 않습니다.');
                }
            });
        }

        // 댓글 작성 기능 초기화
        function initCommentForm() {
            const reviewForm = document.querySelector('.review-form');
            if (!reviewForm) return;
            
            // 이미 이벤트가 등록되어 있는지 확인
            if (reviewForm.dataset.initialized === 'true') return;
            
            const textarea = reviewForm.querySelector('textarea');
            const charCount = reviewForm.querySelector('.char-count');
            const stars = reviewForm.querySelectorAll('.star');
            const submitBtn = reviewForm.querySelector('.submit-btn');
            
            if (!textarea || !charCount || !stars.length || !submitBtn) return;
            
            // 이벤트 등록 여부 표시
            reviewForm.dataset.initialized = 'true';
            
            let selectedRating = 0;
            
            // 글자 수 카운트
            textarea.addEventListener('input', function() {
                charCount.textContent = `${this.value.length}/300`;
            });
            
            // 별점 선택
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    selectedRating = rating;
                    
                    // 별점 표시 업데이트
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });
            
            // 제출 버튼
            submitBtn.addEventListener('click', async function() {
                const content = textarea.value.trim();
                if (!content) {
                    alert('댓글 내용을 입력해주세요.');
                    return;
                }
                
                if (selectedRating === 0) {
                    alert('별점을 선택해주세요.');
                    return;
                }
                
                const storeCode = reviewForm.dataset.storeCode;
                if (!storeCode) {
                    alert('매장 정보를 불러올 수 없습니다.');
                    return;
                }
                
                // AJAX로 댓글 전송
                try {
                    const url = '/api/comment.php';
                    console.log('📝 댓글 작성 요청:', url);
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            store_code: storeCode,
                            nickname: generateRandomNickname(),
                            content: content,
                            rating: selectedRating
                        })
                    });
                    
                    console.log('📥 댓글 응답 상태:', response.status);
                    
                    // 응답 텍스트 먼저 확인
                    const responseText = await response.text();
                    console.log('📄 응답 텍스트:', responseText);
                    
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('JSON 파싱 오류:', parseError);
                        throw new Error('서버 응답이 유효하지 않습니다: ' + responseText);
                    }
                    
                    if (result.success) {
                        console.log('✅ 댓글 작성 성공');
                        // 댓글 목록 새로고침
                        textarea.value = '';
                        charCount.textContent = '0/300';
                        selectedRating = 0;
                        stars.forEach(s => s.classList.remove('active'));
                        
                        // 댓글 목록 업데이트
                        loadComments(storeCode);
                    } else {
                        alert('댓글 작성에 실패했습니다: ' + (result.message || '알 수 없는 오류'));
                    }
                } catch (error) {
                    console.error('댓글 작성 오류:', error);
                    alert('댓글 작성 중 오류가 발생했습니다: ' + error.message);
                }
            });
        }
        
        // 댓글 목록 불러오기
        async function loadComments(storeCode) {
            try {
                const response = await fetch(`/api/comments.php?store_code=${storeCode}`);
                const comments = await response.json();
                
                // 댓글 목록 렌더링
                const reviewList = document.querySelector('.cafe-review-list');
                if (!reviewList) return;
                
                // 기존 댓글 모두 제거 (댓글 폼 제외)
                const reviewItems = reviewList.querySelectorAll('.review-item');
                reviewItems.forEach(item => {
                    if (!item.classList.contains('review-form-item')) {
                        item.remove();
                    }
                });
                
                // 댓글 헤더 업데이트
                const header = reviewList.querySelector('.review-header h3');
                if (header) {
                    header.innerHTML = `리뷰(<span class="comments-count">${comments.length}</span>)`;
                }
                
                // 댓글 렌더링
                const reviewForm = reviewList.querySelector('.review-form');
                comments.forEach(comment => {
                    const reviewItem = document.createElement('div');
                    reviewItem.className = 'review-item';
                    
                    // 시간 변환
                    const date = new Date(comment.created_at);
                    const now = new Date();
                    const diff = now - date;
                    const seconds = Math.floor(diff / 1000);
                    const minutes = Math.floor(seconds / 60);
                    const hours = Math.floor(minutes / 60);
                    const days = Math.floor(hours / 24);
                    
                    let timeStr = '';
                    if (days > 0) {
                        timeStr = `${days}일 전`;
                    } else if (hours > 0) {
                        timeStr = `${hours}시간 전`;
                    } else if (minutes > 0) {
                        timeStr = `${minutes}분 전`;
                    } else {
                        timeStr = '방금 전';
                    }
                    
                    // 닉네임 기반 이모지 번호 생성
                    const emojiNum = comment.nickname ? getEmojiByNickname(comment.nickname) : '014';
                    
                    reviewItem.innerHTML = `
                        <div class="review-left">
                            <div class="review-user">
                                <div class="avatar">
                                    <img src="/assets/img/emoji/${emojiNum}.png" alt="이모지">
                                </div>
                                <span class="name">${comment.nickname || '익명'}</span>
                            </div>
                        </div>
                        <div class="review-right">
                            <div class="review-content">${comment.content}</div>
                            <div class="review-meta">
                                <span class="stars">${'★'.repeat(comment.rating || 5)}</span>
                                <span class="date">${timeStr}</span>
                            </div>
                        </div>
                    `;
                    reviewForm.parentNode.insertBefore(reviewItem, reviewForm);
                });
                
            } catch (error) {
                console.error('댓글 로드 오류:', error);
            }
        }

        // 지도 이벤트 리스너
        function initMapEvents() {
            // 줌 변경 시 인디케이터 업데이트 및 마커 다시 그리기
            naver.maps.Event.addListener(map, 'zoom_changed', function() {
                updateZoomIndicator();
                showStores(); // 줌 변경 시 마커 다시 그리기
                updateCafeList(); // 카페 목록도 업데이트
                // 줌 변경 시 인포윈도우 닫기
                visibleWindows.forEach(win => win.close());
                visibleWindows = [];
                markerWindows.clear(); // 마커 윈도우 맵도 비우기
            });
            
            // 지도 이동 시 줌 인디케이터 업데이트 및 마커 다시 그리기
            naver.maps.Event.addListener(map, 'dragend', function() {
                updateZoomIndicator();
                showStores(); // 지도 이동 시 마커 다시 그리기
                updateCafeList(); // 카페 목록도 업데이트
                // 드래그 시에는 인포윈도우 유지
            });
        }

        // 페이지 로드 시 데이터 로드
        document.addEventListener('DOMContentLoaded', function() {
            loadStores();
            initZoomControls();
            initMyLocation();
            initMapEvents();
            updateZoomIndicator(); // 초기 줌 레벨 표시

            // 오른쪽 패널 초기화 함수
            function clearRightPanel() {
                const aside = document.querySelector('.aside-right');
                
                // 상세 정보 초기화
                const nameEl = aside.querySelector(".cafe-name");
                if (nameEl) nameEl.textContent = "";
                
                const infoList = aside.querySelector(".cafe-info-list");
                if (infoList) infoList.innerHTML = "";
                
                // 이미지 슬라이더 초기화
                const swiperWrapper = aside.querySelector(".swiper-wrapper");
                if (swiperWrapper) swiperWrapper.innerHTML = "";
                
                // 기존 Swiper 인스턴스 제거
                if (window.cafeSwiper) {
                    window.cafeSwiper.destroy(true, true);
                    window.cafeSwiper = null;
                }
            }
            
            // 닫기 버튼
            document.querySelector('.close-detail-btn').addEventListener('click', () => {
                document.querySelector('.aside-right').classList.remove('active');
                // 패널 내용 초기화
                clearRightPanel();
                // history.pushState({}, '', baseUrl);
                history.pushState({}, '', '/'); 
            });

            const path = window.location.pathname;
            let storeCode = null;

            // /starbucks/12345678 형태라면 숫자만 추출
            const match = path.match(/\/starbucks\/(\d+)/);
            if (match) {
                storeCode = match[1];
            }

            // ?store=12345678 형태도 지원
            if (!storeCode) {
                const params = new URLSearchParams(window.location.search);
                storeCode = params.get('store');
            }

            // storeCode 있으면 오른쪽 패널 자동 열기
            if (storeCode) {
                setTimeout(() => {
                    openRightPanel(storeCode);
                }, 600);
            }

            // popstate (뒤로가기 / 앞으로가기 시 패널 상태 유지)
            window.addEventListener('popstate', () => {
                const currentPath = window.location.pathname;
                const matchPop = currentPath.match(/\/starbucks\/(\d+)/);
                const aside = document.querySelector('.aside-right');
                if (matchPop) {
                    openRightPanel(matchPop[1]);
                } else {
                    aside.classList.remove('active');
                }
            });
        });
    </script>
</body>