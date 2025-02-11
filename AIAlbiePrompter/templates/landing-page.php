<?php defined('ABSPATH') || exit; ?>

<div class="aialbie-landing">
    <!-- Hero Section -->
    <div class="hero-section">
        <h1>AI-Powered WordPress Migration Made Simple</h1>
        <p class="hero-subtitle">Transform any website into a WordPress site with just a few clicks</p>
        
        <!-- Quick Start Analysis -->
        <div class="quick-analyzer">
            <h2>Start Your Migration Journey</h2>
            <div class="analyzer-input">
                <input type="text" id="website-url" placeholder="Enter your website URL (e.g., https://historicaviationmilitary.com)" class="url-input">
                <button id="analyze-site" class="analyze-button">
                    <span class="button-text">Analyze My Site</span>
                    <span class="spinner"></span>
                </button>
            </div>
            <p class="input-helper">Or tell us what you want to achieve...</p>
            <textarea id="migration-goal" placeholder="Example: I want to migrate all images and text to a new WordPress site with the same design" class="goal-input"></textarea>
        </div>
    </div>

    <!-- Analysis Results (Initially Hidden) -->
    <div id="analysis-results" class="analysis-section" style="display: none;">
        <h2>Site Analysis Results</h2>
        
        <!-- Overview -->
        <div class="analysis-overview">
            <div class="overview-card platform">
                <span class="card-icon dashicons dashicons-admin-site"></span>
                <h3>Platform Detected</h3>
                <p class="platform-name">Loading...</p>
            </div>
            <div class="overview-card content">
                <span class="card-icon dashicons dashicons-database"></span>
                <h3>Content Overview</h3>
                <ul class="content-stats">
                    <li>Pages: <span class="pages-count">-</span></li>
                    <li>Images: <span class="images-count">-</span></li>
                    <li>Posts: <span class="posts-count">-</span></li>
                </ul>
            </div>
            <div class="overview-card compatibility">
                <span class="card-icon dashicons dashicons-yes-alt"></span>
                <h3>Migration Compatibility</h3>
                <div class="compatibility-score">
                    <div class="score-ring">
                        <svg viewBox="0 0 36 36">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" class="score-circle"/>
                        </svg>
                        <span class="score-number">-%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Migration Path -->
        <div class="migration-path">
            <h3>Recommended Migration Path</h3>
            <div class="path-steps">
                <!-- Steps will be populated by JavaScript -->
            </div>
        </div>

        <!-- Technical Requirements -->
        <div class="technical-requirements">
            <h3>Technical Requirements</h3>
            <div class="requirements-list">
                <!-- Requirements will be populated by JavaScript -->
            </div>
        </div>

        <!-- Migration Options -->
        <div class="migration-options">
            <h3>Available Migration Options</h3>
            <div class="options-grid">
                <div class="option-card automated">
                    <h4>Automated Migration</h4>
                    <ul class="features-list">
                        <li>Full content migration</li>
                        <li>Design preservation</li>
                        <li>SEO maintenance</li>
                        <li>Minimal downtime</li>
                    </ul>
                    <button class="start-migration">Start Automated Migration</button>
                </div>
                <div class="option-card assisted">
                    <h4>Assisted Migration</h4>
                    <ul class="features-list">
                        <li>Expert guidance</li>
                        <li>Custom requirements</li>
                        <li>Technical support</li>
                        <li>Quality assurance</li>
                    </ul>
                    <button class="contact-expert">Contact Migration Expert</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Common Use Cases -->
    <div class="use-cases">
        <h2>What Can AI Albie Do For You?</h2>
        <div class="cases-grid">
            <div class="case-card">
                <span class="case-icon dashicons dashicons-images-alt2"></span>
                <h3>Media Migration</h3>
                <p>Automatically transfer all your images, videos, and media files while preserving quality and metadata</p>
                <button class="try-case" data-case="media">Try Media Migration</button>
            </div>
            <div class="case-card">
                <span class="case-icon dashicons dashicons-editor-paste-text"></span>
                <h3>Content Transfer</h3>
                <p>Seamlessly migrate your posts, pages, and content while maintaining formatting and SEO</p>
                <button class="try-case" data-case="content">Try Content Transfer</button>
            </div>
            <div class="case-card">
                <span class="case-icon dashicons dashicons-art"></span>
                <h3>Design Cloning</h3>
                <p>Recreate your existing design in WordPress while improving performance and responsiveness</p>
                <button class="try-case" data-case="design">Try Design Cloning</button>
            </div>
            <div class="case-card">
                <span class="case-icon dashicons dashicons-store"></span>
                <h3>eCommerce Migration</h3>
                <p>Transfer your online store with products, categories, and customer data intact</p>
                <button class="try-case" data-case="ecommerce">Try Store Migration</button>
            </div>
        </div>
    </div>

    <!-- Success Stories -->
    <div class="success-stories">
        <h2>Success Stories</h2>
        <div class="stories-slider">
            <div class="story-card">
                <img src="<?php echo esc_url(AIALBIE_ASSETS_URL . 'images/case-study-1.jpg'); ?>" alt="Success Story 1" class="story-image">
                <div class="story-content">
                    <h3>Historic Aviation Website</h3>
                    <p>"AI Albie helped us migrate our entire aviation history website to WordPress, preserving all our historical content and improving site performance."</p>
                    <span class="story-stats">
                        <span class="stat">500+ Pages Migrated</span>
                        <span class="stat">2,000+ Images</span>
                        <span class="stat">99.9% Success Rate</span>
                    </span>
                </div>
            </div>
            <!-- More success stories -->
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-grid">
            <div class="faq-item">
                <h3>How long does migration take?</h3>
                <p>Migration time varies based on your site's size and complexity. Our AI analyzes your site and provides an accurate timeline before starting.</p>
            </div>
            <div class="faq-item">
                <h3>Will my site design be preserved?</h3>
                <p>Yes! Our AI carefully analyzes your current design and recreates it in WordPress while maintaining visual consistency.</p>
            </div>
            <div class="faq-item">
                <h3>What about my SEO rankings?</h3>
                <p>We preserve all SEO elements including URLs, meta data, and content structure to maintain your search rankings.</p>
            </div>
            <div class="faq-item">
                <h3>Do I need technical knowledge?</h3>
                <p>No! Our AI-guided process handles the technical details. Just tell us what you want to achieve in plain English.</p>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta-section">
        <h2>Ready to Transform Your Website?</h2>
        <p>Start your migration journey with AI Albie today</p>
        <div class="cta-buttons">
            <button class="analyze-site-cta">Analyze My Site</button>
            <button class="contact-support">Talk to an Expert</button>
        </div>
    </div>
</div>
