<?php
function processAiOutput($aiOutput) {
    $metaDescription = '';
    $title = '';

    // More flexible regex patterns
    $titlePattern = '/<p><strong>Suggested Title Tag:<\/strong>\s*(.*?)<\/p>/i';
    $metaPattern  = '/<p><strong>Suggested Meta Description:<\/strong>\s*(.*?)<\/p>/i';

    // Extract title
    if (preg_match($titlePattern, $aiOutput, $titleMatch)) {
        $title = trim(strip_tags($titleMatch[1]));
        $aiOutput = str_replace($titleMatch[0], '', $aiOutput);
    }

    // Extract meta description
    if (preg_match($metaPattern, $aiOutput, $metaMatch)) {
        $metaDescription = trim(strip_tags($metaMatch[1]));
        $aiOutput = str_replace($metaMatch[0], '', $aiOutput);
    }

    // Clean empty <p> tags & trim
    $aiOutput = preg_replace('/<p>\s*<\/p>/', '', $aiOutput);
    $aiOutput = trim($aiOutput);

    return [
        'content' => $aiOutput,
        'meta_description' => $metaDescription,
        'title' => $title
    ];
}

// Example usage
$aiOutput = "<img src='206.189.54.28' alt=''\/><h1>The Essential Guide to Marketing: Connecting Businesses with Audiences<\/h1>\n\n<p>Marketing is the lifeblood of any successful organization. It is far more than just advertising or selling; it is a comprehensive discipline focused on understanding customer needs and delivering value. At its core, marketing is about building relationships, creating brand awareness, and driving sustainable business growth. In today's hyper-connected digital landscape, effective marketing strategies are indispensable for reaching target audiences and achieving competitive advantage.<\/p>\n\n<h2>What is Marketing?<\/h2>\n<p>Marketing encompasses all the activities a company undertakes to promote the buying, selling, and use of its products or services. It involves a strategic process of researching, promoting, selling, and distributing offerings to consumers. The American Marketing Association defines it as \"the activity, set of institutions, and processes for creating, communicating, delivering, and exchanging offerings that have value for customers, clients, partners, and society at large.\"<\/p>\n\n<h2>The Core Components of a Marketing Strategy<\/h2>\n<p>A robust marketing strategy is built on several foundational elements, often conceptualized through frameworks like the 4 Ps of marketing:<\/p>\n<ul>\n  <li><strong>Product:<\/strong> Developing goods or services that meet market needs.<\/li>\n  <li><strong>Price:<\/strong> Establishing a pricing strategy that reflects value and market position.<\/li>\n  <li><strong>Place:<\/strong> Ensuring products are available where and when customers want them.<\/li>\n  <li><strong>Promotion:<\/strong> Communicating the value proposition through advertising, PR, and sales.<\/li>\n<\/ul>\n\n<h2>Modern Marketing Channels and Techniques<\/h2>\n<p>The evolution of technology has dramatically expanded the marketing toolkit. Today's marketers leverage a blend of traditional and digital channels to maximize reach and engagement.<\/p>\n<ol>\n  <li><strong>Digital Marketing:<\/strong> Includes SEO, content marketing, social media, email campaigns, and PPC advertising.<\/li>\n  <li><strong>Content Marketing:<\/strong> Creating valuable, relevant content to attract and retain a defined audience.<\/li>\n  <li><strong>Social Media Marketing:<\/strong> Engaging with communities on platforms like Facebook, Instagram, and LinkedIn.<\/li>\n  <li><strong>Data-Driven Marketing:<\/strong> Utilizing analytics and customer data to personalize experiences and optimize campaigns.<\/li>\n<\/ol>\n\n<h2>The Importance of a Customer-Centric Approach<\/h2>\n<p>Successful modern marketing is inherently customer-centric. It shifts the focus from simply pushing products to building meaningful, long-term relationships. This involves:<\/p>\n<ul>\n  <li>Deeply understanding customer pain points and desires.<\/li>\n  <li>Creating personalized experiences across all touchpoints.<\/li>\n  <li>Continuously gathering and acting on customer feedback.<\/li>\n<\/ul>\n<blockquote>\"The aim of marketing is to know and understand the customer so well the product or service fits them and sells itself.\" - Peter Drucker<\/blockquote>\n\n<h2>Conclusion: The Future of Marketing<\/h2>\n<p>Marketing is a dynamic and ever-evolving field. As consumer behaviors shift and new technologies emerge, the strategies must adapt. The future of marketing lies in hyper-personalization, ethical data use, and authentic brand storytelling. By mastering the fundamental principles while embracing innovation, businesses can craft powerful marketing campaigns that resonate deeply, build loyalty, and drive enduring success.<\/p>\n\n\n<p><strong>Suggested Title Tag:<\/strong> Marketing Essentials: Strategies, Channels &amp; Best Practices | Guide<\/p>\n<p><strong>Suggested Meta Description:<\/strong> Explore our comprehensive guide to marketing. Learn core strategies, modern digital channels, and why a customer-centric approach is key to business growth and brand success.<\/p>";
$result = processAiOutput($aiOutput);

echo "Title: " . $result['title'] . "\n\n";
echo "Meta Description: " . $result['meta_description'] . "\n\n";
echo "Content: " . $result['content'];
?>
