<?php
ini_set("display_errors", 1);

$basePath = ($_SERVER['REQUEST_URI'] == '/' ? '' : '../');
// $basePath = '';

require_once $basePath . "includes/config.php";
require_once $basePath . "includes/auth.php";

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location:' . $basePath . 'dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?= $title ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/homepage.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= $stylesheet ?>.css?v=<?= time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Colors */
            --primary-color: #164e63;
            --primary-dark: #0f3a47;
            --primary-light: #22d3ee;
            --secondary-color: #10b981;
            --accent-color: #10b981;
            --danger-color: #ea580c;

            /* Neutrals */
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;

            /* Typography */
            --font-family-heading: "Space Grotesk", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-family-body: "DM Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;

            /* Font sizes */
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            --font-size-4xl: 2.25rem;
            --font-size-5xl: 3rem;
            --font-size-6xl: 3.75rem;

            /* Spacing */
            --spacing-xs: 0.5rem;
            --spacing-sm: 0.75rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            --spacing-3xl: 4rem;
            --spacing-4xl: 6rem;

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);

            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --radius-3xl: 2rem;

            /* Transitions */
            --transition-fast: 0.15s ease-out;
            --transition-normal: 0.3s ease-out;
            --transition-slow: 0.5s ease-out;
        }

        body {
            font-family: var(--font-family-body);
            line-height: 1.6;
            color: var(--gray-600);
            background-color: var(--white);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-family-heading);
            font-weight: 700;
            line-height: 1.2;
            color: var(--gray-900);
            letter-spacing: -0.025em;
        }

        h1 {
            font-size: var(--font-size-5xl);
            font-weight: 800;
        }

        h2 {
            font-size: var(--font-size-4xl);
        }

        h3 {
            font-size: var(--font-size-2xl);
        }

        p {
            line-height: 1.7;
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm) var(--spacing-lg);
            font-family: var(--font-family-body);
            font-size: var(--font-size-sm);
            font-weight: 600;
            text-decoration: none;
            border-radius: var(--radius-xl);
            border: 2px solid transparent;
            cursor: pointer;
            transition: all var(--transition-normal);
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white) !important;
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-2xl);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--gray-700);
            border-color: var(--gray-200);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: var(--white) !important;
            transform: translateY(-1px);
        }

        .btn-large {
            padding: var(--spacing-md) var(--spacing-xl);
            font-size: var(--font-size-base);
        }

        .btn-full {
            width: 100%;
        }

        .btn-arrow {
            transition: transform var(--transition-normal);
        }

        .btn:hover .btn-arrow {
            transform: translateX(4px);
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            z-index: 1000;
            transition: all var(--transition-normal);
        }

        .header.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow-lg);
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-md) var(--spacing-lg);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            font-family: var(--font-family-heading);
            font-size: var(--font-size-xl);
            font-weight: 800;
            color: var(--gray-900);
            text-decoration: none;
        }

        .logo-icon {
            font-size: var(--font-size-2xl);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: var(--spacing-xl);
            list-style: none;
        }

        .nav-links a {
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            transition: all var(--transition-fast);
            position: relative;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width var(--transition-normal);
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .toggle-btn {
            display: none;
            position: relative;
            width: 20px;
            height: 20px;
        }

        .nav-toggle,
        .toggle-close {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            cursor: pointer;
            opacity: 1;
            transition: all .7s ease;
        }

        .nav-toggle span,
        .toggle-close span {
            position: absolute;
            width: 24px;
            height: 2px;
            background: var(--gray-700);
            transition: all var(--transition-fast);
        }

        .nav-toggle span:nth-child(1) {
            top: 0;
        }

        .nav-toggle span:nth-child(2) {
            top: 50%;
            transform: translateY(-50%);
        }

        .nav-toggle span:nth-child(3) {
            top: 100%;
            transform: translateY(-100%);
        }


        .toggle-close {
            opacity: 0;
        }

        .toggle-close span {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
        }

        .toggle-close span:nth-child(1) {
            transform: rotate(45deg);
        }

        .toggle-close span:nth-child(2) {
            transform: rotate(-45deg);
        }

        .toggle-btn.active .toggle-close {
            transform: rotate(360deg);
            opacity: 1;
        }

        .toggle-btn.active .nav-toggle {
            transform: rotate(360deg);
            opacity: 0;
        }

        .btn-group {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-group.mobile {
            display: none;
        }

        @media (max-width: 922px) {
            :root {
                --font-size-5xl: 2.5rem;
                --font-size-4xl: 2rem;
            }

            main {
                padding: 0 !important;
            }

            .btn-group.desktop {
                display: none;
            }

            .btn-group.mobile {
                display: flex;
            }

            .nav-menu {
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                height: 0;
                overflow: hidden;
                transition: height 0.3s ease, padding 0.3s ease;
            }

            .nav-links {
                flex-direction: column;
                align-items: flex-start;
                background-color: #fff;
                padding: 20px;
            }

            .toggle-btn {
                display: flex;
            }
        }

        .main {
            margin-top: 60px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 20px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header" id="header">
        <nav class="nav">
            <a href="index.php" class="logo">
                <span class="logo-icon">🚀</span>
                <?php echo SITE_NAME; ?>
            </a>
            <div class="toggle-btn">
                <div class="nav-toggle" id="navToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="toggle-close" id="toggleClose">
                    <span></span>
                    <span></span>
                </div>
            </div>
            <div class="nav-menu">
                <ul class="nav-links" id="navLinks">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#benefits">Benefits</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#testimonials">Reviews</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <ul class="btn-group mobile">
                        <li><a href="<?= $basePath ?>login" class="btn btn-outline">Sign In</a></li>
                        <li><a href="<?= $basePath ?>signup" class="btn btn-primary">Start Free</a></li>
                    </ul>
                </ul>
            </div>
            <ul class="btn-group desktop">
                <li><a href="<?= $basePath ?>login" class="btn btn-outline">Sign In</a></li>
                <li><a href="<?= $basePath ?>signup" class="btn btn-primary">Start Free</a></li>
            </ul>
        </nav>
    </header>
    <script>
        const navToggle = document.querySelector(".toggle-btn");
        const navMenu = document.querySelector(".nav-menu");

        navToggle.addEventListener("click", () => {
            navToggle.classList.toggle("active");
            if (navMenu.classList.contains("open")) {
                // Closing
                navMenu.style.height = navMenu.scrollHeight + "px"; // set current height
                requestAnimationFrame(() => {
                    navMenu.style.height = "0px";
                });
                navMenu.classList.remove("open");
            } else {
                // Opening
                navMenu.style.height = navMenu.scrollHeight + "px";
                navMenu.classList.add("open");
            }
        });

        navMenu.addEventListener("transitionend", () => {
            if (navMenu.classList.contains("open")) {
                navMenu.style.height = "auto"; // allow dynamic height after opening
            }
        });
    </script>