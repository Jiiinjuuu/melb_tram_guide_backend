<?php

$allowedOrigins = [
    "http://localhost:3000",
    "https://melb-stamp-tour.netlify.app"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

require_once __DIR__ . '/db_connect.php';

header("Content-Type: application/json; charset=utf-8");

try {
    // 요청 본문에서 JSON 데이터 읽기
    $data = json_decode(file_get_contents("php://input"), true);
    $email = $data["email"] ?? "";
    $password = $data["password"] ?? "";

    // 이메일 또는 비밀번호 비어있을 때
    if (empty($email) || empty($password)) {
        echo json_encode([
            "success" => false,
            "error" => "이메일과 비밀번호를 모두 입력해주세요."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 사용자 조회
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 비밀번호 검증
    if ($user && password_verify($password, $user["password"])) {
        // 세션에 사용자 정보 저장
        $_SESSION['user_id'] = $user["id"];
        $_SESSION['user_name'] = $user["name"];
        $_SESSION['user_email'] = $user["email"];

        echo json_encode([
            "success" => true,
            "user" => [
                "name" => $user["name"],
                "email" => $user["email"]
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "이메일 또는 비밀번호가 올바르지 않습니다."
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "서버 오류: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
