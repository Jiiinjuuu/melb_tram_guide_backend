<?php
require_once __DIR__ . '/../includes/cors.php';  // CORS
require_once __DIR__ . '/db_connect.php';        // DB 연결 포함

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT u.id AS user_id, u.name, COUNT(s.id) AS stamp_count
        FROM users u
        LEFT JOIN stamps s ON u.id = s.user_id
        GROUP BY u.id, u.name
        ORDER BY stamp_count DESC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'ranking' => $result
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'DB 오류: ' . $e->getMessage()
    ]);
}
