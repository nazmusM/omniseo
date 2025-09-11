<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$auth->requireLogin();
$user = $auth->getCurrentUser();

// Check if user has enough credits
if ($user['credits'] < 10) {
    echo json_encode(['success' => false, 'message' => 'Insufficient credits. Please top-up your account.']);
    exit;
}

// Sanitize input
$prompt = trim($_POST['prompt'] ?? '');
$content_type = $_POST['content_type'] ?? 'blog_article';
$tone = $_POST['tone'] ?? 'informative';
$word_length = $_POST['word_length'] ?? 'medium';

if (empty($prompt)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a content prompt']);
    exit;
}

// Determine word count based on length
$word_counts = [
    'short' => '300-500',
    'medium' => '500-800',
    'long' => '800-1200'
];

$target_words = $word_counts[$word_length] ?? '500-800';

// Create OpenAI prompt
$system_prompt = "You are an expert SEO content writer. Create high-quality, engaging, and SEO-optimized content that reads naturally and provides value to readers. Include relevant headings (H2, H3), bullet points where appropriate, and ensure good readability. The content should be original, informative, and well-structured.";

$user_prompt = "Write a {$content_type} about: {$prompt}

Requirements:
- Tone: {$tone}
- Word count: {$target_words} words
- Include relevant H2 and H3 headings
- Use bullet points or numbered lists where appropriate
- Make it SEO-friendly but natural to read
- Include a compelling introduction and conclusion
- Add suggestions for meta description and keywords at the end

Please format the content with proper markdown formatting.";

// Make OpenAI API call
$openai_data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => $system_prompt],
        ['role' => 'user', 'content' => $user_prompt]
    ],
    'max_tokens' => 2000,
    'temperature' => 0.7
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($openai_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . OPENAI_API_KEY
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode(['success' => false, 'message' => 'Content generation failed. Please try again.']);
    exit;
}

$openai_response = json_decode($response, true);

if (!isset($openai_response['choices'][0]['message']['content'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid response from AI service.']);
    exit;
}

$generated_content = $openai_response['choices'][0]['message']['content'];
$word_count = str_word_count(strip_tags($generated_content));

// Deduct credits
$new_credits = $user['credits'] - 10;
$auth->updateCredits($user['id'], $new_credits);

// Save to database
global $db;
$stmt = $db->prepare("INSERT INTO articles (user_id, prompt, content_type, tone, word_length, output, word_count, credits_used) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$credits_used = 10;
$stmt->bind_param("isssssii", $user['id'], $prompt, $content_type, $tone, $word_length, $generated_content, $word_count, $credits_used);

if ($stmt->execute()) {
    $generation_id = $db->getConnection()->insert_id;
    
    // Format content for display
    $formatted_content = nl2br(htmlspecialchars($generated_content));
    
    echo json_encode([
        'success' => true,
        'content' => $formatted_content,
        'word_count' => $word_count,
        'remaining_credits' => $new_credits,
        'generation_id' => $generation_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save generated content.']);
}
?>
