<?php
header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$stationId = isset($_GET['station_id']) ? intval($_GET['station_id']) : 0;

if ($stationId === 0) {
    echo json_encode(["error" => "정류장 ID가 필요합니다."]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            p.description,
            p.image_url,
            p.station_id,
            IFNULL(ROUND(AVG(r.rating), 1), 0) AS average_rating,
            COUNT(r.id) AS review_count
        FROM places p
        LEFT JOIN reviews r ON p.id = r.place_id
        WHERE p.station_id = ?
        GROUP BY p.id
    ");
    $stmt->execute([$stationId]);
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($places);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
