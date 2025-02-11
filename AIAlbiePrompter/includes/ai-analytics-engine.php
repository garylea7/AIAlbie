<?php
defined('ABSPATH') || exit;

class AIAlbieAnalyticsEngine {
    private $db;
    private $tracking_data = [];
    private $user_insights = [];
    private $conversion_data = [];
    private $behavior_patterns = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_analytics_tables();
        add_action('init', [$this, 'initialize_analytics']);
        add_action('wp_footer', [$this, 'track_user_behavior']);
    }

    private function init_analytics_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // Tracking Data Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_tracking_data (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20),
            event_type varchar(50) NOT NULL,
            event_data text NOT NULL,
            page_url varchar(255),
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // User Insights Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_user_insights (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            insight_type varchar(50) NOT NULL,
            insight_data text NOT NULL,
            confidence float DEFAULT 0,
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Conversion Data Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_conversion_data (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20),
            conversion_type varchar(50) NOT NULL,
            conversion_value float,
            source varchar(50),
            converted_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Behavior Patterns Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_behavior_patterns (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            pattern_type varchar(50) NOT NULL,
            pattern_data text NOT NULL,
            frequency int DEFAULT 0,
            last_seen datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY pattern_type (pattern_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function initialize_analytics() {
        // Setup tracking
        add_action('wp_enqueue_scripts', [$this, 'enqueue_tracking_scripts']);
        
        // Initialize user tracking
        add_action('wp_login', [$this, 'track_user_session']);
        
        // Setup conversion tracking
        add_action('template_redirect', [$this, 'track_conversions']);
        
        // Initialize behavior analysis
        add_action('shutdown', [$this, 'analyze_behavior']);
    }

    public function enqueue_tracking_scripts() {
        wp_enqueue_script('analytics-tracking',
            plugins_url('assets/js/analytics-tracking.js', dirname(__FILE__)),
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script('analytics-tracking', 'analyticsConfig', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('analytics-tracking')
        ]);
    }

    public function track_user_behavior() {
        $user_id = get_current_user_id();
        
        // Track page view
        $this->track_event('page_view', [
            'url' => $_SERVER['REQUEST_URI'],
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);

        // Track user interactions
        add_action('wp_ajax_track_interaction', [$this, 'track_user_interaction']);
        
        // Track AI usage
        add_action('ai_interaction', [$this, 'track_ai_usage']);
    }

    public function track_user_interaction() {
        check_ajax_referer('analytics-tracking', 'nonce');

        $interaction = [
            'type' => $_POST['type'],
            'target' => $_POST['target'],
            'value' => $_POST['value'],
            'timestamp' => current_time('mysql')
        ];

        $this->store_interaction($interaction);
        wp_send_json_success();
    }

    public function track_ai_usage($data) {
        $usage = [
            'feature' => $data['feature'],
            'duration' => $data['duration'],
            'success' => $data['success'],
            'feedback' => $data['feedback'] ?? null
        ];

        $this->store_ai_usage($usage);
    }

    public function track_conversions() {
        // Track goal completions
        add_action('goal_completed', [$this, 'track_goal']);
        
        // Track purchases
        add_action('woocommerce_order_status_completed', [$this, 'track_purchase']);
        
        // Track form submissions
        add_action('gform_after_submission', [$this, 'track_form_submission']);
    }

    public function analyze_behavior() {
        // Get recent behavior data
        $data = $this->get_recent_behavior_data();
        
        // Identify patterns
        $patterns = $this->identify_patterns($data);
        
        // Generate insights
        $insights = $this->generate_insights($patterns);
        
        // Store results
        $this->store_analysis_results($insights);
    }

    private function identify_patterns($data) {
        return [
            'navigation' => $this->analyze_navigation_patterns($data),
            'interaction' => $this->analyze_interaction_patterns($data),
            'conversion' => $this->analyze_conversion_patterns($data),
            'engagement' => $this->analyze_engagement_patterns($data)
        ];
    }

    public function generate_reports() {
        return [
            'user_behavior' => $this->generate_behavior_report(),
            'conversions' => $this->generate_conversion_report(),
            'engagement' => $this->generate_engagement_report(),
            'ai_usage' => $this->generate_ai_usage_report()
        ];
    }

    private function generate_behavior_report() {
        // Get behavior data
        $data = $this->get_behavior_data();
        
        // Analyze patterns
        $patterns = $this->analyze_patterns($data);
        
        // Generate visualizations
        $visualizations = $this->create_visualizations($patterns);
        
        return [
            'data' => $data,
            'patterns' => $patterns,
            'visualizations' => $visualizations
        ];
    }

    public function export_analytics_data() {
        return [
            'tracking_data' => $this->tracking_data,
            'user_insights' => $this->user_insights,
            'conversion_data' => $this->conversion_data,
            'behavior_patterns' => $this->behavior_patterns
        ];
    }

    public function import_analytics_data($data) {
        if (!empty($data['tracking_data'])) {
            foreach ($data['tracking_data'] as $tracking) {
                $this->store_tracking_data($tracking);
            }
        }

        if (!empty($data['user_insights'])) {
            foreach ($data['user_insights'] as $insight) {
                $this->store_user_insight($insight);
            }
        }

        if (!empty($data['conversion_data'])) {
            foreach ($data['conversion_data'] as $conversion) {
                $this->store_conversion_data($conversion);
            }
        }

        if (!empty($data['behavior_patterns'])) {
            foreach ($data['behavior_patterns'] as $pattern) {
                $this->store_behavior_pattern($pattern);
            }
        }
    }
}
