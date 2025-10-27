# cafezoa.com

카페에 위치를 알려줍니다.

```sql
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '브랜드 고유 ID',
    name VARCHAR(10) NOT NULL COMMENT '브랜드명',
    slug VARCHAR(50) UNIQUE COMMENT '브랜드 코드명',
    logo VARCHAR(255) COMMENT '브랜드 로고 이미지 경로',
    color VARCHAR(10) DEFAULT '#00704A' COMMENT '브랜드 대표 색상 코드',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성일'
);

INSERT INTO brands (name, slug, logo, color)
VALUES
('스타벅스', 'starbucks', '/img/brands/starbucks.webp', '#00704A'),
('투썸플레이스', 'twosome', '/img/brands/twosome.webp', '#C21A1A');

CREATE TABLE stores (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '매장 고유 ID',
    brand_id INT NOT NULL COMMENT '브랜드 ID',
    store_code VARCHAR(12) NOT NULL UNIQUE COMMENT '매장 고유 코드',
    name VARCHAR(100) NOT NULL COMMENT '매장명',
    description TEXT COMMENT '간단 설명',
    address VARCHAR(255) COMMENT '주소',
    region VARCHAR(20) COMMENT '시/도',
    city VARCHAR(20) COMMENT '구/군',
    latitude DECIMAL(9,6) COMMENT '위도',
    longitude DECIMAL(9,6) COMMENT '경도',
    phone VARCHAR(20) COMMENT '전화번호',
    hours TEXT COMMENT '영업시간',
    closed VARCHAR(100) COMMENT '쉬는 날',
    since VARCHAR(20) NULL COMMENT '개점일',
    services TEXT COMMENT '서비스 목록 (구분자 문자열)',
    parking_available VARCHAR(10) DEFAULT 'No' COMMENT '주차 가능 여부 (Yes/No)',
    parking_fee VARCHAR(10) DEFAULT 'No' COMMENT '주차 요금 유형 (유료/무료/No)',
    parking_capacity VARCHAR(10) DEFAULT 0 COMMENT '주차 가능 대수',
    parking_detail TEXT COMMENT '주차 상세 정보',
    images TEXT COMMENT '이미지 목록 (구분자 문자열)',
    view_count INT DEFAULT 0 COMMENT '조회수',
    comment_count INT DEFAULT 0 COMMENT '댓글 수',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    INDEX idx_brand (brand_id),
    INDEX idx_region (region),
    INDEX idx_city (city),
    INDEX idx_parking (parking_available, parking_fee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT '댓글 고유 ID',
    store_code VARCHAR(20) NOT NULL COMMENT '매장 코드',
    nickname VARCHAR(30) DEFAULT '익명' COMMENT '닉네임',
    content TEXT NOT NULL COMMENT '내용',
    rating INT DEFAULT 5 COMMENT '평점',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    FOREIGN KEY (store_code) REFERENCES stores(store_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO stores (
    brand_id,
    store_code,
    name,
    description,
    address,
    region,
    city,
    phone,
    services,
    hours,
    closed,
    since,
    latitude,
    longitude,
    parking_available,
    parking_fee,
    parking_capacity,
    parking_detail,
    images
) VALUES (
    1,
    '19747014',
    '월송리CC',
    '누구에게나 열린 여유가 있는 공간입니다. 탁 트인 풍경과 함께 커피 한 잔의 힐링을 느껴보세요.',
    '강원특별자치도 원주시 지정면 오크밸리2길 250',
    '강원',
    '원주시',
    '1522-3232',
    '주차, 피지오, 블론드, 현금없는 매장',
    '월요일 06:00 ~ 22:00\n화요일 06:00 ~ 22:00\n수요일 06:00 ~ 22:00\n목요일 06:00 ~ 22:00\n금요일 06:00 ~ 22:00\n토요일 06:00 ~ 22:00\n일요일 06:00 ~ 22:00',
    '연중무휴',
    '2025-10-30',
    37.413907787106,
    127.822367179311,
    'Yes',
    '유료',
    '100',
    '1. 주차가능 2. 주차장위치 - 건물앞 3. 주차가능대수 - 100대 이상 4. 주차조건 - 1만원 이상 구매시 2시간 무료',
    'upload/store/2025/10/4714_20251025093600_ll5gf.jpg'
);
```



https://image.istarbucks.co.kr/upload/common/img/icon/icon01.png --> 스타벅스 리저브
https://image.istarbucks.co.kr/upload/common/img/icon/icon03.png --> 드라이브 스루
https://image.istarbucks.co.kr/upload/common/img/icon/icon_wt.png --> 워크 스루
https://image.istarbucks.co.kr/upload/common/img/icon/icon02.png --> 커뮤니티 스토어
https://image.istarbucks.co.kr/upload/common/img/icon/icon04.png --> 주차
https://image.istarbucks.co.kr/upload/common/img/icon/icon23.png --> 블론드
https://image.istarbucks.co.kr/upload/common/img/icon/icon22.png --> 피지오
https://image.istarbucks.co.kr/upload/common/img/icon/icon18.png --> 나이트로 콜드 브루 커피
https://image.istarbucks.co.kr/upload/common/img/icon/icon20.png --> 현금없는 매장
https://image.istarbucks.co.kr/upload/common/img/icon/icon_24.png --> 식약처 위생등급제 인증
https://image.istarbucks.co.kr/upload/common/img/icon/icon_delivers_service.png --> 딜리버스
https://image.istarbucks.co.kr/upload/common/img/icon/Moon_icon_16x16_230324.png --> 21시 이후 영업 종료 매장
https://image.istarbucks.co.kr/upload/common/img/icon/icon_now_brewing.png --> 나우 브루잉 매장
https://image.istarbucks.co.kr/upload/common/img/icon/icon_fast_serve.png --> 패스트 서브 매장
https://image.istarbucks.co.kr/upload/common/img/icon/icon_pet_01.png --> 펫 존 매장
https://image.istarbucks.co.kr/upload/common/img/icon/icon07.png --> 공항내
https://image.istarbucks.co.kr/upload/common/img/icon/icon09.png --> 해안가
https://image.istarbucks.co.kr/upload/common/img/icon/icon08.png --> 대학가
https://image.istarbucks.co.kr/upload/common/img/icon/icon12.png --> 터미널/기차역
https://image.istarbucks.co.kr/upload/common/img/icon/icon11.png --> 리조트
https://image.istarbucks.co.kr/upload/common/img/icon/icon13.png --> 병원
https://image.istarbucks.co.kr/upload/common/img/icon/icon10.png --> 입점
https://image.istarbucks.co.kr/upload/common/img/icon/icon14.png --> 지하철 인접
https://image.istarbucks.co.kr/upload/common/img/icon/icon19.png --> 장애인 편의시설
https://image.istarbucks.co.kr/upload/common/img/icon/icon21.png --> 공기청정기
https://image.istarbucks.co.kr/upload/common/img/icon/EV_icon_map.png --> 전기자동차