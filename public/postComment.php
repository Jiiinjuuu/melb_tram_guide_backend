<?php
require_once "../includes/cors.php";
require_once "db_connect.php";

header("Content-Type: application/json");

try {
  $data = json_decode(file_get_contents("php://input"));

  if (!isset($data->review_id, $data->user_id, $data->content)) {
    echo json_encode(["success" => false, "message" => "필수 입력 누락"]);
    exit;
  }

  $review_id = $data->review_id;
  $user_id = $data->user_id;
  $content = trim($data->content);

  $stmt = $pdo->prepare("INSERT INTO comments (review_id, user_id, content) VALUES (?, ?, ?)");
  $stmt->execute([$review_id, $user_id, $content]);

  $comment_id = $pdo->lastInsertId();

  // 사용자 이름 가져오기
  $userStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
  $userStmt->execute([$user_id]);
  $username = $userStmt->fetchColumn() ?: '익명';

  echo json_encode([
    "success" => true,
    "comment" => [
      "id" => (int)$comment_id,
      "user_id" => (int)$user_id,
      "username" => $username,
      "content" => $content,
      "created_at" => date("Y-m-d H:i:s")
    ]
  ]);
} catch (PDOException $e) {
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
