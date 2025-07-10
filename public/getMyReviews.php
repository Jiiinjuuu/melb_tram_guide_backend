<?php
// melb_tram_api/public/getMyReviews.php

session_start();

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/env.php';  // ✅ 환경 변수 로드
require_once __DIR__ . '/db_connect.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "로그인이 필요합니다."]);
    exit();
}

$user_id = $_SESSION['user_id'];

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

    // ✅ .env에서 이미지 base URL 불러오기
    $baseUrl = rtrim($_ENV['IMAGE_BASE_URL'], '/');
    foreach ($reviews as &$review) {
        if (!empty($review['image_url'])) {
            $review['image_full_url'] = $baseUrl . '/' . ltrim($review['image_url'], '/');
        } else {
            $review['image_full_url'] = null;
        }
    }

    echo json_encode([
        "success" => true,
        "reviews" => $reviews
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
