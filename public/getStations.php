<?php
// getStations.php
header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_connect.php';        // DB 연결 포함

header("Content-Type: application/json");

try {
    $stmt = $pdo->query("SELECT * FROM stations");
    $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stations);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

