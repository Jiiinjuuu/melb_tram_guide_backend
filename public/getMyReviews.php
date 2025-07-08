<?php
// public/getMyReviews.php

require_once "../includes/cors.php";
require_once "db_connect.php";

header("Content-Type: application/json");

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "user_id가 필요합니다."]);
    exit();
}

$user_id = $_GET['user_id'];

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

    echo json_encode($reviews);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
