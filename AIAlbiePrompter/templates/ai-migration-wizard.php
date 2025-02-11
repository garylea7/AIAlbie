<?php
// Initialize our secure components
$security = new AIAlbieSecurityManager();
$config = new AIAlbieConfigManager();
$audit = new AIAlbieAuditManager();

// Initialize migration components
$template_analyzer = new AIAlbieTemplateAnalyzer();
$content_optimizer = new AIAlbieContentOptimizer();
$block_converter = new AIAlbieBlockConverter();
$layout_analyzer = new AIAlbieLayoutAnalyzer();
$template_manager = new AIAlbieTemplateManager();

class AIAlbieMigrationWizard {
    private $security;
    private $config;
    private $audit;
    private $template_analyzer;
    private $content_optimizer;
    private $block_converter;
    private $layout_analyzer;
    private $template_manager;
    
    public function __construct() {
        global $security, $config, $audit, $template_analyzer, 
               $content_optimizer, $block_converter, $layout_analyzer, $template_manager;
        
        $this->security = $security;
        $this->config = $config;
        $this->audit = $audit;
        $this->template_analyzer = $template_analyzer;
        $this->content_optimizer = $content_optimizer;
        $this->block_converter = $block_converter;
        $this->layout_analyzer = $layout_analyzer;
        $this->template_manager = $template_manager;
    }

