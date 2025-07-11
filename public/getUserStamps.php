<?php
// melb_tram_api/public/getUserStamps.php
header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start(); // ✅ 세션 시작

require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

// ✅ 세션에서 user_id 확인
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // 인증 필요
    echo json_encode(["error" => "로그인이 필요합니다."]);
    exit();
}

$user_id = $_SESSION['user_id'];

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
