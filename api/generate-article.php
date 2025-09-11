<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['topic'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

// Validate input
$topic = trim($input['topic']);
$keywords = isset($input['keywords']) ? trim($input['keywords']) : '';
$instructions = isset($input['instructions']) ? trim($input['instructions']) : '';
$settings = $input['settings'] ?? [];

if (empty($topic)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Topic is required']);
    exit();
}

// Validate settings
$validTones = ['professional', 'casual', 'informative', 'persuasive', 'friendly'];
$validLengths = ['short', 'medium', 'long'];
$validContentTypes = ['blog_post', 'product_description', 'landing_page', 'news_article', 'technical_guide'];

$tone = isset($settings['tone']) && in_array($settings['tone'], $validTones) 
    ? $settings['tone'] 
    : 'informative';

$wordLength = isset($settings['length']) && in_array($settings['length'], $validLengths) 
    ? $settings['length'] 
    : 'medium';

$contentType = isset($settings['content_type']) && in_array($settings['content_type'], $validContentTypes) 
    ? $settings['content_type'] 
    : 'blog_post';

// Map word length to approximate word count
$wordCountMap = [
    'short' => rand(800, 1200),
    'medium' => rand(1500, 2000),
    'long' => rand(2500, 3500)
];

$targetWordCount = $wordCountMap[$wordLength];
$creditsRequired = calculateCreditsRequired($wordLength, $contentType);

// Check user credits
$stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['credits'] < $creditsRequired) {
    http_response_code(402);
    echo json_encode([
        'success' => false, 
        'message' => "Insufficient credits. You need {$creditsRequired} credits to generate this article.",
        'credits_required' => $creditsRequired,
        'credits_available' => $user['credits']
    ]);
    exit();
}

