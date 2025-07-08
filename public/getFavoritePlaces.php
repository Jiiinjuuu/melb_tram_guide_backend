<?php
// melb_tram_api/public/getFavoritePlaces.php

require_once "../includes/cors.php";
require_once "db_connect.php";

header("Content-Type: application/json");

// user_id 필수
if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "user_id가 필요합니다."]);
    exit();
}

$user_id = $_GET['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT p.*
        FROM places p
        JOIN user_places up ON p.id = up.place_id
        WHERE up.user_id = :user_id
    ");
    $stmt->execute([':user_id' => $user_id]);
    $favoritePlaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($favoritePlaces);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
