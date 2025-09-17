<?php 
$title = 'About Us';
$stylesheet = 'about';
?>

<?php include('../includes/header.php');?>

<main class="main">
    <div class="about-hero">
        <h1>Revolutionizing Content Creation with AI</h1>
        <p>OmniSEO empowers creators, marketers, and businesses to generate high-quality, SEO-optimized content in minutes, not hours.</p>
    </div>

    <div class="about-section">
        <h2>Our Story</h2>
        <p>Founded in 2023, OmniSEO was born from a simple observation: content creation was becoming increasingly time-consuming and complex in the digital age. While businesses recognized the importance of quality content for SEO and engagement, the resources required were often prohibitive.</p>
        
        <p>Our team of AI researchers, content strategists, and software engineers came together with a mission to democratize content creation through artificial intelligence. We've developed sophisticated algorithms that understand context, tone, and SEO principles to generate content that resonates with both readers and search engines.</p>
        
        <p>Today, thousands of users trust OmniSEO to power their content strategy, from solo bloggers to enterprise marketing teams.</p>
    </div>

    <div class="about-section">
        <h2>Our Mission</h2>
        <div class="mission-grid">
            <div class="mission-card">
                <div class="mission-icon">🚀</div>
                <h3>Accelerate Creation</h3>
                <p>Reduce content production time from hours to minutes without sacrificing quality.</p>
            </div>
            
            <div class="mission-card">
                <div class="mission-icon">🎯</div>
                <h3>Enhance SEO</h3>
                <p>Generate content that ranks well by incorporating SEO best practices naturally.</p>
            </div>
            
            <div class="mission-card">
                <div class="mission-icon">💡</div>
                <h3>Spark Creativity</h3>
                <p>Overcome writer's block with AI-powered suggestions and content ideas.</p>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2>Our Technology</h2>
        <p>OmniSEO leverages cutting-edge natural language processing and generation models trained on vast datasets of high-quality content. Our proprietary algorithms ensure that generated content is:</p>
        
        <ul>
            <li><strong>Original and unique</strong> - Every piece of content is generated fresh, not copied</li>
            <li><strong>SEO-optimized</strong> - Built with search engine ranking factors in mind</li>
            <li><strong>Tone-adaptive</strong> - Matches your brand voice and style requirements</li>
            <li><strong>Fact-aware</strong> - Incorporates relevant information while avoiding misinformation</li>
        </ul>
        
        <p>We continuously improve our models based on user feedback and the evolving landscape of content creation and SEO.</p>
    </div>

    <div class="about-section">
        <h2>Our Team</h2>
        <p>OmniSEO is built by a diverse team of experts in artificial intelligence, computational linguistics, content marketing, and software engineering. We're united by our passion for solving complex problems and making advanced technology accessible to everyone.</p>
        
        <div class="team-grid">
            <div class="team-member">
                <img src="../assets/team/ceo.jpg" alt="Sarah Chen - CEO & Founder">
                <h3>Sarah Chen</h3>
                <p>CEO & Founder</p>
            </div>
            
            <div class="team-member">
                <img src="../assets/team/cto.jpg" alt="Michael Rodriguez - CTO">
                <h3>Michael Rodriguez</h3>
                <p>Chief Technology Officer</p>
            </div>
            
            <div class="team-member">
                <img src="../assets/team/cmo.jpg" alt="Jessica Williams - CMO">
                <h3>Jessica Williams</h3>
                <p>Chief Marketing Officer</p>
            </div>
            
            <div class="team-member">
                <img src="../assets/team/lead-ai.jpg" alt="David Kim - Lead AI Researcher">
                <h3>David Kim</h3>
                <p>Lead AI Researcher</p>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2>By The Numbers</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Active Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">2M+</div>
                <div class="stat-label">Articles Generated</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">99.9%</div>
                <div class="stat-label">Uptime</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">4.8/5</div>
                <div class="stat-label">Customer Rating</div>
            </div>
        </div>
    </div>

    <div class="cta-section">
        <h2>Ready to Transform Your Content Strategy?</h2>
        <p>Join thousands of creators who are already using OmniSEO to save time and elevate their content.</p>
        <a href="../register/" class="cta-button">Get Started Free</a>
    </div>
</main>

<?php include("../includes/footer.php") ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab functionality for legal pages
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        if (tabButtons.length > 0) {
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabId = button.getAttribute('data-tab');
                    
                    // Update active tab button
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    
                    // Show active tab content
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                        if (content.id === tabId) {
                            content.classList.add('active');
                        }
                    });
                });
            });
        }
    });
</script>
</body>
</html>