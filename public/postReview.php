<?php
require_once "../includes/cors.php";
require_once "db_connect.php";

header("Content-Type: application/json");

try {
    if (!isset($_POST['place_id'], $_POST['user_id'], $_POST['content'], $_POST['rating'])) {
        echo json_encode(["success" => false, "message" => "필수 항목 누락"]);
        exit;
    }

    $place_id = $_POST['place_id'];
    $user_id = $_POST['user_id'];
    $content = trim($_POST['content']);
    $rating = intval($_POST['rating']);

    // 이미지 파일 업로드 처리
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/reviews/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $filename = uniqid() . "_" . basename($_FILES['image']['name']);
        $target = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_url = "/uploads/reviews/" . $filename;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO reviews (place_id, user_id, content, rating, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$place_id, $user_id, $content, $rating, $image_url]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
