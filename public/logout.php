<?php
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

session_unset();     // 세션 변수 제거
session_destroy();   // 세션 종료

header('Content-Type: application/json');
echo json_encode(["success" => true, "message" => "로그아웃 되었습니다."]);
?>
