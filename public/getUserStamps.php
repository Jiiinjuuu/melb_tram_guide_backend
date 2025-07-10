<?php
// melb_tram_api/public/getUserStamps.php

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

// user_id 파라미터 확인
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["error" => "user_id는 필수입니다."]);
    exit();
}

try {
    // 스탬프 받은 명소 정보 조회
    $stmt = $pdo->prepare("
        SELECT 
            s.place_id,
            p.name AS place_name,
            p.category,
            p.subcategory,
            p.latitude,
            p.longitude,
            s.earned_at
        FROM stamps s
        JOIN places p ON s.place_id = p.id
        WHERE s.user_id = :user_id
        ORDER BY s.earned_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $stamps = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stamps);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
