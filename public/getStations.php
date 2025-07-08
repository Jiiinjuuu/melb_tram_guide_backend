<?php
// getStations.php
require_once __DIR__ . '/../includes/cors.php';  // CORS
require_once __DIR__ . '/db_connect.php';        // DB 연결 포함

header("Content-Type: application/json");

try {
    $stmt = $pdo->query("SELECT * FROM stations");
    $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stations);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

