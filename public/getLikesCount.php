<?php
// melb_tram_api/public/getLikesCount.php

// ✅ 1. CORS 설정
header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ 2. 환경변수 로드
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// ✅ 3. DB 연결
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

// ✅ 4. 응답 타입 지정
header("Content-Type: application/json");

// ✅ 5. 파라미터 검증
if (!isset($_GET['review_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "review_id가 필요합니다."]);
    exit();
}

$review_id = $_GET['review_id'];

// ✅ 6. 좋아요 개수 조회
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS like_count
        FROM likes
        WHERE review_id = :review_id
    ");
    $stmt->execute([':review_id' => $review_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
