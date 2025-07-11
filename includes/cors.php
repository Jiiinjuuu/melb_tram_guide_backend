<?php
$origin = "https://melb-stamp-tour.netlify.app";  // 프론트 주소 명확히 설정

header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true"); // ✅ 이게 있어야 쿠키 저장됨
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
