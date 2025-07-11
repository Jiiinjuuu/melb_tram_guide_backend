<?php
// melb_tram_api/public/getPlaceRating.php

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

if (!isset($_GET['place_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "place_id가 필요합니다."]);
    exit();
}

$place_id = $_GET['place_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            ROUND(AVG(rating), 1) AS average_rating,
            COUNT(*) AS review_count
        FROM reviews
        WHERE place_id = :place_id
    ");
    $stmt->execute([':place_id' => $place_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
