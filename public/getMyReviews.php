<?php
// melb_tram_api/public/getMyReviews.php

$origin = "https://melb-stamp-tour.netlify.app";

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $origin) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // 동일 도메인 내부라면 빈값으로
    'secure' => true, // Netlify는 HTTPS 배포이므로 true
    'httponly' => true,
    'samesite' => 'None'
]);
// ✅ 2. 세션 시작
session_start();

// ✅ 3. 환경변수 로드 (.env)
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// ✅ 4. DB 연결
try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 연결 실패: " . $e->getMessage()]);
    exit();
}

// ✅ 5. JSON 응답 헤더
header("Content-Type: application/json");

// ✅ 6. 로그인 확인
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "로그인이 필요합니다."]);
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ 7. 리뷰 데이터 조회
try {
    $stmt = $pdo->prepare("
        SELECT r.id, r.place_id, p.name AS place_name, r.rating, r.content, r.image_url, r.created_at
        FROM reviews r
        JOIN places p ON r.place_id = p.id
        WHERE r.user_id = :user_id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ 8. 이미지 URL 처리
    $baseUrl = rtrim($_ENV['IMAGE_BASE_URL'], '/');
    foreach ($reviews as &$review) {
        if (!empty($review['image_url'])) {
            $review['image_full_url'] = $baseUrl . '/' . ltrim($review['image_url'], '/');
        } else {
            $review['image_full_url'] = null;
        }
    }

    // ✅ 9. 최종 응답
    echo json_encode([
        "success" => true,
        "reviews" => $reviews
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