try {
    // Prepare the prompt for OpenRouter
    $prompt = buildArticlePrompt($topic, $keywords, $instructions, $tone, $wordLength, $contentType, $targetWordCount);
    
    // Call OpenRouter API
    $articleContent = callOpenRouterAPI($prompt, $targetWordCount);
    
    if ($articleContent) {
        // Extract title from content (first H1 or first meaningful line)
        $title = extractTitleFromContent($articleContent, $topic);
        
        // Count actual words
        $actualWordCount = str_word_count(strip_tags($articleContent));
        
        // Deduct credits
        $stmt = $db->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
        $stmt->bind_param("ii", $creditsRequired, $user_id);
        $stmt->execute();
        
        // Save article to database with all columns
        $stmt = $db->prepare("INSERT INTO articles (
            user_id, prompt, content_type, tone, word_length, title, output, word_count, credits_used, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->bind_param(
            "issssssii", 
            $user_id, 
            $prompt, 
            $contentType, 
            $tone, 
            $wordLength, 
            $title, 
            $articleContent, 
            $actualWordCount, 
            $creditsRequired
        );
        
        if ($stmt->execute()) {
            $articleId = $stmt->insert_id;
            
            echo json_encode([
                'success' => true,
                'article' => $articleContent,
                'title' => $title,
                'article_id' => $articleId,
                'word_count' => $actualWordCount,
                'credits_used' => $creditsRequired,
                'credits_remaining' => $user['credits'] - $creditsRequired,
                'generated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            throw new Exception('Failed to save article to database');
        }
    } else {
        throw new Exception('Failed to generate article content');
    }
    
} catch (Exception $e) {
    error_log("Article generation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while generating the article',
        'error' => $e->getMessage() ?? 'Internal server error'
    ]);
}

function calculateCreditsRequired($wordLength, $contentType) {
    $baseCredits = [
        'short' => 5,
        'medium' => 10,
        'long' => 15
    ];
    
    $complexityMultiplier = [
        'blog_post' => 1.0,
        'news_article' => 1.2,
        'product_description' => 1.5,
        'landing_page' => 1.8,
        'technical_guide' => 2.0
    ];
    
    $base = $baseCredits[$wordLength] ?? 10;
    $multiplier = $complexityMultiplier[$contentType] ?? 1.0;
    
    return max(1, round($base * $multiplier));
}

function buildArticlePrompt($topic, $keywords, $instructions, $tone, $wordLength, $contentType, $targetWordCount) {
    $contentTypeTemplates = [
        'blog_post' => "Write a comprehensive blog post about: {$topic}",
        'product_description' => "Create a compelling product description for: {$topic}",
        'landing_page' => "Write persuasive landing page copy for: {$topic}",
        'news_article' => "Write a news article about: {$topic}",
        'technical_guide' => "Create a technical guide about: {$topic}"
    ];
    
    $prompt = $contentTypeTemplates[$contentType] ?? "Write a comprehensive article about: {$topic}";
    $prompt .= "\n\n";
    
    if (!empty($keywords)) {
        $prompt .= "Primary keywords: {$keywords}\n";
    }
    
    if (!empty($instructions)) {
        $prompt .= "Specific instructions: {$instructions}\n";
    }
    
    $prompt .= "Writing tone: " . ucfirst($tone) . "\n";
    $prompt .= "Target length: " . number_format($targetWordCount) . " words\n";
    $prompt .= "Content type: " . str_replace('_', ' ', $contentType) . "\n";
    
    $prompt .= "\nFormatting requirements:\n";
    $prompt .= "- Format the article in clean HTML, but only include the content section.\n";
    $prompt .= "- Only output the article body, no full HTML page structure.\n";
    $prompt .= "- Do not include <html>, <head>, <body>, or <title> tags.\n";
    $prompt .= "- Use proper semantic tags like <h1>, <h2>, <p>, <ul>, <ol>, <blockquote>, <img> if needed.\n";
    $prompt .= "- Make sure the article looks ready to paste into WordPress post content.\n";
    $prompt .= "- Include meta description and title tag suggestions\n";
    $prompt .= "- Use bullet points and numbered lists where appropriate\n";
    $prompt .= "- Include engaging introduction and conclusion\n";
    $prompt .= "- Optimize for SEO with natural keyword integration\n";
    $prompt .= "- Make it informative, engaging, and well-structured\n";
    
    if ($contentType === 'technical_guide') {
        $prompt .= "- Include code examples and technical details where relevant\n";
        $prompt .= "- Use clear, precise language with proper terminology\n";
    }
    
    if ($contentType === 'landing_page') {
        $prompt .= "- Include compelling calls-to-action\n";
        $prompt .= "- Focus on benefits and value proposition\n";
        $prompt .= "- Use persuasive language and social proof elements\n";
    }
    
    return $prompt;
}

function extractTitleFromContent($content, $fallbackTitle) {
    // Try to find the first H1 tag
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/i', $content, $matches)) {
        return trim(strip_tags($matches[1]));
    }
    
    // Try to find the first H2 tag
    if (preg_match('/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches)) {
        return trim(strip_tags($matches[1]));
    }
    
    // Extract first meaningful line from content
    $lines = explode("\n", strip_tags($content));
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && strlen($line) > 10) {
            // Limit title length
            if (strlen($line) > 80) {
                $line = substr($line, 0, 77) . '...';
            }
            return $line;
        }
    }
    
    return $fallbackTitle;
}

function callOpenRouterAPI($prompt, $targetWordCount) {
    $apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';
    
    if (empty($apiKey)) {
        throw new Exception('OpenRouter API key not configured');
    }
    
    // Calculate max tokens based on word count (approx 1.33 tokens per word)
    $maxTokens = min(4000, ceil($targetWordCount * 1.33 * 1.2)); // 20% buffer
    
    $data = [
        'model' => 'mistralai/mistral-7b-instruct:free',
        'messages' => [
            [
                'role' => 'system',
                'content' => "You are a professional content writer specializing in SEO-optimized articles. Always respond with well-structured HTML content."
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => $maxTokens,
        'temperature' => 0.7,
        'top_p' => 0.9,
        'frequency_penalty' => 0.2,
        'presence_penalty' => 0.1
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://openrouter.ai/api/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'https://omniseo.com'),
            'X-Title: omniSEO Article Generator'
        ],
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("OpenRouter API Error: HTTP {$httpCode} - {$response}");
        throw new Exception("API request failed with status: {$httpCode}");
    }
    
    if ($error) {
        throw new Exception("cURL error: {$error}");
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception("OpenRouter error: " . $result['error']['message']);
    }
    
    if (isset($result['choices'][0]['message']['content'])) {
        $content = $result['choices'][0]['message']['content'];
        
        // Clean up the content
        $content = trim($content);
        $content = preg_replace('/^```html\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        
        return $content;
    }
    
    throw new Exception('No content received from API');
}