    /**
     * Start migration process
     */
    public function start_migration($url, $user_intent) {
        try {
            // Log migration start
            $this->audit->log_security_event('migration_start', [
                'url' => $url,
                'intent' => $user_intent
            ]);

            // Step 1: Analyze site
            $analysis = $this->analyze_site($url, $user_intent);
            
            // Step 2: Get recommendations
            $recommendations = $this->get_recommendations($analysis);
            
            // Step 3: Show preview
            $preview = $this->generate_preview($recommendations);
            
            return [
                'success' => true,
                'analysis' => $analysis,
                'recommendations' => $recommendations,
                'preview' => $preview
            ];
            
        } catch (Exception $e) {
            $this->audit->log_security_event('migration_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Analyze site content and structure
     */
    private function analyze_site($url, $user_intent) {
        // Analyze content types and structure
        $content_analysis = $this->layout_analyzer->analyze_content($url);
        
        // Analyze templates
        $template_analysis = $this->template_analyzer->analyze_site($url, $user_intent);
        
        // Get optimization suggestions
        $optimization = $this->content_optimizer->analyze_content($url);
        
        return [
            'content' => $content_analysis,
            'templates' => $template_analysis,
            'optimization' => $optimization
        ];
    }

    /**
     * Get smart recommendations
     */
    private function get_recommendations($analysis) {
        // Get template recommendations
        $templates = $this->template_manager->get_templates();
        
        // Score each template
        $scores = [];
        foreach ($templates as $id => $template) {
            $scores[$id] = $this->score_template($template, $analysis);
        }
        
        // Sort by score
        arsort($scores);
        
        // Get top recommendations
        $recommendations = [];
        foreach (array_slice($scores, 0, 3, true) as $id => $score) {
            $recommendations[] = [
                'template' => $templates[$id],
                'score' => $score,
                'reasons' => $this->get_recommendation_reasons($templates[$id], $analysis)
            ];
        }
        
        return $recommendations;
    }

    /**
     * Score template match
     */
    private function score_template($template, $analysis) {
        $score = 0;
        
        // Check content type match
        if (isset($analysis['content']['type'])) {
            if ($template['features']['content_type'] === $analysis['content']['type']) {
                $score += 10;
            }
        }
        
        // Check layout match
        if (isset($analysis['content']['layout'])) {
            if (in_array($analysis['content']['layout'], $template['layouts'])) {
                $score += 5;
            }
        }
        
        // Check feature match
        foreach ($template['features'] as $feature => $supported) {
            if ($supported && isset($analysis['content']['features'][$feature])) {
                $score += 2;
            }
        }
        
        return $score;
    }

    /**
     * Get recommendation reasons
     */
    private function get_recommendation_reasons($template, $analysis) {
        $reasons = [];
        
        // Content type match
        if ($template['features']['content_type'] === $analysis['content']['type']) {
            $reasons[] = "Perfect match for your {$analysis['content']['type']} content";
        }
        
        // Layout match
        if (in_array($analysis['content']['layout'], $template['layouts'])) {
            $reasons[] = "Supports your preferred {$analysis['content']['layout']} layout";
        }
        
        // Feature matches
        foreach ($template['features'] as $feature => $supported) {
            if ($supported && isset($analysis['content']['features'][$feature])) {
                $reasons[] = "Includes {$feature} support";
            }
        }
        
        return $reasons;
    }

    /**
     * Generate live preview
     */
    private function generate_preview($recommendations) {
        $previews = [];
        
        foreach ($recommendations as $rec) {
            $previews[] = [
                'template' => $rec['template']['id'],
                'preview' => $this->template_manager->preview_template(
                    $rec['template']['id']
                )
            ];
        }
        
        return $previews;
    }

    /**
     * Apply migration
     */
    public function apply_migration($template_id, $options) {
        try {
            // Log migration application
            $this->audit->log_security_event('migration_apply', [
                'template' => $template_id,
                'options' => $options
            ]);

            // Step 1: Apply template
            $template = $this->template_manager->apply_template($template_id, $options);
            
            // Step 2: Convert content to blocks
            $blocks = $this->block_converter->convert_to_blocks($options['content']);
            
            // Step 3: Apply optimizations
            $optimized = $this->content_optimizer->optimize_content($blocks);
            
            // Step 4: Create pages
            $pages = $this->create_wordpress_pages($optimized);
            
            return [
                'success' => true,
                'template' => $template,
                'pages' => $pages
            ];
            
        } catch (Exception $e) {
            $this->audit->log_security_event('migration_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create WordPress pages
     */
    private function create_wordpress_pages($content) {
        $pages = [];
        
        foreach ($content as $page) {
            $post_id = wp_insert_post([
                'post_title' => $page['title'],
                'post_content' => $page['content'],
                'post_status' => 'draft',
                'post_type' => 'page'
            ]);
            
            if ($post_id) {
                $pages[] = [
                    'id' => $post_id,
                    'title' => $page['title'],
                    'url' => get_preview_post_link($post_id)
                ];
            }
        }
        
        return $pages;
    }
}

// Create wizard instance
$wizard = new AIAlbieMigrationWizard();

// Handle AJAX requests
add_action('wp_ajax_aialbie_start_migration', function() {
    global $wizard;
    
    $url = $_POST['url'];
    $intent = $_POST['intent'];
    
    wp_send_json($wizard->start_migration($url, $intent));
});

add_action('wp_ajax_aialbie_apply_migration', function() {
    global $wizard;
    
    $template_id = $_POST['template_id'];
    $options = $_POST['options'];
    
    wp_send_json($wizard->apply_migration($template_id, $options));
});
?>

<div class="ai-migration-wizard">
    <!-- Step 1: Natural Language Input -->
    <div class="wizard-step" data-step="1">
        <div class="step-header">
            <h2>Tell Me About Your Website</h2>
            <p>Describe what you want to achieve in your own words</p>
        </div>
        
        <div class="ai-chat-input">
            <textarea id="userIntent" placeholder="For example: 'I want to convert my old HTML website about historic aviation into a modern WordPress site. Keep the same content but make it look more professional.'"></textarea>
            <button class="analyze-button">
                <span class="icon">🔍</span> Analyze My Needs
            </button>
        </div>

        <!-- AI is thinking animation -->
        <div class="ai-thinking" style="display: none;">
            <div class="thinking-animation">
                <span></span><span></span><span></span>
            </div>
            <p>Analyzing your website and requirements...</p>
        </div>
    </div>

    <!-- Step 2: AI Suggestions -->
    <div class="wizard-step" data-step="2" style="display: none;">
        <div class="step-header">
            <h2>Here's What I Recommend</h2>
            <p>Based on your website's content and needs</p>
        </div>

        <!-- Template Recommendations -->
        <div class="template-recommendations">
            <div class="recommendation-grid">
                <?php foreach ($wizard->get_recommendations($wizard->analyze_site('https://example.com', 'example intent')) as $rec): ?>
                <div class="template-card" data-template-id="<?php echo esc_attr($rec['template']['id']); ?>">
                    <div class="template-preview">
                        <img src="<?php echo esc_url($rec['template']['preview_image']); ?>" alt="<?php echo esc_attr($rec['template']['name']); ?>">
                        <div class="preview-overlay">
                            <button class="live-preview-btn">Live Preview</button>
                        </div>
                    </div>
                    <div class="template-info">
                        <h3><?php echo esc_html($rec['template']['name']); ?></h3>
                        <p><?php echo esc_html($rec['template']['description']); ?></p>
                        <div class="ai-confidence">
                            <span class="confidence-label">AI Confidence Match:</span>
                            <div class="confidence-bar">
                                <div class="confidence-fill" style="width: <?php echo esc_attr($rec['score']); ?>%"></div>
                            </div>
                            <span class="confidence-percent"><?php echo esc_attr($rec['score']); ?>%</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Content Migration Options -->
        <div class="migration-options">
            <h3>Content Migration Options</h3>
            <div class="option-cards">
                <div class="option-card">
                    <div class="option-icon">🔄</div>
                    <h4>Smart Content Transfer</h4>
                    <p>AI will preserve your content structure while adapting it to WordPress blocks</p>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="option-card">
                    <div class="option-icon">🎨</div>
                    <h4>Style Migration</h4>
                    <p>Keep your brand colors and styling preferences</p>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="option-card">
                    <div class="option-icon">🔍</div>
                    <h4>SEO Preservation</h4>
                    <p>Maintain your SEO rankings and metadata</p>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Preview & Customize -->
    <div class="wizard-step" data-step="3" style="display: none;">
        <div class="step-header">
            <h2>Preview & Customize</h2>
            <p>See how your site will look and make adjustments</p>
        </div>

        <div class="preview-container">
            <div class="preview-toolbar">
                <div class="device-switcher">
                    <button data-device="desktop" class="active">
                        <span class="dashicons dashicons-desktop"></span>
                    </button>
                    <button data-device="tablet">
                        <span class="dashicons dashicons-tablet"></span>
                    </button>
                    <button data-device="mobile">
                        <span class="dashicons dashicons-smartphone"></span>
                    </button>
                </div>
                <div class="preview-actions">
                    <button class="customize-btn">
                        <span class="dashicons dashicons-admin-customizer"></span> Customize
                    </button>
                    <button class="proceed-btn">
                        <span class="dashicons dashicons-yes"></span> Looks Good
                    </button>
                </div>
            </div>
            <div class="preview-frame">
                <iframe id="previewFrame" src="about:blank"></iframe>
            </div>
        </div>
    </div>
</div>

<style>
.ai-migration-wizard {
    max-width: 1200px;
    margin: 20px auto;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
}

.wizard-step {
    padding: 20px;
}

.step-header {
    text-align: center;
    margin-bottom: 30px;
}

.step-header h2 {
    font-size: 24px;
    color: #1e1e1e;
    margin-bottom: 10px;
}

.step-header p {
    color: #666;
    font-size: 16px;
}

/* AI Chat Input */
.ai-chat-input {
    max-width: 800px;
    margin: 0 auto;
}

.ai-chat-input textarea {
    width: 100%;
    height: 120px;
    padding: 15px;
    border: 2px solid #ddd;
    border-radius: 10px;
    font-size: 16px;
    resize: none;
    margin-bottom: 15px;
}

.ai-chat-input textarea:focus {
    outline: none;
    border-color: #0073aa;
}

.analyze-button {
    background: #0073aa;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 auto;
}

/* AI Thinking Animation */
.ai-thinking {
    text-align: center;
    margin: 20px 0;
}

.thinking-animation {
    display: flex;
    justify-content: center;
    gap: 6px;
}

.thinking-animation span {
    width: 8px;
    height: 8px;
    background: #0073aa;
    border-radius: 50%;
    animation: thinking 1.4s infinite;
}

.thinking-animation span:nth-child(2) {
    animation-delay: 0.2s;
}

.thinking-animation span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes thinking {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Template Grid */
.recommendation-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.template-card {
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s;
}

.template-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.template-preview {
    position: relative;
    padding-top: 66.67%;
}

.template-preview img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.template-card:hover .preview-overlay {
    opacity: 1;
}

.live-preview-btn {
    background: white;
    color: #1e1e1e;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.template-info {
    padding: 15px;
}

.ai-confidence {
    margin-top: 10px;
}

.confidence-bar {
    height: 6px;
    background: #eee;
    border-radius: 3px;
    margin: 5px 0;
}

.confidence-fill {
    height: 100%;
    background: #0073aa;
    border-radius: 3px;
    transition: width 0.3s;
}

/* Migration Options */
.option-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.option-card {
    padding: 20px;
    border: 1px solid #eee;
    border-radius: 8px;
    text-align: center;
}

.option-icon {
    font-size: 24px;
    margin-bottom: 10px;
}

/* Preview Section */
.preview-container {
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
}

.preview-toolbar {
    padding: 10px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.device-switcher {
    display: flex;
    gap: 10px;
}

.device-switcher button {
    background: none;
    border: none;
    padding: 5px;
    cursor: pointer;
    color: #666;
}

.device-switcher button.active {
    color: #0073aa;
}

.preview-frame {
    height: 600px;
}

.preview-frame iframe {
    width: 100%;
    height: 100%;
    border: none;
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #0073aa;
}

input:checked + .slider:before {
    transform: translateX(26px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wizard = document.querySelector('.ai-migration-wizard');
    const analyzeButton = document.querySelector('.analyze-button');
    const aiThinking = document.querySelector('.ai-thinking');
    
    // Step 1: Process user input
    analyzeButton.addEventListener('click', async () => {
        const userIntent = document.getElementById('userIntent').value;
        
        // Show thinking animation
        aiThinking.style.display = 'block';
        analyzeButton.disabled = true;
        
        try {
            // Process user intent with AI
            const analysis = await processUserIntent(userIntent);
            
            // Update recommendations based on analysis
            updateRecommendations(analysis);
            
            // Move to next step
            showStep(2);
        } catch (error) {
            console.error('Error processing intent:', error);
        } finally {
            aiThinking.style.display = 'none';
            analyzeButton.disabled = false;
        }
    });
    
    // Template preview
    document.querySelectorAll('.live-preview-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const templateId = e.target.closest('.template-card').dataset.templateId;
            showPreview(templateId);
        });
    });
    
    // Device preview switching
    document.querySelectorAll('.device-switcher button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.device-switcher button').forEach(b => 
                b.classList.remove('active'));
            btn.classList.add('active');
            updatePreviewSize(btn.dataset.device);
        });
    });
    
    // Helper functions
    async function processUserIntent(intent) {
        // AI processing would happen here
        return new Promise(resolve => {
            setTimeout(() => {
                resolve({
                    recommendedTemplates: ['template1', 'template2'],
                    contentStructure: 'hierarchical',
                    stylePreferences: {
                        colors: ['#336699', '#ffffff'],
                        fonts: ['Arial', 'Georgia']
                    }
                });
            }, 2000);
        });
    }
    
    function updateRecommendations(analysis) {
        // Update UI based on AI analysis
    }
    
    function showStep(stepNumber) {
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.style.display = 'none';
        });
        document.querySelector(`[data-step="${stepNumber}"]`).style.display = 'block';
    }
    
    function showPreview(templateId) {
        showStep(3);
        // Load preview content
    }
    
    function updatePreviewSize(device) {
        const frame = document.querySelector('.preview-frame');
        switch(device) {
            case 'mobile':
                frame.style.maxWidth = '375px';
                break;
            case 'tablet':
                frame.style.maxWidth = '768px';
                break;
            default:
                frame.style.maxWidth = '100%';
        }
    }
});
</script>
