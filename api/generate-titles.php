<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['topic']) || !isset($input['count'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$count = intval($input['count']);
$creditsNeeded = $count; // 1 credit per title

// Check user credits
$stmt = $conn->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['credits'] < $creditsNeeded) {
    echo json_encode(['success' => false, 'message' => "Insufficient credits. You need {$creditsNeeded} credits to generate {$count} titles."]);
    exit();
}

try {
    // Prepare the prompt for title generation
    $prompt = buildTitlePrompt($input);
    
    // Call OpenRouter API
    $response = callOpenRouterAPI($prompt);
    
    if ($response) {
        // Parse titles from response
        $titles = parseTitles($response, $count);
        
        if (!empty($titles)) {
            // Deduct credits
            $stmt = $conn->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
            $stmt->bind_param("ii", $creditsNeeded, $user_id);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'titles' => $titles,
                'credits_remaining' => $user['credits'] - $creditsNeeded
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to parse generated titles']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to generate titles']);
    }
    
} catch (Exception $e) {
    error_log("Title generation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while generating titles']);
}

function buildTitlePrompt($input) {
    $prompt = "Generate {$input['count']} engaging, SEO-optimized article titles for the topic: {$input['topic']}\n\n";
    
    if (!empty($input['keywords'])) {
        $prompt .= "Include these keywords naturally: {$input['keywords']}\n";
    }
    
    $prompt .= "Requirements:\n";
    $prompt .= "- Each title should be compelling and click-worthy\n";
    $prompt .= "- Optimize for search engines\n";
    $prompt .= "- Keep titles between 50-60 characters\n";
    $prompt .= "- Make them unique and diverse\n";
    $prompt .= "- Format as a numbered list (1. Title, 2. Title, etc.)\n";
    
    return $prompt;
}

function parseTitles($response, $expectedCount) {
    $titles = [];
    
    // Split by lines and look for numbered items
    $lines = explode("\n", $response);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Match numbered list items (1. Title, 2. Title, etc.)
        if (preg_match('/^\d+\.\s*(.+)$/', $line, $matches)) {
            $title = trim($matches[1]);
            if (!empty($title)) {
                $titles[] = $title;
            }
        }
    }
    
    // If we didn't get enough titles, try alternative parsing
    if (count($titles) < $expectedCount) {
        $titles = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !preg_match('/^(generate|here|titles|topic)/i', $line)) {
                // Remove any leading numbers, dashes, or bullets
                $title = preg_replace('/^[\d\-\*\•\.\)\]\s]+/', '', $line);
                $title = trim($title);
                
                if (!empty($title) && strlen($title) > 10) {
                    $titles[] = $title;
                }
            }
        }
    }
    
    // Return only the requested number of titles
    return array_slice($titles, 0, $expectedCount);
}

function callOpenRouterAPI($prompt) {
    $apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';
    
    if (empty($apiKey)) {
        throw new Exception('OpenRouter API key not configured');
    }
    
    $data = [
        'model' => 'meta-llama/llama-3.1-8b-instruct:free',
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 1000,
        'temperature' => 0.8
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://openrouter.ai/api/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'HTTP-Referer: https://omniseo.com',
        'X-Title: omniSEO Title Generator'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('OpenRouter API request failed with code: ' . $httpCode);
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return $result['choices'][0]['message']['content'];
    }
    
    throw new Exception('Invalid response from OpenRouter API');
}
?>
