<?php
$content = "<section>\n    <h1>The Comprehensive Guide to Modern Marketing Strategies<\/h1>\n    <p>In today's hyper-connected digital landscape, marketing has evolved far beyond traditional advertisements. It is a multifaceted discipline that blends creativity with data, strategy with execution, and brand building with direct response. A successful <strong>marketing strategy<\/strong> is no longer a luxury; it is an essential roadmap for business growth, customer acquisition, and long-term viability. This guide explores the core components that form the backbone of contemporary marketing efforts.<\/p>\n\n    <h2>The Pillars of Digital Marketing<\/h2>\n    <p><strong>Digital marketing<\/strong> encompasses all online efforts to connect with audiences. Its primary components work in synergy to create a powerful online presence.<\/p>\n    <ul>\n        <li><strong>Search Engine Optimization (SEO)<\/strong>: The art and science of improving your website's visibility in organic search results. A strong SEO foundation ensures your brand is found by people actively searching for your solutions.<\/li>\n        <li><strong>Pay-Per-Click (PPC)<\/strong>: The paid counterpart to SEO, PPC allows for immediate visibility on search engines and social platforms. You only pay when a user clicks your ad, making it a highly measurable tactic for <strong>lead generation<\/strong>.<\/li>\n        <li><strong>Email Marketing<\/strong>: A direct and personal channel for nurturing leads and retaining customers. Effective <strong>email marketing<\/strong> delivers valuable content, promotional offers, and builds lasting relationships.<\/li>\n        <li><strong>Social Media Marketing<\/strong>: This involves engaging with your audience on platforms like Facebook, Instagram, LinkedIn, and TikTok. <strong>Social media marketing<\/strong> is crucial for community building, customer service, and amplifying your <strong>content marketing<\/strong> efforts.<\/li>\n    <\/ul>\n\n    <h2>Content and Connection: Building Your Brand<\/h2>\n    <p>At the heart of modern marketing lies valuable content. <strong>Content marketing<\/strong> focuses on creating and distributing relevant articles, videos, podcasts, and infographics to attract and retain a clearly defined audience. This approach establishes your authority, builds trust, and supports every other aspect of your strategy, especially SEO.<\/p>\n    <p>This entire effort contributes to your overall <strong>branding<\/strong>—the perception of your company in the mind of the consumer. Consistent messaging and quality experiences across all touchpoints solidify your brand identity and foster customer loyalty.<\/p>\n\n    <h2>Leveraging Influence and Generating Leads<\/h2>\n    <p>Two of the most potent strategies in a modern marketer's toolkit are:<\/p>\n    <ol>\n        <li><strong>Influencer Marketing<\/strong>: Partnering with individuals who have a dedicated social following and are viewed as experts within their niche. This strategy provides social proof and can dramatically extend your reach to highly engaged communities.<\/li>\n        <li><strong>Lead Generation<\/strong>: The process of attracting and converting strangers into prospects interested in your products or services. This is the ultimate goal of most marketing activities, from PPC campaigns to gated content offers.<\/li>\n    <\/ol>\n\n    <h2>Crafting a Cohesive Marketing Strategy<\/h2>\n    <p>The true power of marketing is realized when these elements are integrated into a unified <strong>marketing strategy<\/strong>. This strategy should define your target audience, set clear goals, allocate resources, and choose the right mix of tactics—from <strong>digital marketing<\/strong> to <strong>influencer marketing<\/strong>—to achieve maximum impact and ROI.<\/p>\n\n    <h2>Conclusion: Marketing as a Growth Engine<\/h2>\n    <p>Marketing is the engine that drives business growth. By understanding and effectively implementing these interconnected strategies—<strong>SEO<\/strong>, <strong>PPC<\/strong>, <strong>content marketing<\/strong>, and beyond—businesses can build a powerful brand, generate a consistent pipeline of qualified leads, and create meaningful connections with their audience that translate into sustainable success.<\/p>\n<\/section>\n\n<!-- Meta Description Suggestion: Explore our comprehensive guide to digital marketing. Learn effective strategies for SEO, PPC, content marketing, social media, email, branding, and lead generation to grow your business. -->\n<!-- Title Tag Suggestion: Modern Marketing Guide: SEO, PPC, Content & Social Media Strategies -->";

function processAiOutput($aiOutput) {
    $metaDescription = '';
    $titleTag = '';

    // Extract meta description suggestion
    if (preg_match('/<!--\s*Meta Description Suggestion:\s*(.*?)\s*-->/is', $aiOutput, $metaMatch)) {
        $metaDescription = trim($metaMatch[1]);
        $aiOutput = str_replace($metaMatch[0], '', $aiOutput);
    }

    // Extract title tag suggestion
    if (preg_match('/<!--\s*Title Tag Suggestion:\s*(.*?)\s*-->/is', $aiOutput, $titleTagMatch)) {
        $titleTag = trim($titleTagMatch[1]);
        $aiOutput = str_replace($titleTagMatch[0], '', $aiOutput);
    }

    // Clean up any remaining HTML comments
    $aiOutput = preg_replace('/<!--.*?-->/s', '', $aiOutput);
    
    // Remove extra whitespace and newlines
    $aiOutput = trim($aiOutput);

    return [
        'content' => $aiOutput,
        'meta_description' => $metaDescription,
        'title_tag' => $titleTag
    ];
}

// Test the function
$result = processAiOutput($content);


// Display as array for better visualization
echo "=== ARRAY OUTPUT ===\n";
print_r($result);
?>