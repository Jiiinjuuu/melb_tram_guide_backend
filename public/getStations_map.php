<?php
// getStations_map.php
require_once __DIR__ . '/../includes/cors.php';  // CORS
require_once __DIR__ . '/db_connect.php';        // DB 연결 포함

header("Content-Type: application/json");

try {
    // 위도와 경도 값이 있는 정류장만 선택
    $stmt = $pdo->query("SELECT id, name, description, latitude, longitude FROM stations WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stations);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
