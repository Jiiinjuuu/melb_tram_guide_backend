<?php
// melb_tram_api/public/db_connect.php

$host = 'yamabiko.proxy.rlwy.net';
$port = '55085';
$dbname = 'railway';
$username = 'root';
$password = 'LLeiCmOoBITYlbPUxlNtUdfUfXLNXMyt';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB 연결 실패: " . $e->getMessage()]);
    exit;
}
