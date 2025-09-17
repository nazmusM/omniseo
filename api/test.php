<?php
$apiKey = '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';


function generateImage($prompt, $apiKey) {
    $savePath = 'opt/lampp/htdocs/omniseo/assets/images/';
    // Validate API key
    if (!$apiKey) {
        return ['error' => 'OpenRouter API key not set'];
    }
    
    // Ensure the save path exists and is writable
    if (!is_dir($savePath)) {
        if (!mkdir($savePath, 0755, true)) {
            return ['error' => "Could not create directory: $savePath"];
        }
    }
    
    if (!is_writable($savePath)) {
        return ['error' => "Directory is not writable: $savePath"];
    }

    // Prepare the API request
    $payload = [
        "model" => "google/gemini-2.5-flash-image-preview",
        "messages" => [
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        "modalities" => ["image", "text"],
        "max_tokens" => 500
    ];

    $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey,
            "HTTP-Referer: " . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'https://omniseo.com'),
            "X-Title: omniSEO"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 120 // Increased timeout for image generation
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['error' => "CURL error: $err"];
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data['error'])) {
        return ['error' => 'API error: ' . $data['error']['message']];
    }

    // Process the image response
    if (isset($data['choices'][0]['message']['images']) && is_array($data['choices'][0]['message']['images'])) {
        $img = $data['choices'][0]['message']['images'][0];
        $imageData = null;
        $extension = 'png';
        
        // Handle different response formats
        if (isset($img['image_url']['url']) && !empty($img['image_url']['url'])) {
            $imageUrl = $img['image_url']['url'];
            $imageData = file_get_contents($imageUrl);
        } elseif (isset($img['image_url']) && is_string($img['image_url']) && !empty($img['image_url'])) {
            $imageUrl = $img['image_url'];
            $imageData = file_get_contents($imageUrl);
        } elseif (isset($img['url']) && !empty($img['url'])) {
            $imageUrl = $img['url'];
            $imageData = file_get_contents($imageUrl);
        } elseif (isset($img['b64_json']) && !empty($img['b64_json'])) {
            $imageData = base64_decode($img['b64_json']);
        }
        
        // Save the image if we have data
        if ($imageData) {
            // Generate a unique filename
            $filename = 'generated_image_' . time() . '_' . uniqid() . '.' . $extension;
            $fullPath = $savePath . $filename;
            
            // Save the image file
            if (file_put_contents($fullPath, $imageData)) {
                return $fullPath; // Return the full path to the saved image
            } else {
                return ['error' => 'Failed to save image to file system'];
            }
        }
    }
    
    return ['error' => 'No image generated or unsupported response format'];
}

// Example usage:
 $imagePath = generateImage("Generate a beautiful landscape image", $apiKey);

if (is_array($imagePath) && isset($imagePath['error'])) {
    // Handle error
    echo "Error: " . $imagePath['error'];
} else {
    // Store $imagePath in database
    echo "Image saved to: " . $imagePath;
}
?>