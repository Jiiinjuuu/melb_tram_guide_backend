<?php
// getplaces_map.php
require_once __DIR__ . '/../includes/cors.php';
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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT id, name, description, latitude, longitude FROM places WHERE station_id = ?");
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
