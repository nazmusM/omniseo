<?php 
$title = 'Support Center - OmniSEO';
$stylesheet = 'support';
?>

<?php include('../includes/header.php');?>

<main class="main">
    <div class="support-header">
        <h1>OmniSEO Support Center</h1>
        <p>Find answers, guides, and resources to help you get the most out of OmniSEO</p>
    </div>

    <div class="search-section">
        <h2>How can we help you today?</h2>
        <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Search for answers..." id="support-search">
        </div>
    </div>

    <div class="help-categories">
        <div class="help-card">
            <div class="help-icon">📚</div>
            <h3>Documentation & Guides</h3>
            <p>Comprehensive guides and tutorials to help you master OmniSEO's features</p>
            <a href="../documentation/" class="help-link">View Documentation →</a>
        </div>
        
        <div class="help-card">
            <div class="help-icon">🎥</div>
            <h3>Video Tutorials</h3>
            <p>Watch step-by-step video guides to learn how to use OmniSEO effectively</p>
            <a href="../tutorials/" class="help-link">Watch Tutorials →</a>
        </div>
        
        <div class="help-card">
            <div class="help-icon">❓</div>
            <h3>FAQs</h3>
            <p>Find answers to frequently asked questions about OmniSEO</p>
            <a href="#faqs" class="help-link">View FAQs →</a>
        </div>
    </div>

    <div class="faq-section">
        <h2>Frequently Asked Questions</h2>
        
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">How do I get started with OmniSEO?</div>
                <div class="faq-answer">
                    <p>Getting started with OmniSEO is easy! After creating your account, you can:</p>
                    <ol>
                        <li>Navigate to the Article Generator from your dashboard</li>
                        <li>Choose between Single Article or Bulk Generator</li>
                        <li>Enter your topic, keywords, and any specific instructions</li>
                        <li>Configure your settings (tone, length, language, etc.)</li>
                        <li>Click "Generate" and wait for your content to be created</li>
                    </ol>
                    <p>Check out our <a href="../documentation/">documentation</a> for detailed guides.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">What's the difference between credits and subscriptions?</div>
                <div class="faq-answer">
                    <p>OmniSEO offers two purchasing options:</p>
                    <ul>
                        <li><strong>Credits:</strong> One-time purchase that never expires. Perfect for occasional users or those who want flexibility.</li>
                        <li><strong>Subscriptions:</strong> Monthly plans that provide a set number of credits each month, often at a discounted rate. Ideal for regular users.</li>
                    </ul>
                    <p>You can view and manage your credits/subscription from your account dashboard.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">How does the AI ensure content quality and originality?</div>
                <div class="faq-answer">
                    <p>OmniSEO uses advanced AI models trained on diverse, high-quality datasets to generate original content. Our system:</p>
                    <ul>
                        <li>Creates content from scratch based on your inputs</li>
                        <li>Incorporates SEO best practices naturally</li>
                        <li>Includes built-in plagiarism checks</li>
                        <li>Allows for customization of tone, style, and structure</li>
                    </ul>
                    <p>While we strive for high quality, we always recommend reviewing and editing generated content to ensure it meets your specific needs.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Can I request a refund for unused credits?</div>
                <div class="faq-answer">
                    <p>We offer refunds on unused credits within 30 days of purchase, provided the credits haven't been used. Subscription payments are refundable within the first 14 days of a new subscription or renewal.</p>
                    <p>To request a refund, please contact our support team with your account details and reason for the refund request.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">How do I improve my content's SEO with OmniSEO?</div>
                <div class="faq-answer">
                    <p>OmniSEO includes several features to help optimize your content for search engines:</p>
                    <ul>
                        <li>Natural keyword integration throughout your content</li>
                        <li>Proper heading structure (H1, H2, H3 tags)</li>
                        <li>Meta description generation</li>
                        <li>Readability optimization</li>
                        <li>Semantic SEO with related terms and concepts</li>
                    </ul>
                    <p>For best results, provide specific keywords and consider enabling the "Include Meta Description" option in your settings.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="contact-options">
        <div class="contact-option">
            <div class="contact-icon">📧</div>
            <h3>Email Support</h3>
            <p>Send us a detailed message and we'll respond within 24 hours</p>
            <a href="../contact/" class="contact-button">Contact Us</a>
        </div>
        
        <div class="contact-option">
            <div class="contact-icon">💬</div>
            <h3>Live Chat</h3>
            <p>Chat with our support team in real-time during business hours</p>
            <a href="#" class="contact-button" id="live-chat-button">Start Chat</a>
        </div>
        
        <div class="contact-option">
            <div class="contact-icon">📞</div>
            <h3>Phone Support</h3>
            <p>Speak directly with our support team for urgent issues</p>
            <a href="tel:+11234567890" class="contact-button">Call Now</a>
        </div>
    </div>

    <div class="status-section">
        <h2>System Status</h2>
        <div class="system-status">
            <div class="status-indicator">
                <div class="status-dot operational"></div>
                <span class="status-text">All Systems Operational</span>
            </div>
            <span class="status-check">Last checked: <?php echo date('M j, Y g:i A'); ?></span>
        </div>
    </div>

    <div class="community-section">
        <h2>Join Our Community</h2>
        <p>Connect with other OmniSEO users, share tips, and get help from the community</p>
        
        <div class="community-links">
            <a href="https://facebook.com/groups/omniseo" class="community-link" target="_blank">
                <span class="community-icon">👥</span>
                <span>Facebook Group</span>
            </a>
            
            <a href="https://discord.gg/omniseo" class="community-link" target="_blank">
                <span class="community-icon">💬</span>
                <span>Discord Community</span>
            </a>
            
            <a href="https://twitter.com/omniseo" class="community-link" target="_blank">
                <span class="community-icon">🐦</span>
                <span>Twitter Updates</span>
            </a>
            
            <a href="../blog/" class="community-link">
                <span class="community-icon">📝</span>
                <span>Blog & Tips</span>
            </a>
        </div>
    </div>
</main>

<?php include("../includes/footer.php") ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // FAQ accordion functionality
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', () => {
                // Close all other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
            });
        });
        
        // Support search functionality
        const searchInput = document.getElementById('support-search');
        const faqQuestions = document.querySelectorAll('.faq-question');
        
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            if (searchTerm.length > 2) {
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question');
                    const answer = item.querySelector('.faq-answer');
                    const questionText = question.textContent.toLowerCase();
                    const answerText = answer.textContent.toLowerCase();
                    
                    if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                        item.style.display = 'block';
                        item.classList.add('active'); // Expand matching FAQs
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                faqItems.forEach(item => {
                    item.style.display = 'block';
                    item.classList.remove('active');
                });
            }
        });
        
        // Live chat button handler
        const liveChatButton = document.getElementById('live-chat-button');
        
        liveChatButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Check if live chat is available (during business hours)
            const now = new Date();
            const hour = now.getHours();
            const isWeekday = now.getDay() >= 1 && now.getDay() <= 5;
            const isBusinessHours = isWeekday && hour >= 9 && hour < 18;
            
            if (isBusinessHours) {
                alert('Live chat is available! Connecting you with a support agent...');
                // In a real implementation, this would open your chat widget
            } else {
                alert('Our live chat is currently offline. Business hours are Monday-Friday, 9AM-6PM EST. Please email us or check our documentation for immediate help.');
            }
        });
    });
</script>
</body>
</html>