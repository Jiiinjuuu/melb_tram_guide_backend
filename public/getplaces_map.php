<?php
// getplaces_map.php

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

// 파라미터 체크
if (!isset($_GET['station_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing station_id']);
    exit;
}

$station_id = $_GET['station_id'];

try {
    // ✅ 스탬프 명소 구분을 위해 is_stampPlace 칼럼도 SELECT에 포함시킴
    $stmt = $pdo->prepare("
        SELECT id, name, description, latitude, longitude, is_stampPlace
        FROM places
        WHERE station_id = ?
    ");
    $stmt->execute([$station_id]);
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($places);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database query failed',
        'details' => $e->getMessage()
    ]);
}
