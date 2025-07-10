<?php
session_start();

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/db_connect.php';

session_unset();     // 세션 변수 제거
session_destroy();   // 세션 종료

header('Content-Type: application/json');
echo json_encode(["success" => true, "message" => "로그아웃 되었습니다."]);
?>
