<?php
// melb_tram_api/public/session_check.php

session_start();

require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "loggedIn" => true,
        "user" => [
            "id" => $_SESSION['user_id'],
            "name" => $_SESSION['user_name'],
            "email" => $_SESSION['user_email']
        ]
    ], JSON_UNESCAPED_UNICODE); // ✅ 한글 깨짐 방지 옵션
} else {
    echo json_encode(["loggedIn" => false]);
}
