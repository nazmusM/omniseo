<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

// omniSEO API Endpoint
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$user = $auth->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
if (!$action) {
    echo json_encode(['error' => 'No action specified']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Define credit costs
define('ARTICLE_CREDITS', 1500);
define('KEYWORD_CREDITS', 10);
define('IMAGE_CREDITS', 200);
define('TITLE_CREDITS', 200);

// -------------------- CREDIT MANAGEMENT --------------------
function checkCredits($db, $user_id, $creditsRequired) {
    $stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'error' => 'User not found'];
    }
    
    $user = $result->fetch_assoc();
    
    if ($user['credits'] < $creditsRequired) {
        return ['success' => false, 'error' => 'Insufficient credits'];
    }
    
    return ['success' => true, 'credits' => $user['credits']];
}

function deductCredits($db, $user_id, $creditsToDeduct, $action) {
    // Start transaction for atomic operation
    $db->begin_transaction();
    
    try {
        // Update user credits
        $stmt = $db->prepare("UPDATE users SET credits = credits - ? WHERE id = ? AND credits >= ?");
        $stmt->bind_param("iii", $creditsToDeduct, $user_id, $creditsToDeduct);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            $db->rollback();
            return false;
        }
        
        // Log credit usage
        $stmt = $db->prepare("INSERT INTO credit_usage (user_id, credits_used, action, timestamp) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $creditsToDeduct, $action);
        $stmt->execute();
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        $db->rollback();
        error_log("Credit deduction error: " . $e->getMessage());
        return false;
    }
}

// -------------------- UTILITY --------------------
function callOpenAI($messages, $max_tokens = 1000) {
    $payload = [
        'model' => 'deepseek/deepseek-chat-v3.1:free',
        'messages' => $messages,
        'max_tokens' => $max_tokens,
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
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $_ENV['OPENROUTER_API_KEY'],
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
    
    if ($error) {
        return ['error' => 'CURL error: ' . $error];
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['error'])) {
        return ['error' => 'API error: ' . $data['error']['message']];
    }
    
    if (isset($data['choices'][0]['message']['content'])) {
        return $data['choices'][0]['message']['content'];
    }
    
    return ['error' => 'No content returned from AI'];
}

function generateImage($prompt, $size = '1024x1024') {
    // Use OpenRouter's free image generation (Stable Diffusion)
    $payload = [
        'model' => 'google/gemini-2.5-flash-image-preview:free',
        'prompt' => $prompt,
        'size' => $size,
        'n' => 1
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://openrouter.ai/api/v1/images',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $_ENV['OPENROUTER_API_KEY'],
            'HTTP-Referer: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'https://omniseo.com'),
            'X-Title: omniSEO Image Generator'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => 'CURL error: ' . $error];
    }
    
    curl_close($ch);
    $data = json_decode($response, true);
    
    if (isset($data['error'])) {
        return ['error' => 'API error: ' . $data['error']['message']];
    }
    
    if (isset($data['data'][0]['url'])) {
        return $data['data'][0]['url'];
    }
    
    return ['error' => 'No image returned'];
}

// -------------------- Build Prompt --------------------------
function buildArticlePrompt($topic, $keywords, $instructions, $tone, $wordCount, $language) {
    $prompt = "Write a comprehensive article about: {$topic}\n\n";
    
    if (!empty($keywords)) {
        $prompt .= "Primary keywords: " . (is_array($keywords) ? implode(', ', $keywords) : $keywords) . "\n";
    }
    
    if (!empty($instructions)) {
        $prompt .= "Specific instructions: {$instructions}\n";
    }
    
    $prompt .= "Writing tone: " . ucfirst($tone) . "\n";
    $prompt .= "Target length: " . $wordCount . " words\n";
    $prompt .= "Content language: " . $language . "\n";
    
    $prompt .= "\nFormatting requirements:\n";
    $prompt .= "- Format the article in clean HTML, but only include the content section.\n";
    $prompt .= "- Only output the article body, no full HTML page structure.\n";
    $prompt .= "- Do not include <html>, <head>, <body>, or <title> tags.\n";
    $prompt .= "- Use proper semantic tags like <h1>, <h2>, <p>, <ul>, <ol>, <blockquote>.\n";
    $prompt .= "- Make sure the article looks ready to paste into WordPress post content.\n";
    $prompt .= "- Include meta description and title tag suggestions at the end\n";
    $prompt .= "- Use bullet points and numbered lists where appropriate\n";
    $prompt .= "- Include engaging introduction and conclusion\n";
    $prompt .= "- Optimize for SEO with natural keyword integration\n";
    $prompt .= "- Make it informative, engaging, and well-structured\n";
    
    return $prompt;
}

