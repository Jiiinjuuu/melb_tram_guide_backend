<?php
header('Content-Type: application/json; charset=utf-8');

// CORS 허용 (개발용, 보안 신경 안 써도 될 때)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// OPTIONS 프리플라이트 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. 설문 데이터 파싱 (POST)
$input = json_decode(file_get_contents('php://input'), true);
$user_interest = $input['interest'] ?? '관광';
$user_time = $input['time'] ?? '오전';

// 2. 임시 추천 경로 생성 (샘플 데이터)
$route = [
    ["name" => "플린더스 스트리트 역", "type" => "역"],
    ["name" => "페더레이션 광장", "type" => "관광지"],
    ["name" => "멜버른 박물관", "type" => "박물관"],
    ["name" => "카페 데 그라운즈", "type" => "카페"]
];

// 3. Gemini API 호출 (REST, cURL)
$api_key = 'AIzaSyAohHn9GzPsjMoxkTyD2ToW4dI5PeNSEUI';
$gemini_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;

// 프롬프트 생성 (장소별 순서, 각 장소에서 할 일, 다음 장소까지 소요 시간, 3줄 이내)
$prompt = "아래는 멜버른 트램 여행 추천 경로입니다. 각 장소별로 한 줄씩, 해당 장소에서 할 일과 다음 장소까지 이동 시간(약 15분)을 포함해 설명해 주세요. 예시: 1. [장소명]: [설명]";
foreach ($route as $idx => $place) {
    $next = isset($route[$idx + 1]) ? $route[$idx + 1]['name'] : null;
    $prompt .= "\n" . ($idx + 1) . ". " . $place['name'] . ":";
    if ($next) {
        $prompt .= " 다음 장소까지 이동 시간 약 15분.";
    }
}

$payload = [
    "contents" => [
        ["parts" => [["text" => $prompt]]]
    ]
];

$ch = curl_init($gemini_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$story = '';
if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $story = $data['candidates'][0]['content']['parts'][0]['text'];
    } else {
        $story = 'AI 설명 생성에 실패했습니다.';
    }
} else {
    $story = "http_code: $http_code, response: $response";
}

// 4. 결과 반환
echo json_encode([
    'success' => true,
    'route' => $route,
    'summary' => [
        'total_time' => 120, // 예시: 120분 (2시간)
        'total_distance' => 3.2, // 예시: 3.2km
        'stamp_count' => 2 // 예시: 2개
    ],
    'story' => $story
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); 