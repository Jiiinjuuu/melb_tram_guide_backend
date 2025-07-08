<?php
// melb_tram_api/public/deleteReview.php

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

if (!$user_id || !$review_id) {
    http_response_code(400);
    echo json_encode(["error" => "user_id와 review_id가 필요합니다."]);
    exit();
}

try {
    // 내가 쓴 후기인지 확인
    $check = $pdo->prepare("SELECT * FROM reviews WHERE id = :review_id AND user_id = :user_id");
    $check->execute([
        ':review_id' => $review_id,
        ':user_id' => $user_id
    ]);

    if ($check->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(["error" => "본인 후기만 삭제할 수 있습니다."]);
        exit();
    }

    // 삭제
    $delete = $pdo->prepare("DELETE FROM reviews WHERE id = :review_id");
    $delete->execute([':review_id' => $review_id]);

    echo json_encode(["status" => "success", "message" => "후기가 삭제되었습니다."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