// -------------------- Process Output ------------------------
function processAiOutput($aiOutput) {
    $metaDescription = '';
    $title = '';

    // Extract meta description suggestion
    if (preg_match('/<!--\s*Meta Description Suggestion:\s*(.*?)\s*-->/is', $aiOutput, $metaMatch)) {
        $metaDescription = trim($metaMatch[1]);
        $aiOutput = str_replace($metaMatch[0], '', $aiOutput);
    }

    // Extract title tag suggestion
    if (preg_match('/<!--\s*Title Tag Suggestion:\s*(.*?)\s*-->/is', $aiOutput, $titleTagMatch)) {
        $title = trim($titleTagMatch[1]);
        $aiOutput = str_replace($titleTagMatch[0], '', $aiOutput);
    }

    // Clean up any remaining HTML comments
    $aiOutput = preg_replace('/<!--.*?-->/s', '', $aiOutput);
    
    // Remove extra whitespace and newlines
    $aiOutput = trim($aiOutput);

    return [
        'content' => $aiOutput,
        'meta_description' => $metaDescription,
        'title' => $title
    ];
}



// -------------------- ARTICLE GENERATION --------------------
function generateArticle($db, $user_id, $topic, $keywords, $instructions, $settings = []) {
    // Calculate total credits needed
    $creditsNeeded = ARTICLE_CREDITS;
    $includeImages = $settings['includeImages'] ?? false;
    
    if ($includeImages) {
        $creditsNeeded += IMAGE_CREDITS;
    }
    
    // Check credits
    $creditCheck = checkCredits($db, $user_id, $creditsNeeded);
    if (!$creditCheck['success']) {
        return ['error' => $creditCheck['error']];
    }
    
    $wordCount = $settings['word_count'] ?? 500;
    $tone = $settings['tone'] ?? 'professional';
    $language = $settings['language'] ?? 'English';
    
    $systemMsg = "You are an expert SEO content writer. Produce natural, human-readable, Google-optimized content.";
    
    $response = callOpenAI([
        ['role' => 'system', 'content' => $systemMsg],
        ['role' => 'user', 'content' => buildArticlePrompt($topic, $keywords, $instructions, $tone, $wordCount, $language)]
    ], $wordCount * 5);
    
    if (isset($response['error'])) {
        return $response;
    }
    
    $imageUrl = '';
    if ($includeImages) {
        $imagePrompt = "Create a featured image relevant to this article: \"$topic\"";
        $imageResult = generateImage($imagePrompt);
        
        if (!isset($imageResult['error'])) {
            $imageUrl = $imageResult;
        }
        // Continue even if image generation fails
    }
    
    // Process the AI output to extract title and metadata
    $processedOutput = processAiOutput($response);
    
    $title = !empty($processedOutput['title']) ? $processedOutput['title'] : $topic;
    $content = $processedOutput['content'];
    $metaDescription = $processedOutput['meta_description'] ?? '';
    
    // Calculate actual word count
    $actualWordCount = str_word_count(strip_tags($content));
    
    // Deduct credits
    if (!deductCredits($db, $user_id, $creditsNeeded, 'generateArticle')) {
        return ['error' => 'Failed to deduct credits'];
    }
    
    // Store article in database
    $articleId = storeArticle($db, $user_id, $topic, $tone, $wordCount, $title, $content, $metaDescription, $actualWordCount, $creditsNeeded, $imageUrl);
    
    return [
        'success' => true,
        'title' => $title,
        'content' => $content,
        'meta_description' => $metaDescription,
        'image' => $imageUrl,
        'article_id' => $articleId,
    ];
}

// -------------------- BULK ARTICLE GENERATION --------------------
function generateBulkArticles($db, $user_id, $titles, $settings = [], $keywords = [], $instructions = '') {
    $articles = [];
    
    foreach ($titles as $title) {
        $articleSettings = $settings;
        $articleSettings['title'] = $title;
        
        $result = generateArticle($db, $user_id, $title, $keywords, $instructions, $articleSettings);
        
        if (isset($result['error'])) {
            // Stop processing if we encounter an error (like insufficient credits)
            return ['error' => "Failed to generate article '$title': " . $result['error']];
        }
        
        $articles[] = $result;
    }
    
    return $articles;
}

