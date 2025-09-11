<style>
    @media (max-width: 1024px) {
        .footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-xl);
        }
    }

    @media (max-width: 768px) {
        .footer-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3><?php echo SITE_NAME; ?></h3>
                <p>AI-powered SEO content generation for marketers, bloggers, and businesses.</p>
                <div class="social-links">
                    <a href="#" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" role="img" aria-label="Twitter">
                            <title>Twitter</title>
                            <path fill="#1DA1F2" d="M23.954 4.569c-.885.392-1.83.656-2.825.775 
    1.014-.611 1.794-1.574 2.163-2.724-.951.555-2.005.959-3.127 
    1.184-.897-.959-2.178-1.555-3.594-1.555-2.717 
    0-4.924 2.206-4.924 4.924 0 .39.045.765.127 
    1.124-4.09-.205-7.719-2.165-10.148-5.144-.424.729-.666 
    1.577-.666 2.475 0 1.708.87 3.216 2.188 
    4.099-.807-.026-1.566-.248-2.229-.616v.062c0 
    2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.317 
    0-.626-.03-.928-.086.627 1.956 2.444 
    3.379 4.6 3.419-1.68 1.319-3.809 
    2.105-6.102 2.105-.397 0-.788-.023-1.175-.069 
    2.179 1.397 4.768 2.212 7.557 2.212 
    9.054 0 14-7.496 14-13.986 0-.21 
    0-.423-.015-.634.961-.689 1.8-1.56 2.46-2.548z" />
                        </svg>

                    </a>
                    <a href="#" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" role="img" aria-label="Facebook">
                            <title>Facebook</title>
                            <circle cx="12" cy="12" r="12" fill="#1877F2" />
                            <path d="M15.5 8.5h-1.5c-.4 0-.9.3-.9.9v1.1h2.4l-.3 2.1h-2.1V19h-2.3v-5.4H9.6v-2.1h1.2V9.7c0-1.2.7-3 3-3 .7 0 1.4.1 1.7.2v1.6z" fill="#fff" />
                        </svg>

                    </a>
                    <a href="#" aria-label="LinkedIn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" role="img" aria-label="LinkedIn">
                            <title>LinkedIn</title>
                            <path fill="#0A66C2" d="M20.447 20.452H16.86v-5.569c0-1.328-.025-3.039-1.85-3.039-1.853 
    0-2.136 1.445-2.136 2.939v5.669H9.29V9h3.426v1.561h.049c.477-.9 
    1.637-1.85 3.372-1.85 3.605 0 4.271 2.373 
    4.271 5.459v6.282zM5.337 7.433c-1.106 
    0-2.003-.897-2.003-2.003 0-1.105.897-2.003 
    2.003-2.003 1.105 0 2.003.898 2.003 
    2.003 0 1.106-.898 2.003-2.003 2.003zM7.119 
    20.452H3.554V9h3.565v11.452zM22.225 
    0H1.771C.792 0 0 .774 0 1.729v20.542C0 
    23.227.792 24 1.771 24h20.451C23.2 
    24 24 23.227 24 22.271V1.729C24 
    .774 23.2 0 22.225 0z" />
                        </svg>

                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" role="img" aria-label="Instagram">
                            <title>Instagram</title>
                            <defs>
                                <linearGradient id="igGrad" x1="0" x2="1" y1="0" y2="1">
                                    <stop offset="0" stop-color="#F58529" />
                                    <stop offset="0.5" stop-color="#DD2A7B" />
                                    <stop offset="1" stop-color="#8134AF" />
                                </linearGradient>
                            </defs>
                            <rect x="0" y="0" width="24" height="24" rx="5" fill="url(#igGrad)" />
                            <path d="M12 7.2A4.8 4.8 0 1 0 12 16.8 4.8 4.8 0 1 0 12 7.2zm0 7.9a3.1 3.1 0 1 1 0-6.2 3.1 3.1 0 0 1 0 6.2zM17.6 6.6a1.08 1.08 0 1 1-2.16 0 1.08 1.08 0 0 1 2.16 0z" fill="#fff" />
                            <path d="M6.8 4h10.4A2.8 2.8 0 0 1 20 6.8v10.4A2.8 2.8 0 0 1 17.2 20H6.8A2.8 2.8 0 0 1 4 17.2V6.8A2.8 2.8 0 0 1 6.8 4m0-1.2A4 4 0 0 0 2.8 6.8v10.4A4 4 0 0 0 6.8 22h10.4a4 4 0 0 0 4-4V6.8a4 4 0 0 0-4-4H6.8z" fill="#fff" opacity="0.9" />
                        </svg>

                    </a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Product</h4>
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#benefits">Benefits</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#testimonials">Reviews</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#">Use Cases</a></li>
                    <li><a href="#">Integrations</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Resources</h4>
                <ul>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Documentation</a></li>
                    <li><a href="#">Tutorials</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p>Powered by AI • Built for Content Creators</p>
        </div>
    </div>
</footer>

<script>
    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.getElementById('header');
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Feature tabs functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Pricing toggle functionality
    const pricingToggle = document.getElementById('pricing-toggle');
    if (pricingToggle) {
        pricingToggle.addEventListener('change', function() {
            const monthlyPrices = document.querySelectorAll('.monthly-price');
            const annualPrices = document.querySelectorAll('.annual-price');

            if (this.checked) {
                monthlyPrices.forEach(price => price.style.display = 'none');
                annualPrices.forEach(price => price.style.display = 'block');
            } else {
                monthlyPrices.forEach(price => price.style.display = 'block');
                annualPrices.forEach(price => price.style.display = 'none');
            }
        });
    }

    // Add loading animation to buttons
    document.querySelectorAll('.btn-primary').forEach(button => {
        button.addEventListener('click', function(e) {
            const innerText = this.innerText;
            if (this.href && this.href.includes('signup')) {
                this.innerHTML = '<span>Loading...</span>';

                setTimeout(() => {
                    this.innerHTML = innerText;
                }, 3000);
            }
        });
    });

document.addEventListener("DOMContentLoaded", () => {
  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add("animate");
          obs.unobserve(entry.target); // run once
        }
      });
    },
    { threshold: 0.2 } // adjust how much of element must be visible
  );

  // Observe all elements with data-animate attribute
  document.querySelectorAll("[data-animate]").forEach(el => observer.observe(el));
});

</script>
</body>

</html>