<?php
// 환경변수 설정 파일 로드
require_once __DIR__ . '/../includes/config.php';

// CORS 설정
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = explode(',', ALLOWED_ORIGINS);
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: " . APP_URL);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

// 사용자 요청 파싱
$input = json_decode(file_get_contents('php://input'), true);
$user_interest = $input['interest'] ?? '관광';
$user_time = $input['time'] ?? '오전';
$user_latitude = $input['latitude'] ?? null;
$user_longitude = $input['longitude'] ?? null;

// Gemini API 키 직접 삽입
$api_key = 'AIzaSyAQH8Gfs-o6_lFUEs7hqAqeO-yub9UOKwo';
$gemini_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;

// 위치 정보 문장 구성
$location_info = $user_latitude && $user_longitude
    ? "사용자의 현재 위치: 위도 {$user_latitude}, 경도 {$user_longitude}"
    : "사용자의 현재 위치 정보가 없습니다.";

// 프롬프트
$prompt = <<<EOT
당신은 멜버른 시티 투어 전문 가이드입니다. 다음 정보를 바탕으로 맞춤형 트램 여행 경로를 생성해주세요:

{$location_info}
사용자 관심사: {$user_interest}
소요 시간: {$user_time}

🚋 노선 기준: 35번 City Circle, 96번, 86번 트램 노선을 모두 고려하여 명소를 선택해주세요.

다음 형식으로 JSON 응답을 생성해주세요:
{
  "route": [
    {
      "name": "장소명",
      "type": "장소 유형",
      "description": "장소 설명",
      "estimated_time": "예상 소요시간(분)"
    }
  ],
  "summary": {
    "total_time": "총 소요시간(분)",
    "total_distance": "총 거리(km)"
  },
  "detailed_story": "전체 경로에 대한 상세한 스토리텔링 설명 (각 장소별 할 일, 이동 방법, 주의사항 포함)"
}

⚠️ 멜버른의 실제 명소만 포함할 것. JSON만 출력하세요. 불필요한 텍스트, 코드블럭, 마크다운 없이 JSON만 출력하세요.
EOT;

// Gemini 요청
$payload = [
    "contents" => [
        ["parts" => [["text" => $prompt]]]
    ]
];

$ch = curl_init($gemini_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 결과 변수
$route = [];
$summary = ['total_time' => 0, 'total_distance' => 0.0];
$story = '';

if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

    $json_start = strpos($text, '{');
    $json_end = strrpos($text, '}');
    if ($json_start !== false && $json_end !== false) {
        $json_str = substr($text, $json_start, $json_end - $json_start + 1);
        $parsed = json_decode($json_str, true);

        if ($parsed) {
            $route = $parsed['route'] ?? [];
            $summary = $parsed['summary'] ?? $summary;
            $story = $parsed['detailed_story'] ?? '';
        } else {
            $story = 'JSON 파싱 실패';
        }
    } else {
        $story = '응답에서 JSON 블록을 찾을 수 없음';
    }
} else {
    $story = "⚠️ Gemini API 요청 실패 또는 응답 없음 (HTTP code: $http_code)";
}

// 최종 응답
echo json_encode([
    'success' => true,
    'route' => $route,
    'summary' => $summary,
    'story' => $story
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
