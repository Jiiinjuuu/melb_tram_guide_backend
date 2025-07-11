<?php

header("Access-Control-Allow-Origin: https://melb-stamp-tour.netlify.app");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start(); // ✅ 세션 시작 (자동 로그인 위해 필요)

require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["error" => "모든 필드를 입력해주세요."]);
    exit;
}

$name = $data->name;
$email = $data->email;
$password = password_hash($data->password, PASSWORD_DEFAULT); // 비밀번호 해싱

try {
    // 이메일 중복 검사
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["error" => "이미 등록된 이메일입니다."]);
        exit;
    }

    // 사용자 등록
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);

    // 방금 등록한 사용자 정보 가져오기
    $newUserId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->execute([$newUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ 자동 로그인 처리
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    echo json_encode([
        "success" => true,
        "message" => "회원가입 완료 및 자동 로그인 되었습니다.",
        "user" => $user
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "서버 오류: " . $e->getMessage()]);
}
?>
