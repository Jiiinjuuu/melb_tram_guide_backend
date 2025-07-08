<?php
require_once __DIR__ . '/../includes/cors.php';  // CORS
require_once __DIR__ . '/db_connect.php';        // DB 연결 포함

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

    echo json_encode(["success" => true, "message" => "회원가입 완료"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "서버 오류: " . $e->getMessage()]);
}
?>
