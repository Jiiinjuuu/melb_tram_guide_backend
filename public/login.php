<?php
require_once __DIR__ . '/../includes/cors.php';  // CORS
require_once __DIR__ . '/db_connect.php';        // DB 연결 포함

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["error" => "이메일과 비밀번호를 입력해주세요."]);
    exit;
}

$email = $data->email;
$password = $data->password;

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => true,
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["error" => "이메일 또는 비밀번호가 일치하지 않습니다."]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "서버 오류: " . $e->getMessage()]);
}
?>
