<?php
// melb_tram_api/public/getStampPlaces.php

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

try {
    $stmt = $pdo->query("
        SELECT 
            id, name, description, latitude, longitude, station_id
        FROM places
        WHERE is_stampPlace = 1
    ");
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($places);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'DB 오류',
        'details' => $e->getMessage()
    ]);
}
