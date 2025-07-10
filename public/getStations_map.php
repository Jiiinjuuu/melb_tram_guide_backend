<?php
// getStations_map.php
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/db_connect.php';

header("Content-Type: application/json");

try {
    // line도 함께 가져오기
    $stmt = $pdo->query("SELECT id, name, description, latitude, longitude, line FROM stations WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stations);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
