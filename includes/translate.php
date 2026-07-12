<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$text = $_GET['text'] ?? '';
$from = $_GET['from'] ?? 'en';
$to   = $_GET['to']   ?? 'my';

if (empty($text)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing "text" parameter']);
    exit;
}

$url = 'https://api.mymemory.translated.net/get?' . http_build_query([
    'q'        => $text,
    'langpair' => $from . '|' . $to,
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(502);
    echo json_encode(['error' => 'Translation API request failed']);
    curl_close($ch);
    exit;
}

curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['responseData']['translatedText'])) {
    http_response_code(502);
    echo json_encode(['error' => 'Unexpected API response']);
    exit;
}

echo json_encode([
    'translatedText' => $data['responseData']['translatedText'],
    'match'          => $data['responseDetails'] ?? null,
]);