// -------------------- KEYWORD GENERATION --------------------
function generateKeywords($db, $user_id, $topic, $count = 10) {
    // Check credits
    $creditCheck = checkCredits($db, $user_id, KEYWORD_CREDITS);
    if (!$creditCheck['success']) {
        return ['error' => $creditCheck['error']];
    }
    
    $systemMsg = "You are an expert SEO researcher. Provide $count relevant keywords for the topic: \"$topic\". Return keywords as comma-separated value, no explanations, no numbers, just keywords.";
    
    $response = callOpenAI([
        ['role' => 'system', 'content' => $systemMsg],
        ['role' => 'user', 'content' => "Generate $count relevant keywords for: $topic"]
    ], 200);
    
    if (isset($response['error'])) {
        return $response;
    }
    
    // Deduct credits
    if (!deductCredits($db, $user_id, KEYWORD_CREDITS, 'generateKeywords')) {
        return ['error' => 'Failed to deduct credits'];
    }
    
    // Parse the response into an array
    $keywords = array_map('trim', explode(',', $response));
    $keywords = array_filter($keywords); // Remove empty values
    
    return [
        'success' => true,
        'keywords' => $keywords,
        'count' => count($keywords)
    ];
}

// --------------------------- Title Generation ---------------------
function generateTitles($db, $user_id, $topic, $count = 10) {
    // Check credits
    $creditCheck = checkCredits($db, $user_id, TITLE_CREDITS);
    if (!$creditCheck['success']) {
        return ['error' => $creditCheck['error']];
    }
    
    $systemMsg = "You are an expert SEO researcher. Provide $count relevant titles for the topic: \"$topic\". Return titles as an array, no explanations, no numbers, just titles.";
    
    $response = callOpenAI([
        ['role' => 'system', 'content' => $systemMsg],
        ['role' => 'user', 'content' => "Generate $count relevant titles for: $topic"]
    ], 200);
    
    if (isset($response['error'])) {
        return $response;
    }
    
    // Convert OpenAI response string to array
    if (is_string($response)) {
        $titlesArray = json_decode($response, true);
        if (!is_array($titlesArray)) {
            return ['error' => 'Failed to parse titles'];
        }
    } else {
        $titlesArray = $response;
    }
    
    // Deduct credits
    if (!deductCredits($db, $user_id, TITLE_CREDITS, 'generateTitles')) {
        return ['error' => 'Failed to deduct credits'];
    }
    
    header('Content-Type: application/json');
    return [
        'success' => true,
        'titles' => $titlesArray, // ✅ Proper array
    ];
}




// Store generated article in database
function storeArticle($db, $user_id, $prompt, $tone, $wordLength, $title, $output, $metaDescription, $wordCount, $creditsUsed, $imageUrl = '') {
    $stmt = $db->prepare("INSERT INTO articles (user_id, prompt, tone, word_length, title, output, meta_description, word_count, credits_used, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ississsiis", $user_id, $prompt, $tone, $wordLength, $title, $output, $metaDescription, $wordCount, $creditsUsed, $imageUrl);
    $stmt->execute();
    
    return $stmt->insert_id;
}

// -------------------- REQUEST HANDLER --------------------
try {
    switch ($action) {
        case 'generateArticle':
            $topic = $input['topic'] ?? '';
            $keywords = $input['keywords'] ?? [];
            $instructions = $input['instructions'] ?? '';
            $settings = $input['settings'] ?? [];
            
            if (!$topic) {
                throw new Exception('Topic is required');
            }

            if (!$keywords) {
                throw new Exception('Keywords are required');
            }
            
            $result = generateArticle($db, $user_id, $topic, $keywords, $instructions, $settings);
            echo json_encode($result);
            break;


        case 'generateTitles':
            $topic = $input['topic'] ?? '';
            $keywords = $input['keywords'] ?? [];
            $instructions = $input['instructions'] ?? '';
            $settings = $input['settings'] ?? [];

            if (!$topic) {
                throw new Exception('Topic is required');
            }

            if (!$keywords) {
                throw new Exception('Keywords are required');
            }

            $result = generateTitles($db, $user_id, $topic, $keywords, $instructions, $settings);
            echo json_encode($result);
            break;
            

        case 'bulkArticles':
            $titles = $input['titles'] ?? [];
            $keywords = $input['keywords'] ?? [];
            $instructions = $input['instructions'] ?? '';
            $settings = $input['settings'] ?? [];
            
            if (empty($titles)) {
                throw new Exception('Titles are required');
            }
            
            $result = generateBulkArticles($db, $user_id, $titles, $settings, $keywords, $instructions);
            echo json_encode($result);
            break;

        case 'generateKeywords':
            $topic = $input['topic'] ?? '';
            $count = $input['count'] ?? 10;
            
            if (!$topic) {
                throw new Exception('Topic is required');
            }
            
            $result = generateKeywords($db, $user_id, $topic, $count);
            echo json_encode($result);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>