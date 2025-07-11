<?php
// melb_tram_api/public/getLikesCount.php

<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

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
        SELECT COUNT(*) AS like_count
        FROM likes
        WHERE review_id = :review_id
    ");
    $stmt->execute([':review_id' => $review_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
