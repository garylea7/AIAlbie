<?php
defined('ABSPATH') || exit;

class AIAlbieResponsiveEngine {
    private $db;
    private $device_profiles = [];
    private $performance_metrics = [];
    private $voice_patterns = [];
    private $seo_insights = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_responsive_tables();
        add_action('init', [$this, 'load_responsive_data']);
        add_action('template_redirect', [$this, 'optimize_for_device']);
        add_filter('the_content', [$this, 'enhance_voice_seo']);
    }

    private function init_responsive_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // Device Profiles Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_device_profiles (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            device_type varchar(50) NOT NULL,
            screen_specs text NOT NULL,
            performance_requirements text,
            optimization_rules text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY device_type (device_type)
        ) $charset_collate;";

        // Performance Metrics Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_performance_metrics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_url varchar(255) NOT NULL,
            load_time float,
            first_paint float,
            first_contentful_paint float,
            largest_contentful_paint float,
            time_to_interactive float,
            optimization_score int,
            measured_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY page_url (page_url)
        ) $charset_collate;";

        // Voice Patterns Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_voice_patterns (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            pattern_type varchar(50) NOT NULL,
            voice_query text NOT NULL,
            semantic_structure text,
            response_template text,
            success_rate float DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY pattern_type (pattern_type)
        ) $charset_collate;";

        // SEO Insights Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_seo_insights (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_url varchar(255) NOT NULL,
            voice_keywords text,
            semantic_structure text,
            rich_snippets text,
            performance_impact float,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY page_url (page_url)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function optimize_for_device() {
        $device = $this->detect_device();
        $optimization_rules = $this->get_device_rules($device);
        
        // Apply device-specific optimizations
        add_filter('wp_get_attachment_image_attributes', function($attr) use ($device) {
            return $this->optimize_images($attr, $device);
        });

        // Add responsive meta tags
        add_action('wp_head', function() use ($device) {
            $this->add_responsive_meta_tags($device);
        });

        // Optimize scripts and styles
        add_filter('script_loader_tag', [$this, 'optimize_script_loading'], 10, 2);
        add_filter('style_loader_tag', [$this, 'optimize_style_loading'], 10, 2);
    }

    private function detect_device() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $device_data = [
            'type' => $this->get_device_type($user_agent),
            'screen' => $this->get_screen_characteristics(),
            'capabilities' => $this->detect_device_capabilities(),
            'performance' => $this->measure_device_performance()
        ];

        return $this->create_device_profile($device_data);
    }

    private function optimize_images($attr, $device) {
        // Calculate optimal image dimensions
        $dimensions = $this->calculate_optimal_dimensions($attr, $device);
        
        // Generate srcset for responsive images
        $srcset = $this->generate_responsive_srcset($attr['src'], $dimensions);
        
        // Add WebP support with fallback
        $sources = $this->generate_image_sources($attr['src']);

        return array_merge($attr, [
            'srcset' => $srcset,
            'sizes' => $dimensions['sizes'],
            'loading' => 'lazy',
            'data-sources' => json_encode($sources)
        ]);
    }

    public function enhance_voice_seo($content) {
        // Add structured data for voice search
        $structured_data = $this->generate_structured_data($content);
        
        // Enhance content for voice search
        $enhanced_content = $this->optimize_for_voice_search($content);
        
        // Add speech-friendly markers
        $marked_content = $this->add_speech_markers($enhanced_content);
        
        return $marked_content . $structured_data;
    }

    private function optimize_for_voice_search($content) {
        // Extract key phrases for voice search
        $key_phrases = $this->extract_voice_keywords($content);
        
        // Structure content for voice responses
        $voice_structure = $this->create_voice_structure($content);
        
        // Add natural language patterns
        $enhanced_content = $this->add_natural_language($content);
        
        return $this->format_for_voice($enhanced_content, $key_phrases);
    }

    public function measure_performance() {
        $metrics = [
            'load_time' => $this->measure_page_load(),
            'first_paint' => $this->measure_first_paint(),
            'first_contentful_paint' => $this->measure_first_contentful_paint(),
            'largest_contentful_paint' => $this->measure_largest_contentful_paint(),
            'time_to_interactive' => $this->measure_time_to_interactive()
        ];

        $this->store_performance_metrics($metrics);
        return $this->analyze_performance($metrics);
    }

    private function measure_page_load() {
        $start_time = microtime(true);
        register_shutdown_function(function() use ($start_time) {
            $load_time = microtime(true) - $start_time;
            $this->log_performance_metric('page_load', $load_time);
        });
    }

    public function optimize_performance() {
        // Implement lazy loading
        add_filter('wp_get_attachment_image_attributes', function($attr) {
            $attr['loading'] = 'lazy';
            return $attr;
        });

        // Optimize database queries
        add_action('pre_get_posts', [$this, 'optimize_queries']);

        // Cache expensive operations
        add_action('init', function() {
            if (!wp_using_ext_object_cache()) {
                $this->setup_object_cache();
            }
        });

        // Minify HTML output
        ob_start([$this, 'minify_html']);
    }

    public function analyze_voice_patterns($content) {
        $patterns = [
            'questions' => $this->extract_question_patterns($content),
            'commands' => $this->extract_command_patterns($content),
            'conversations' => $this->extract_conversation_patterns($content)
        ];

        return $this->optimize_voice_patterns($patterns);
    }

    private function extract_question_patterns($content) {
        $questions = [];
        preg_match_all('/\b(what|where|when|who|why|how)\b.*\?/i', $content, $matches);
        
        foreach ($matches[0] as $question) {
            $questions[] = [
                'text' => $question,
                'type' => $this->classify_question_type($question),
                'entities' => $this->extract_entities($question),
                'intent' => $this->detect_question_intent($question)
            ];
        }

        return $questions;
    }

    public function generate_voice_schema() {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => [
                    '.voice-enabled',
                    '[data-speakable="true"]'
                ]
            ],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => get_site_url() . '/?s={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }

    public function export_responsive_data() {
        return [
            'device_profiles' => $this->device_profiles,
            'performance_metrics' => $this->performance_metrics,
            'voice_patterns' => $this->voice_patterns,
            'seo_insights' => $this->seo_insights
        ];
    }

    public function import_responsive_data($data) {
        if (!empty($data['device_profiles'])) {
            foreach ($data['device_profiles'] as $profile) {
                $this->store_device_profile($profile);
            }
        }

        if (!empty($data['performance_metrics'])) {
            foreach ($data['performance_metrics'] as $metric) {
                $this->store_performance_metric($metric);
            }
        }

        if (!empty($data['voice_patterns'])) {
            foreach ($data['voice_patterns'] as $pattern) {
                $this->store_voice_pattern($pattern);
            }
        }

        if (!empty($data['seo_insights'])) {
            foreach ($data['seo_insights'] as $insight) {
                $this->store_seo_insight($insight);
            }
        }
    }
}
