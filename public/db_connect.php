<?php
// melb_tram_api/db_connect.php

$host = 'localhost';
$dbname = 'melbourne_tram_guide';
$username = 'root';
$password = ''; // XAMPP 기본값. 설정 다르면 맞게 바꿔줘

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "데이터베이스 연결 실패: " . $e->getMessage()]);
    exit;
}
?>
