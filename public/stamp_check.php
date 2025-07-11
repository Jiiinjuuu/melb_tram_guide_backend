<?php
// stamp_check.php
header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

// ✅ 로그인된 사용자 확인
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => '로그인이 필요합니다.']);
    exit;
}

$user_id = $_SESSION['user']['id'];

// ✅ place_id 유효성 검사
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['place_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'place_id가 필요합니다.']);
    exit;
}

$place_id = intval($data['place_id']);

try {
    // ✅ 기존 스탬프 확인
    $stmt = $pdo->prepare("SELECT id FROM stamps WHERE user_id = ? AND place_id = ?");
    $stmt->execute([$user_id, $place_id]);

    if ($stmt->fetch()) {
        echo json_encode(['status' => 'exists']);
    } else {
        // ✅ 신규 스탬프 발급
        $stmt = $pdo->prepare("INSERT INTO stamps (user_id, place_id, earned_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $place_id]);
        echo json_encode(['status' => 'new']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB 오류: ' . $e->getMessage()]);
}
