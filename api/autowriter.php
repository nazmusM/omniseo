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

// Start output buffering to prevent premature output
ob_start();

$user = $auth->getCurrentUser();
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$type = $input['type'] ?? '';

if (!$action) {
    echo json_encode(['error' => 'No action specified']);
    ob_end_flush();
    exit;
}

$user_id = $_SESSION['user_id'];

// Define credit costs
define('ARTICLE_CREDITS', 1500);
define('KEYWORD_CREDITS', 10);
define('IMAGE_CREDITS', 200);
define('TITLE_CREDITS', 200);

// -------------------- CREDIT MANAGEMENT --------------------
function checkCredits($db, $user_id, $creditsRequired)
{
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

function deductCredits($db, $user_id, $creditsToDeduct, $action)
{
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
function callOpenAI($messages, $max_tokens = 4000)
{
    $payload = [
        'model' => 'gpt-4.1-mini',
        'messages' => $messages,
        'max_tokens' => $max_tokens,
        'temperature' => 0.7,
        'top_p' => 0.9,
        'frequency_penalty' => 0.2,
        'presence_penalty' => 0.1
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $_ENV['OPENAI_API_URL'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $_ENV['OPENAI_API_KEY'],
            'HTTP-Referer: ' . (isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'https://omniseo.com'),
            'X-Title: omniSEO Article Generator'
        ],
        CURLOPT_TIMEOUT => 180, // Increased timeout for longer articles
        CURLOPT_CONNECTTIMEOUT => 15
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

function generateImage($prompt)
{
    $savePath = '../assets/blog/';

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
            "Authorization: Bearer " . $_ENV['OPENROUTER_API_KEY'],
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
                return $filename; // Return the full path to the saved image
            } else {
                return ['error' => 'Failed to save image to file system'];
            }
        }
    }

    return ['error' => 'No image generated or unsupported response format'];
}

// -------------------- Build Prompt --------------------------
function buildArticlePrompt($type, $topic, $keywords = [], $instructions = '', $settings)
{
    $wordCount = $settings['word_count'] ?? 1500; // Default to 1500 words
    $tone = $settings['tone'] ?? 'professional';
    $language = $settings['language'] ?? 'English';
    $point_of_view = $settings['pointOfView'] ?? 'Second Person';
    $bold_italic = $settings['boldItalic'] ?? false;
    $faq = $settings['faq'] ?? '3 Answers';
    $key_takeaways = $settings['keyTakeaways'] ?? '3 Items';
    $external_links = $settings['externalLinks'] ?? '1 Link';

    
    
    $prompt = "Write a comprehensive, SEO-optimized article on the topic: '{$topic}'.\n\n";

    // Keywords
    if (!empty($keywords)) {
        $prompt .= "Primary keywords (integrate naturally): " . (is_array($keywords) ? implode(', ', $keywords) : $keywords) . "\n\n";
    }

    // Additional instructions
    if (!empty($instructions)) {
        $prompt .= "Specific instructions: {$instructions}\n\n";
    }

    // Tone, length, language - with emphasis on length
    $prompt .= "Writing tone: " . ucfirst($tone) . "\n";
    $prompt .= "Target length: EXACTLY {$wordCount} or more than {$wordCount} words (long, detailed, and structured) - this is crucial\n";
    $prompt .= "Content language: {$language}\n\n";
    $prompt .= "Point of view: {$point_of_view}\n\n";

    if ($bold_italic == 'Yes') {
        $prompt .= "Use bold and italic where needed or suitable\n\n";
    }

    $prompt .= "Frequently asked questions: {$faq}\n\n";
    $prompt .= "Key takeaways: {$key_takeaways}\n\n";
    $prompt .= "External links to related websites: {$external_links}\n\n";

    // Structure instructions
    $prompt .= "Article structure requirements:\n";
    $prompt .= "- Begin with an engaging introduction\n";
    $prompt .= "- Include multiple sections with subheadings\n";
    $prompt .= "- Use bullet points and numbered lists where appropriate\n";
    $prompt .= "- Include examples and practical applications\n";
    $prompt .= "- Add a FAQ section with {$faq} . make sure each question is clearly **distinguishable** from the answer. Add line breaks or spacing between Q&A pairs for readability. Keep answers concise, informative, and neutral in tone. Use proper UTF-8 characters if the article is not in English.\n";
    $prompt .= "- End with a strong conclusion and key takeaways\n";
    $prompt .= "- Ensure the article is comprehensive and meets the word count requirement\n\n";

    // JSON output
    $prompt .= "Return strictly **valid JSON only**, without code fences, arrays, or any extra text. (parseable by json_decode in PHP) with keys:\n";
    $prompt .= "{\n";
    $prompt .= "  \"success\": true,\n";

    if($type == 'bulk') {
        $prompt .= "  \"title\": \" Strictly follow this. Article title for the topic must be: {$topic}\",\n";
    } else {
        $prompt .= "  \"title\": \"SEO-friendly title for the article on {$topic}\",\n";
    }
    $prompt .= "  \"title\": \"SEO-friendly title for the article\",\n";
    $prompt .= "  \"meta_description\": \"Concise meta description under 160 characters\",\n";
    $prompt .= "  \"content\": \"Full article inside <article>...</article> tags, ready for WordPress. Do NOT repeat the title in <h1>. Use proper semantic HTML tags, bullet/numbered lists, examples, and a strong conclusion.\"\n";
    $prompt .= "}\n";

    // Crucial part: avoid repeating title in body
    $prompt .= "Important instructions:\n";
    $prompt .= "- Do NOT use the article title as the first <h1> or anywhere verbatim in the content.\n";
    $prompt .= "- Start the article with an engaging introduction that naturally introduces the topic.\n";
    $prompt .= "- Use headings (<h2>, <h3>) for sections and subtopics.\n";
    $prompt .= "- Integrate the keywords naturally without forcing them.\n\n";


    // Formatting instructions
    $prompt .= "Formatting and style:\n";
    $prompt .= "- Keep the article natural and human-like.\n";
    $prompt .= "- Only output JSON; do not include any explanation or commentary.\n";
    $prompt .= "- Do not include <html>, <head>, <body>, or <title> tags.\n";
    $prompt .= "- Make the content ready to paste directly into a WordPress post.\n";
    $prompt .= "- Use smooth transitions, multiple sections, and structured flow like a professional blog.\n";
    $prompt .= "- IMPORTANT: The article MUST be approximately {$wordCount} words in length.\n";


    return $prompt;
}

// -------------------- ARTICLE GENERATION --------------------
function generateArticle($type, $db, $project_id, $user_id, $topic, $keywords, $instructions, $settings = [])
{
    // Calculate total credits needed
    $creditsNeeded = ARTICLE_CREDITS;
    $includeImages = $settings['includeImages'] ?? false;

    // Adjust credits based on word count
    $wordCount = $settings['word_count'] ?? 1500;
    if ($wordCount > 2000) {
        $creditsNeeded += 500; // Additional credits for very long articles
    }

    if ($includeImages) {
        $creditsNeeded += IMAGE_CREDITS;
    }

    // Check credits
    $creditCheck = checkCredits($db, $user_id, $creditsNeeded);
    if (!$creditCheck['success']) {
        return ['error' => $creditCheck['error']];
    }

    $systemMsg = "You are an expert SEO content writer. Produce natural, human-readable, Google-optimized content that meets the specified word count requirements.";

    // Calculate appropriate max_tokens based on word count
    $estimatedTokens = min(4000, intval(($settings['word_count'] ?? 1500) * 1.5));

    $response = callOpenAI([
        ['role' => 'system', 'content' => $systemMsg],
        ['role' => 'user', 'content' => buildArticlePrompt($type, $topic, $keywords, $instructions, $settings)]
    ], $estimatedTokens);

    if (isset($response['error'])) {
        return $response;
    }

    $imagePath = '';
    if ($includeImages) {
        $imagePrompt = "Create a featured image relevant to this article: \"$topic\"";
        $imagePath = generateImage($imagePrompt);
    }

    $parsedData = json_decode($response, true);

    // Calculate actual word count
    $actualWordCount = str_word_count(strip_tags($parsedData['content']));

    // Deduct credits
    if (!deductCredits($db, $user_id, $creditsNeeded, 'generateArticle')) {
        return ['error' => 'Failed to deduct credits'];
    }

    $success = $parsedData['success'];
    $title = $parsedData['title'];
    $metaDescription = $parsedData['meta_description'];
    $content = $parsedData['content'];

    $publish_to_wordpress = $settings['publishToWordpress'] == 'Yes' ? true : false;
    $publish_status = $settings['publishStatus'] ?? 'draft';

    $status = 'draft';
    if ($publish_to_wordpress) {
        $status = publishToWordpress($publish_status);
    }

    // Store article in database
    $articleId = storeArticle($db, $project_id, $user_id, $topic, $title, $content, $metaDescription, $actualWordCount, $creditsNeeded, $imagePath, $status);

    return [
        'success' => $success,
        'title' => $title,
        'meta_description' => $metaDescription,
        'content' => $content,
        'image' => $imagePath,
        'article_id' => $articleId,
        'status' => $status,
        'word_count' => $actualWordCount
    ];
}

// Json parse
// function parseAiResponse($aiResponse)
// {
//     // Clean the response first
//     $cleanedResponse = trim($aiResponse);

//     // Try to find JSON in the response
//     $jsonStart = strpos($cleanedResponse, '{');
//     $jsonEnd = strrpos($cleanedResponse, '}');

//     if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
//         $jsonString = substr($cleanedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
//     } else {
//         // If no obvious JSON structure, try to parse the whole response
//         $jsonString = $cleanedResponse;
//     }

//     // Remove any markdown code block markers
//     $jsonString = preg_replace('/```(json)?/i', '', $jsonString);

//     // Decode the JSON
//     $data = json_decode($jsonString, true);

//     // Check if JSON decoding was successful
//     if (json_last_error() !== JSON_ERROR_NONE) {
//         // Try to fix common JSON issues
//         $jsonString = preg_replace('/,\s*([}\]])/', '$1', $jsonString); // Remove trailing commas
//         $jsonString = preg_replace('/([{,]\s*)(\w+)(\s*:)/', '$1"$2"$3', $jsonString); // Add quotes to unquoted keys

//         $data = json_decode($jsonString, true);

//         if (json_last_error() !== JSON_ERROR_NONE) {
//             throw new Exception('Invalid JSON response from AI: ' . json_last_error_msg() . '. Response: ' . substr($aiResponse, 0, 200));
//         }
//     }

//     // Validate the required fields
//     $requiredFields = ['success', 'title', 'meta_description', 'content'];
//     foreach ($requiredFields as $field) {
//         if (!isset($data[$field])) {
//             throw new Exception("Missing required field: $field");
//         }
//     }

//     // Return the parsed data
//     return [
//         'success' => (bool)$data['success'],
//         'title' => $data['title'],
//         'meta_description' => $data['meta_description'],
//         'content' => $data['content']
//     ];
// }


// -------------------- KEYWORD GENERATION --------------------
function generateKeywords($db, $user_id, $topic, $count = 10)
{
    // Check credits
    $creditCheck = checkCredits($db, $user_id, KEYWORD_CREDITS);
    if (!$creditCheck['success']) {
        return ['error' => $creditCheck['error']];
    }

    $systemMsg = "You are an expert SEO researcher. Provide $count relevant keywords for the topic: \"$topic\". Return keywords only in comma separated value no explanations.";

    $response = callOpenAI([
        ['role' => 'system', 'content' => $systemMsg],
        ['role' => 'user', 'content' => "Generate $count relevant keywords for: $topic."]
    ]);

    if (isset($response['error'])) {
        return $response;
    }

    // Try to parse as JSON
    $keywords = json_decode($response, true);

    // If JSON parsing failed, try to extract keywords from text
    if (!is_array($keywords) || json_last_error() !== JSON_ERROR_NONE) {
        $keywords = array_map('trim', explode(',', $response));
        $keywords = array_filter($keywords); // Remove empty values
        $keywords = array_slice($keywords, 0, $count);
    }

    // Deduct credits
    if (!deductCredits($db, $user_id, KEYWORD_CREDITS, 'generateKeywords')) {
        return ['error' => 'Failed to deduct credits'];
    }

    return [
        'success' => true,
        'keywords' => $keywords,
        'count' => count($keywords)
    ];
}

// --------------------------- Title Generation ---------------------
function generateTitles($db, $user_id, $topic, $count, $keywords)
{
    // Check credits
    $creditCheck = checkCredits($db, $user_id, TITLE_CREDITS);
    if (!$creditCheck['success']) {
        return ['error' => $creditCheck['error']];
    }

    $systemMsg = "You are an expert SEO researcher. Provide $count relevant titles for the topic: \"$topic\" and keywords: \"" . (is_array($keywords) ? implode(', ', $keywords) : $keywords) . "\". Return titles as a JSON array.";

    $response = callOpenAI([
        ['role' => 'system', 'content' => $systemMsg],
        ['role' => 'user', 'content' => "Generate $count relevant titles for: $topic. Return as JSON array."]
    ]);

    if (isset($response['error'])) {
        return $response;
    }

    // Parse the response
    $titlesArray = json_decode($response, true);

    // If JSON parsing failed, try to extract from text
    if (!is_array($titlesArray) || json_last_error() !== JSON_ERROR_NONE) {
        // Try to find array-like structure in text
        if (preg_match('/\[.*\]/s', $response, $matches)) {
            $titlesArray = json_decode($matches[0], true);
        }

        // If still not an array, create array from lines
        if (!is_array($titlesArray)) {
            $titlesArray = array_filter(array_map('trim', explode("\n", $response)));
            $titlesArray = array_slice($titlesArray, 0, $count);
        }
    }

    // Deduct credits
    if (!deductCredits($db, $user_id, TITLE_CREDITS, 'generateTitles')) {
        return ['error' => 'Failed to deduct credits'];
    }

    return [
        'success' => true,
        'titles' => $titlesArray,
    ];
}

// Store generated article in database
function storeArticle($db, $project_id, $user_id, $prompt, $title, $output, $metaDescription, $wordCount, $creditsUsed, $imageUrl = '', $status)
{
    $stmt = $db->prepare("INSERT INTO articles (user_id, project_id, prompt, title, output, meta_description, word_count, credits_used, image_url, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("iissssiiss", $user_id, $project_id,  $prompt, $title, $output, $metaDescription, $wordCount, $creditsUsed, $imageUrl, $status);
    $stmt->execute();

    return $stmt->insert_id;
}

// publish article to wordpress
function publishToWordpress($publish_status)
{
    return $publish_status;
}

// -------------------- REQUEST HANDLER --------------------
try {
    switch ($action) {
        case 'generateArticle':
            $topic = $input['topic'] ?? '';
            $keywords = $input['keywords'] ?? [];
            $instructions = $input['instructions'] ?? '';
            $settings = $input['settings'] ?? [];
            $project_id = $settings['projectId'] ?? 0;

            if (!$topic && $type !== 'bulk') {
                throw new Exception('Topic is required');
            }

            if ($type !== 'bulk' && empty($keywords)) {
                throw new Exception('Keywords are required');
            }

            $result = generateArticle($type, $db, $project_id, $user_id, $topic, $keywords, $instructions, $settings);
            echo json_encode($result);
            break;

        case 'generateTitles':
            $topic = $input['topic'] ?? '';
            $keywords = $input['keywords'] ?? [];
            $count = $input['count'] ?? 5;

            if (!$topic) {
                throw new Exception('Topic is required');
            }

            $result = generateTitles($db, $user_id, $topic, $count, $keywords);
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

        case 'createProject':
            $project_name = $input['project_name'] ?? '';
            $wp_url = $input['wp_url'] ?? '';

            if (empty($project_name) || empty($wp_url)) {
                throw new Exception('Please enter details of the project.');
            }

            $stmt = $db->prepare("INSERT INTO projects(user_id, name, wp_url) VALUES(?, ?, ?)");
            $stmt->bind_param('iss', $user_id, $project_name, $wp_url);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Project created successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create project. Please try again later.']);
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

// Ensure no extra output
ob_end_flush();
