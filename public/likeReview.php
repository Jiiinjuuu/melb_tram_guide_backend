<?php
// melb_tram_api/public/likeReview.php

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
    // 이미 좋아요한 상태인지 확인
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = :user_id AND review_id = :review_id");
    $stmt->execute([
        ':user_id' => $user_id,
        ':review_id' => $review_id
    ]);

    if ($stmt->rowCount() > 0) {
        // 이미 좋아요했으면 삭제
        $delete = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND review_id = :review_id");
        $delete->execute([
            ':user_id' => $user_id,
            ':review_id' => $review_id
        ]);
        echo json_encode(["status" => "unliked"]);
    } else {
        // 없으면 추가
        $insert = $pdo->prepare("INSERT INTO likes (user_id, review_id) VALUES (:user_id, :review_id)");
        $insert->execute([
            ':user_id' => $user_id,
            ':review_id' => $review_id
        ]);
        echo json_encode(["status" => "liked"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
