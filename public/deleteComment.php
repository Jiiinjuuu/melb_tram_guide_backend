<?php
// melb_tram_api/public/deleteComment.php

header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


session_start(); // ✅ 세션 시작
require_once "db_connect.php";

header("Content-Type: application/json");

// ✅ POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "POST 요청만 허용됩니다."]);
    exit();
}

// ✅ 로그인 상태 확인
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "로그인이 필요합니다."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
$comment_id = $data->comment_id ?? null;
$user_id = $_SESSION['user_id']; // ✅ 세션에서 가져옴

if (!$comment_id) {
    http_response_code(400);
    echo json_encode(["error" => "comment_id가 필요합니다."]);
    exit();
}

try {
    // ✅ 본인 댓글인지 확인
    $check = $pdo->prepare("SELECT * FROM comments WHERE id = :comment_id AND user_id = :user_id");
    $check->execute([
        ':comment_id' => $comment_id,
        ':user_id' => $user_id
    ]);

    if ($check->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(["error" => "본인 댓글만 삭제할 수 있습니다."]);
        exit();
    }

    // ✅ 삭제 수행
    $delete = $pdo->prepare("DELETE FROM comments WHERE id = :comment_id");
    $delete->execute([':comment_id' => $comment_id]);

    echo json_encode(["success" => true, "message" => "댓글이 삭제되었습니다."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
