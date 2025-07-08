<?php
// melb_tram_api/public/getReviews.php

require_once "../includes/cors.php";
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
        SELECT r.*, u.name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.place_id = :place_id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([':place_id' => $place_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($reviews);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
