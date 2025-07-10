<?php
// stamp_check.php

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

try {
    // 1. POST 데이터 파싱
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['user_id'], $data['place_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'user_id 또는 place_id 누락']);
        exit;
    }

    $user_id = intval($data['user_id']);
    $place_id = intval($data['place_id']);

    // 2. 이미 있는지 확인
    $stmt = $pdo->prepare("SELECT id FROM stamps WHERE user_id = ? AND place_id = ?");
    $stmt->execute([$user_id, $place_id]);

    if ($stmt->fetch()) {
        echo json_encode(['status' => 'exists']);
    } else {
        // 3. 신규 스탬프 등록
        $stmt = $pdo->prepare("INSERT INTO stamps (user_id, place_id, earned_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $place_id]);
        echo json_encode(['status' => 'new']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB 오류: ' . $e->getMessage()]);
    exit;
}
