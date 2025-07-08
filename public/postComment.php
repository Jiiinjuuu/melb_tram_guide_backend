<?php
// melb_tram_api/public/postComment.php

require_once "../includes/cors.php";
require_once "db_connect.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "POST 요청만 허용됩니다."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

$user_id = $data->user_id ?? null;
$review_id = $data->review_id ?? null;
$content = $data->content ?? null;

if (!$user_id || !$review_id || !$content) {
    http_response_code(400);
    echo json_encode(["error" => "user_id, review_id, content는 필수입니다."]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO comments (user_id, review_id, content, created_at)
        VALUES (:user_id, :review_id, :content, NOW())
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':review_id' => $review_id,
        ':content' => $content
    ]);

    echo json_encode(["status" => "success", "message" => "댓글이 등록되었습니다."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
