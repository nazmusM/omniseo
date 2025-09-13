<?php
$api = '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';

function generateImage($prompt, $api, $size = '1024x1024') {
    $payload = [
        "model" => "google/gemini-2.5-flash-image-preview",
  "messages" => [
      "role" => "user",
      "content" => [
          "type" => "text",
          "text" => "What is in this image?"
          ],
      "type" => "image_url"
          ["image_url" => [
            "url" => "https://upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Gfp-wisconsin-madison-the-nature-boardwalk.jpg/2560px-Gfp-wisconsin-madison-the-nature-boardwalk.jpg"
          
          ]
  ]
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://openrouter.ai/api/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api,
            'HTTP-Referer: ' . ($_SERVER['HTTP_ORIGIN'] ?? 'https://omniseo.com'),
            'X-Title: omniSEO Image Generator'
        ],
        CURLOPT_TIMEOUT => 60
    ]);
    
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ["error" => "CURL error: " . $error];
    }
    
    curl_close($ch);

    // 🔎 Return raw API response so we can inspect
    return ["raw" => $response];
}

header('Content-Type: application/json');
echo json_encode([
    "image" => generateImage("A futuristic cyberpunk city at night with neon lights", $api)
]);
