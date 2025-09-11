<?php
ini_set("dispaly_errors", 1);
error_reporting(E_ALL);
// omniSEO API Endpoint
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';


header('Content-Type: application/json');

$auth->requireLogin();
$user = $auth->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if (!$action) {
    echo json_encode(['error' => 'No action specified']);
    exit;
}

$user_id = $_SESSION['user_id'];

// function can_generate($db, $user_id, $creditsRequired){
// // Check user credits
// $stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
// $stmt->bind_param("i", $user_id);
// $stmt->execute();
// $user = $stmt->get_result()->fetch_assoc();

// if ($user['credits'] < $creditsRequired) {
//     http_response_code(402);
//     return false;
// }
// }



// -------------------- UTILITY --------------------
function callOpenAI($messages, $max_tokens=1000) {
    $payload = [
        'model' => 'mistralai/mistral-7b-instruct:free',
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
    $data = json_decode($response, true);
    json_encode($data);
    if(isset($data['choices'][0]['message']['content'])){
        return $data['choices'][0]['message']['content'];
    }
    return ['error' => 'No content returned from AI'];
}

function generateImage($prompt, $size='1024x1024') {
    $payload = [
        'prompt' => $prompt,
        'n' => 1,
        'size' => $size
    ];
    $ch = curl_init($_ENV['OPENAI_IMAGE_URL']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $result = curl_exec($ch);
    if(curl_errno($ch)){
        curl_close($ch);
        return ['error' => curl_error($ch)];
    }
    curl_close($ch);
    $data = json_decode($result, true);
    if(isset($data['data'][0]['url'])){
        return $data['data'][0]['url'];
    }
    return ['error' => 'No image returned'];
}

// -------------------- Build Prompt --------------------------
function buildArticlePrompt($topic, $keywords, $instructions, $tone, $wordCount, $language) {
    $contentTypeTemplates = [
        'blog_post' => "Write a comprehensive blog post about: {$topic}",
        'product_description' => "Create a compelling product description for: {$topic}",
        'landing_page' => "Write persuasive landing page copy for: {$topic}",
        'news_article' => "Write a news article about: {$topic}",
        'technical_guide' => "Create a technical guide about: {$topic}"
    ];
    
    $prompt = "Write a comprehensive article about: {$topic}";
    $prompt .= "\n\n";
    
    if (!empty($keywords)) {
        $prompt .= "Primary keywords: {$keywords}\n";
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
    $prompt .= "- Use proper semantic tags like <h1>, <h2>, <p>, <ul>, <ol>, <blockquote>, <img> if needed.\n";
    $prompt .= "- Make sure the article looks ready to paste into WordPress post content.\n";
    $prompt .= "- Include meta description and title tag suggestions\n";
    $prompt .= "- Use bullet points and numbered lists where appropriate\n";
    $prompt .= "- Include engaging introduction and conclusion\n";
    $prompt .= "- Optimize for SEO with natural keyword integration\n";
    $prompt .= "- Make it informative, engaging, and well-structured\n";
    
    return $prompt;
}

// -------------------- ARTICLE GENERATION --------------------
function generateArticle($topic, $keywords, $instructions, $settings=[], $userId=null) {
    $title = $settings['title'] ?? '';
    $includeImages = $settings['images'] ?? false;
    $wordCount = $settings['word_count'] ?? 500;

    $systemMsg = "You are an expert SEO content writer. Produce natural, human-readable, Google-optimized content.";

    $response = callOpenAI([
        ['role'=>'system','content'=>$systemMsg],
        ['role'=>'user','content'=> buildArticlePrompt($topic, $keywords, $instructions, $settings['tone'], $settings['length'], $settings['language'])]
    ], $wordCount*5);

    $imageUrl = '';
    if($includeImages && isset($response['error'])===false){
        $imagePrompt = "Create a featured image relevant to this article: \"$topic\"";
        $imageUrl = generateImage($imagePrompt);
    }

    return [
        'success' => true,
        'title' => $title ?: $topic,
        'content' => $response,
        'image' => $imageUrl,
        'article_id' => 3
    ];
}

// -------------------- BULK ARTICLE GENERATION --------------------
function generateBulkArticles($titles, $settings=[], $keywords=[], $instructions='') {
    $articles = [];
    foreach($titles as $title){
        $articles[] = generateArticle($title, $keywords, $instructions, $settings + ['title'=>$title]);
    }
    return $articles;
}

// -------------------- KEYWORD GENERATION --------------------
function generateKeywords($topic, $count) {
    $systemMsg = "You are an expert SEO researcher. Provide $count relevant keywords for the topic: \"$topic\". Return keywords as comma-separated value, no explanations. no numbers. just keywords.";
    $response = callOpenAI([
        ['role'=>'system','content'=>$systemMsg],
        ['role'=>'user','content'=>"Generate keywords"]
    ], 200);

    return $response;
}

// -------------------- REQUEST HANDLER --------------------
switch($action){
    case 'generateArticle':
        $topic = $input['topic'] ?? '';
        $keywords = $input['keywords'] ?? [];
        $instructions = $input['instructions'] ?? '';
        $settings = $input['settings'] ?? [];
        // if(can_generate($db, $user_id, $creditsRequired)) { echo json_encode(['error'=>'TInsufficient credit. Please add credit and try again.']); exit; }
        if(!$topic || !$keywords) { echo json_encode(['error'=>'Topic and keywords are required']); exit; }
        $result = generateArticle($topic, $keywords, $instructions, $settings, $user['id']);
        echo json_encode($result);
        break;

    case 'bulkArticles':
        $titles = $input['titles'] ?? [];
        $keywords = $input['keywords'] ?? [];
        $instructions = $input['instructions'] ?? '';
        $settings = $input['settings'] ?? [];
        if(empty($titles)) { echo json_encode(['error'=>'Titles required']); exit; }
        $result = generateBulkArticles($titles, $settings, $keywords, $instructions);
        echo json_encode($result);
        break;

    case 'generateKeywords':
        $topic = $input['topic'] ?? '';
        $count = 10;
        if(!$topic) { echo json_encode(['error'=>'Topic required']); exit; }
        $result = generateKeywords($topic, $count);
        echo json_encode(['success' => true, 'keywords' => $result]);
        break;

    default:
        echo json_encode(['error'=>'Invalid action']);
        break;
}

?>