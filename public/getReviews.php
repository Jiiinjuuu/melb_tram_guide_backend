<?php
// melb_tram_api/public/getReviews.php
header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../includes/env.php";     // ✅ 환경변수 추가
require_once "db_connect.php";

header("Content-Type: application/json");

// place_id 필수
if (!isset($_GET['place_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "place_id가 필요합니다."]);
    exit();
}

$place_id = $_GET['place_id'];

try {
    $stmt = $pdo->prepare("
        SELECT r.id, r.content, r.rating, r.created_at, r.image_url, u.name AS username
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.place_id = :place_id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([':place_id' => $place_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ .env에서 base URL 가져오기
    $baseUrl = rtrim($_ENV['IMAGE_BASE_URL'], '/');
    foreach ($reviews as &$review) {
        if (!empty($review['image_url'])) {
            $review['image_full_url'] = $baseUrl . '/' . ltrim($review['image_url'], '/');
        } else {
            $review['image_full_url'] = null;
        }
    }

    echo json_encode($reviews);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
