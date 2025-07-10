<?php
// melb_tram_api/public/getMyReviews.php

session_start();

require_once __DIR__ . '/../includes/cors.php';
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

    // ✅ image_url 앞에 도메인 붙여서 image_full_url 추가
    $baseUrl = 'http://localhost/melb_tram_api/public';
    foreach ($reviews as &$review) {
        if (!empty($review['image_url'])) {
            $review['image_full_url'] = $baseUrl . $review['image_url'];
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
