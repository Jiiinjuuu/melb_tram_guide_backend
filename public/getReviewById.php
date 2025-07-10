<?php
// melb_tram_api/public/getReviewById.php

require_once "../includes/cors.php";
require_once "../includes/env.php";     // ✅ 환경변수 불러오기
require_once "db_connect.php";

header("Content-Type: application/json");

if (!isset($_GET['review_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "review_id가 필요합니다."]);
    exit;
}

$review_id = $_GET['review_id'];

try {
    $stmt = $pdo->prepare("
        SELECT r.*, u.name AS username
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.id = :review_id
    ");
    $stmt->execute([':review_id' => $review_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        http_response_code(404);
        echo json_encode(["error" => "해당 후기를 찾을 수 없습니다."]);
        exit;
    }

    // ✅ 이미지 전체 경로 추가 (.env에서 불러온 base URL 사용)
    if (!empty($review['image_url'])) {
        $baseUrl = rtrim($_ENV['IMAGE_BASE_URL'], '/');
        $review['image_full_url'] = $baseUrl . '/' . ltrim($review['image_url'], '/');
    } else {
        $review['image_full_url'] = null;
    }

    echo json_encode($review);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
