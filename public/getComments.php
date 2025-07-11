<?php
// melb_tram_api/public/getComments.php

header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "db_connect.php";

header("Content-Type: application/json");

if (!isset($_GET['review_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "review_id가 필요합니다."]);
    exit();
}

$review_id = $_GET['review_id'];

try {
    $stmt = $pdo->prepare("
        SELECT c.*, u.email AS username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.review_id = :review_id
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([':review_id' => $review_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comments);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
