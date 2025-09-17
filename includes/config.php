<?php
//Display errors
ini_set("display_errors", 1);
error_reporting(E_ALL);

// Load environment variables
function loadEnv($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Database configuration
$prod = false;

if ($prod) {
    define('DB_HOST', $_ENV['DB_HOST']);
    define('DB_NAME', $_ENV['DB_NAME']);
    define('DB_USER', $_ENV['DB_USER']);
    define('DB_PASS', $_ENV['DB_PASS']);
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'omniseo');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}



// API Keys
define('OPENAI_API_KEY', $_ENV['OPENAI_API_KEY'] ?? '');

// Site configuration
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost/omniseo');
define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'omniSEO');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
