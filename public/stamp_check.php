<?php
// stamp_check.php
$origin = "https://melb-stamp-tour.netlify.app";

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $origin) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // 동일 도메인 내부라면 빈값으로
    'secure' => true, // Netlify는 HTTPS 배포이므로 true
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();

require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

// ✅ 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => '로그인이 필요합니다.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ 입력값 확인
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
        exit;
    }

    // ✅ 이미지가 포함된 후기 있는지 확인
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND place_id = ? AND image_url IS NOT NULL AND image_url != ''");
    $stmt->execute([$user_id, $place_id]);

    if ($stmt->fetch()) {
        // ✅ 이미지 후기 있음 → 스탬프 발급
        $stmt = $pdo->prepare("INSERT INTO stamps (user_id, place_id, earned_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $place_id]);
        echo json_encode(['status' => 'new']);
    } else {
        // ❌ 이미지 없는 후기만 존재하거나 없음
        echo json_encode(['status' => 'no_image']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB 오류: ' . $e->getMessage()]);
}