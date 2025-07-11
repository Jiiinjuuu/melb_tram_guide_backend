<?php
// melb_tram_api/public/session_check.php

require_once __DIR__ . '/../includes/cors.php'; // ✅ 먼저 CORS 헤더부터 설정해야 함
session_start();

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
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["loggedIn" => false]);
}
