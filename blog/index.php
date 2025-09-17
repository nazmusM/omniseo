<?php 
$title = 'Blog';
$stylesheet = 'blog';
?>

<?php include('../includes/header.php');?>

<div class="main-content">
    <div class="blog-header">
        <h1>OmniSEO Blog</h1>
        <p>Insights, tips, and strategies for content creation, SEO, and AI-powered writing</p>
    </div>

    <div class="blog-container">
        <div class="blog-main">
            <div class="blog-filters">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Search blog posts..." id="blog-search">
                </div>
                
                <div class="category-filter">
                    <select id="category-select">
                        <option value="">All Categories</option>
                        <option value="seo">SEO Strategies</option>
                        <option value="content-creation">Content Creation</option>
                        <option value="ai-writing">AI Writing</option>
                        <option value="digital-marketing">Digital Marketing</option>
                        <option value="tips-tutorials">Tips & Tutorials</option>
                    </select>
                </div>
            </div>

            <div class="blog-grid">
                <!-- Blog Post 1 -->
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="../assets/blog/blog.png" alt="AI Content Creation">
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-meta">
                            <span class="blog-card-category">AI Writing</span>
                            <span class="blog-card-date">📅 September 13, 2025</span>
                        </div>
                        <h2><a href="../blog/how-ai-is-revolutionizing-content-creation/">How AI is Revolutionizing Content Creation in 2025</a></h2>
                        <p class="blog-card-excerpt">Discover how artificial intelligence is transforming the content creation landscape, making it faster, more efficient, and more effective than ever before. Learn about the latest advancements...</p>
                        <div class="blog-card-footer">
                            <span>8 min read</span>
                            <a href="../blog/how-ai-is-revolutionizing-content-creation/" class="read-more">
                                Read more
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 2 -->
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="../assets/blog/blog.png" alt="SEO Trends 2025">
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-meta">
                            <span class="blog-card-category">SEO Strategies</span>
                            <span class="blog-card-date">📅 September 10, 2025</span>
                        </div>
                        <h2><a href="../blog/top-seo-trends-2025/">Top 10 SEO Trends You Can't Ignore in 2025</a></h2>
                        <p class="blog-card-excerpt">Stay ahead of the competition with these essential SEO trends for 2025. From voice search optimization to AI-powered content, learn what strategies will dominate search engine rankings...</p>
                        <div class="blog-card-footer">
                            <span>10 min read</span>
                            <a href="../blog/top-seo-trends-2025/" class="read-more">
                                Read more
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 3 -->
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="../assets/blog/blog.png" alt="Content Marketing Strategy">
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-meta">
                            <span class="blog-card-category">Digital Marketing</span>
                            <span class="blog-card-date">📅 September 5, 2025</span>
                        </div>
                        <h2><a href="../blog/build-content-marketing-strategy/">How to Build a Content Marketing Strategy That Converts</a></h2>
                        <p class="blog-card-excerpt">A solid content marketing strategy is crucial for business growth. Learn how to create a plan that attracts, engages, and converts your target audience while maximizing ROI...</p>
                        <div class="blog-card-footer">
                            <span>12 min read</span>
                            <a href="../blog/build-content-marketing-strategy/" class="read-more">
                                Read more
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 4 -->
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="../assets/blog/blog.png" alt="Keyword Research">
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-meta">
                            <span class="blog-card-category">SEO Strategies</span>
                            <span class="blog-card-date">📅 September 2, 2025</span>
                        </div>
                        <h2><a href="../blog/advanced-keyword-research-techniques/">Advanced Keyword Research Techniques for 2025</a></h2>
                        <p class="blog-card-excerpt">Move beyond basic keyword research with these advanced techniques. Discover how to find untapped opportunities, understand search intent, and create content that ranks for valuable keywords...</p>
                        <div class="blog-card-footer">
                            <span>7 min read</span>
                            <a href="../blog/advanced-keyword-research-techniques/" class="read-more">
                                Read more
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 5 -->
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="../assets/blog/ai-tools.jpg" alt="AI Writing Tools">
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-meta">
                            <span class="blog-card-category">AI Writing</span>
                            <span class="blog-card-date">📅 August 28, 2025</span>
                        </div>
                        <h2><a href="../blog/ai-writing-tools-comparison/">AI Writing Tools Comparison: Which One is Right for You?</a></h2>
                        <p class="blog-card-excerpt">With so many AI writing tools available, it can be challenging to choose the right one. We compare the top options on the market and help you decide which tool best fits your content needs...</p>
                        <div class="blog-card-footer">
                            <span>15 min read</span>
                            <a href="../blog/ai-writing-tools-comparison/" class="read-more">
                                Read more
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Blog Post 6 -->
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="../assets/blog/blog.png" alt="Long Form Content">
                    </div>
                    <div class="blog-card-content">
                        <div class="blog-card-meta">
                            <span class="blog-card-category">Content Creation</span>
                            <span class="blog-card-date">📅 August 25, 2025</span>
                        </div>
                        <h2><a href="../blog/long-form-content-seo-benefits/">The SEO Benefits of Long-Form Content (And How to Do It Right)</a></h2>
                        <p class="blog-card-excerpt">Long-form content continues to dominate search engine results. Learn why comprehensive, in-depth articles perform better and how to create long-form content that engages readers and ranks well...</p>
                        <div class="blog-card-footer">
                            <span>9 min read</span>
                            <a href="../blog/long-form-content-seo-benefits/" class="read-more">
                                Read more
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="pagination-item">
                    <a href="#" class="pagination-link prev-next disabled">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Previous
                    </a>
                </div>
                
                <div class="pagination-item">
                    <a href="#" class="pagination-link page-number active">1</a>
                </div>
                
                <div class="pagination-item">
                    <a href="#" class="pagination-link page-number">2</a>
                </div>
                
                <div class="pagination-item">
                    <a href="#" class="pagination-link page-number">3</a>
                </div>
                
                <div class="pagination-item">
                    <span class="pagination-dots">...</span>
                </div>
                
                <div class="pagination-item">
                    <a href="#" class="pagination-link page-number">8</a>
                </div>
                
                <div class="pagination-item">
                    <a href="#" class="pagination-link prev-next">
                        Next
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="blog-sidebar">
            <div class="sidebar-section">
                <h3>Popular Posts</h3>
                <ul class="popular-posts-list">
                    <li class="popular-post-item">
                        <div class="popular-post-image">
                            <img src="../assets/blog/blog.png alt="AI Content Creation">
                        </div>
                        <div class="popular-post-content">
                            <h4><a href="../blog/how-ai-is-revolutionizing-content-creation/">How AI is Revolutionizing Content Creation</a></h4>
                            <div class="popular-post-meta">Sept 13, 2025</div>
                        </div>
                    </li>
                    <li class="popular-post-item">
                        <div class="popular-post-image">
                            <img src="../assets/blog/blog.png" alt="SEO Trends">
                        </div>
                        <div class="popular-post-content">
                            <h4><a href="../blog/top-seo-trends-2025/">Top 10 SEO Trends for 2025</a></h4>
                            <div class="popular-post-meta">Sept 10, 2025</div>
                        </div>
                    </li>
                    <li class="popular-post-item">
                        <div class="popular-post-image">
                            <img src="../assets/blog/blog.png" alt="Content Marketing">
                        </div>
                        <div class="popular-post-content">
                            <h4><a href="../blog/build-content-marketing-strategy/">Content Marketing Strategy That Converts</a></h4>
                            <div class="popular-post-meta">Sept 5, 2025</div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3>Categories</h3>
                <ul class="categories-list">
                    <li class="category-item">
                        <a href="../blog/category/seo/">SEO Strategies</a>
                        <span class="category-count">12</span>
                    </li>
                    <li class="category-item">
                        <a href="../blog/category/content-creation/">Content Creation</a>
                        <span class="category-count">8</span>
                    </li>
                    <li class="category-item">
                        <a href="../blog/category/ai-writing/">AI Writing</a>
                        <span class="category-count">15</span>
                    </li>
                    <li class="category-item">
                        <a href="../blog/category/digital-marketing/">Digital Marketing</a>
                        <span class="category-count">7</span>
                    </li>
                    <li class="category-item">
                        <a href="../blog/category/tips-tutorials/">Tips & Tutorials</a>
                        <span class="category-count">10</span>
                    </li>
                </ul>
            </div>

            <div class="sidebar-section">
                <h3>Subscribe</h3>
                <p>Get the latest articles and news delivered to your inbox.</p>
                <form class="subscribe-form">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php") ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('blog-search');
        const blogCards = document.querySelectorAll('.blog-card');
        
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            blogCards.forEach(card => {
                const title = card.querySelector('h2').textContent.toLowerCase();
                const excerpt = card.querySelector('.blog-card-excerpt').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || excerpt.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Category filter functionality
        const categorySelect = document.getElementById('category-select');
        
        categorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            
            blogCards.forEach(card => {
                const category = card.querySelector('.blog-card-category').textContent.toLowerCase();
                
                if (!selectedCategory || category.includes(selectedCategory)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>
</body>
</html>