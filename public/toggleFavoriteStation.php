<?php
// melb_tram_api/public/toggleFavoriteStation.php

require_once "../includes/cors.php";
require_once "db_connect.php";

header("Content-Type: application/json");

// POST 방식만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "POST 요청만 허용됩니다."]);
    exit();
}

// JSON 파싱
$data = json_decode(file_get_contents("php://input"));

$user_id = $data->user_id ?? null;
$station_id = $data->station_id ?? null;

// 유효성 검사
if (!$user_id || !$station_id) {
    http_response_code(400);
    echo json_encode(["error" => "user_id와 station_id를 모두 입력하세요."]);
    exit();
}

try {
    // 즐겨찾기 여부 확인
    $stmt = $pdo->prepare("SELECT * FROM user_stations WHERE user_id = :user_id AND station_id = :station_id");
    $stmt->execute([
        ":user_id" => $user_id,
        ":station_id" => $station_id
    ]);

    if ($stmt->rowCount() > 0) {
        // 이미 즐겨찾기에 있음 → 삭제
        $deleteStmt = $pdo->prepare("DELETE FROM user_stations WHERE user_id = :user_id AND station_id = :station_id");
        $deleteStmt->execute([
            ":user_id" => $user_id,
            ":station_id" => $station_id
        ]);
        echo json_encode(["status" => "removed"]);
    } else {
        // 없으면 추가
        $insertStmt = $pdo->prepare("INSERT INTO user_stations (user_id, station_id) VALUES (:user_id, :station_id)");
        $insertStmt->execute([
            ":user_id" => $user_id,
            ":station_id" => $station_id
        ]);
        echo json_encode(["status" => "added"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 오류: " . $e->getMessage()]);
}
?>
