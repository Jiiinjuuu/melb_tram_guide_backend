<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';

// CORS 설정
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = explode(',', ALLOWED_ORIGINS);
header("Access-Control-Allow-Origin: " . (in_array($origin, $allowed_origins) ? $origin : APP_URL));
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 사용자 입력 파싱
$input = json_decode(file_get_contents('php://input'), true);
$user_interest = $input['interest'] ?? '관광';
$user_time = $input['time'] ?? '오전';
$user_latitude = $input['latitude'] ?? null;
$user_longitude = $input['longitude'] ?? null;

// Gemini API 키 확인
$api_key = GEMINI_API_KEY;
if (empty($api_key)) {
    http_response_code(500);
    echo json_encode(["error" => "Gemini API 키가 설정되지 않았습니다."]);
    exit();
}

// 프롬프트 생성
$location_info = $user_latitude && $user_longitude
    ? "사용자의 현재 위치: 위도 {$user_latitude}, 경도 {$user_longitude}"
    : "사용자의 현재 위치 정보가 없습니다.";

$prompt = <<<EOT
당신은 멜버른 트램 여행 전문가입니다.
아래 사용자 조건을 참고하여 여행 루트를 추천하고, JSON 형식으로만 출력해 주세요.

{$location_info}
관심사: {$user_interest}
시간대: {$user_time}
노선 기준: 멜버른 City Circle (35번 트램) 노선을 중심으로 추천
장소 조건: 실제 존재하는 명소만 포함 (역, 광장, 박물관, 카페 등 다양하게)

🎯 출력 형식 (꼭 지킬 것):
{
  "route": [
    {
      "name": "플린더스 스트리트 역",
      "type": "역",
      "description": "멜버른의 대표적인 랜드마크에서 사진을 찍고 트램 투어를 시작합니다.",
      "estimated_time": 30,
      "is_stampPlace": 1
    },
    ...
  ],
  "summary": {
    "total_time": 120,
    "total_distance": 3.2,
    "stamp_count": 2
  },
  "detailed_story": "이 루트는 멜버른의 도시적 매력을 짧은 시간에 체험할 수 있는 코스로, 첫 장소인 플린더스 스트리트 역부터..."
}

⚠️ 반드시 JSON만 출력하세요. 코드 블록(예: ```json)은 쓰지 마세요. 설명 문장도 출력하지 마세요.
EOT;

// Gemini 요청
$gemini_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key;
$payload = [ "contents" => [["parts" => [["text" => $prompt]]]] ];

$ch = curl_init($gemini_url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 30,
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 초기값
$route = [];
$summary = ['total_time' => 120, 'total_distance' => 3.2, 'stamp_count' => 0];
$story = '';
$place_descriptions = [];
$debug_raw = '';

// Gemini 응답 처리
if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    $ai_raw = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $debug_raw = $ai_raw;

    // 마크다운 제거
    $cleaned = preg_replace('/```(json)?/i', '', $ai_raw);
    $cleaned = trim($cleaned);

    // JSON 추출
    $json_start = strpos($cleaned, '{');
    $json_end = strrpos($cleaned, '}');
    if ($json_start !== false && $json_end !== false) {
        $json_str = substr($cleaned, $json_start, $json_end - $json_start + 1);
        $parsed_data = json_decode($json_str, true);

        if (is_array($parsed_data)) {
            if (isset($parsed_data['route'])) {
                $route = $parsed_data['route'];
                foreach ($route as $item) {
                    if (isset($item['name']) && isset($item['description'])) {
                        $place_descriptions[$item['name']] = $item['description'];
                    }
                }
            }
            if (isset($parsed_data['summary'])) {
                $summary = $parsed_data['summary'];
            }
            if (isset($parsed_data['detailed_story'])) {
                $story = $parsed_data['detailed_story'];
            }
        } else {
            $story = "⚠️ Gemini 응답은 있었지만 JSON 파싱에 실패했습니다.";
        }
    } else {
        $story = "⚠️ Gemini 응답에서 JSON 구조를 찾을 수 없습니다.";
    }
} else {
    $story = "⚠️ Gemini API 요청 실패 또는 응답 없음 (HTTP code: $http_code)";
}

// 최종 응답 반환
echo json_encode([
    'success' => true,
    'route' => $route,
    'summary' => $summary,
    'detailed_story' => $story,
    'place_descriptions' => $place_descriptions,
    'debug_raw' => APP_ENV !== 'production' ? $debug_raw : null
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
