<?php
// melb_tram_api/public/postReview.php

require_once "../includes/cors.php";
require_once "db_connect.php";

header("Content-Type: application/json");

// POST 방식만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "POST 요청만 허용됩니다."]);
    exit();
}

// JSON 파싱
$data = json_decode(file_get_contents("php://input"));

$user_id = $data->user_id ?? null;
$place_id = $data->place_id ?? null;
$rating = $data->rating ?? null;
$content = $data->content ?? null;
$image_url = $data->image_url ?? null;

// 필수 항목 검사
if (!$user_id || !$place_id || !$rating || !$content) {
    http_response_code(400);
    echo json_encode(["error" => "user_id, place_id, rating, content는 필수입니다."]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO reviews (user_id, place_id, rating, content, image_url, created_at)
        VALUES (:user_id, :place_id, :rating, :content, :image_url, NOW())
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':place_id' => $place_id,
        ':rating' => $rating,
        ':content' => $content,
        ':image_url' => $image_url
    ]);

    echo json_encode(["status" => "success", "message" => "후기가 등록되었습니다."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
