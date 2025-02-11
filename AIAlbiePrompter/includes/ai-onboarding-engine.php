<?php
defined('ABSPATH') || exit;

class AIAlbieOnboardingEngine {
    private $db;
    private $onboarding_flows = [];
    private $user_progress = [];
    private $engagement_metrics = [];
    private $personalization = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_onboarding_tables();
        add_action('init', [$this, 'initialize_onboarding']);
        add_action('wp_login', [$this, 'track_user_progress'], 10, 2);
    }

    private function init_onboarding_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // Onboarding Flows Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_onboarding_flows (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            flow_name varchar(50) NOT NULL,
            flow_steps text NOT NULL,
            conditions text,
            active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY flow_name (flow_name)
        ) $charset_collate;";

        // User Progress Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_user_progress (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            flow_id bigint(20) NOT NULL,
            current_step int DEFAULT 0,
            completed_steps text,
            started_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Engagement Metrics Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_engagement_metrics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            metric_type varchar(50) NOT NULL,
            metric_value float,
            context text,
            recorded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Personalization Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_personalization (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            preference_key varchar(50) NOT NULL,
            preference_value text,
            source varchar(50),
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_preference (user_id, preference_key)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function initialize_onboarding() {
        // Add onboarding flows
        $this->register_default_flows();
        
        // Setup progress tracking
        add_action('wp_login', [$this, 'check_user_progress']);
        
        // Initialize personalization
        add_action('template_redirect', [$this, 'personalize_experience']);
        
        // Setup engagement tracking
        add_action('wp_footer', [$this, 'track_engagement']);
    }

    private function register_default_flows() {
        $default_flows = [
            'new_user' => [
                'welcome' => [
                    'title' => 'Welcome to AIAlbie',
                    'content' => 'Let\'s get you started with AI-powered assistance',
                    'action' => 'show_welcome_modal'
                ],
                'profile_setup' => [
                    'title' => 'Set Up Your Profile',
                    'content' => 'Tell us about your preferences',
                    'action' => 'show_profile_setup'
                ],
                'ai_introduction' => [
                    'title' => 'Meet Your AI Assistant',
                    'content' => 'Learn how AIAlbie can help you',
                    'action' => 'show_ai_demo'
                ],
                'feature_tour' => [
                    'title' => 'Discover Key Features',
                    'content' => 'See what you can do with AIAlbie',
                    'action' => 'start_feature_tour'
                ]
            ],
            'advanced_user' => [
                'ai_customization' => [
                    'title' => 'Customize Your AI',
                    'content' => 'Make AIAlbie work better for you',
                    'action' => 'show_customization_options'
                ],
                'workflow_setup' => [
                    'title' => 'Optimize Your Workflow',
                    'content' => 'Set up your preferred working style',
                    'action' => 'configure_workflow'
                ]
            ]
        ];

        foreach ($default_flows as $type => $flow) {
            $this->register_onboarding_flow($type, $flow);
        }
    }

    public function start_onboarding($user_id, $flow_type = 'new_user') {
        // Get appropriate flow
        $flow = $this->get_onboarding_flow($flow_type);
        
        // Create progress record
        $progress_id = $this->create_progress_record($user_id, $flow['id']);
        
        // Start first step
        $this->start_flow_step($progress_id, 0);
        
        return $progress_id;
    }

    public function track_user_progress($user_id, $flow_id) {
        // Get current progress
        $progress = $this->get_user_progress($user_id, $flow_id);
        
        // Update metrics
        $this->update_engagement_metrics($user_id, $progress);
        
        // Check for completion
        if ($this->is_flow_completed($progress)) {
            $this->complete_onboarding($user_id, $flow_id);
        }
    }

    public function personalize_experience() {
        // Get user preferences
        $user_id = get_current_user_id();
        if (!$user_id) return;
        
        $preferences = $this->get_user_preferences($user_id);
        
        // Apply personalizations
        $this->apply_visual_preferences($preferences);
        $this->apply_content_preferences($preferences);
        $this->apply_ai_preferences($preferences);
    }

    private function apply_visual_preferences($preferences) {
        if (isset($preferences['theme'])) {
            add_filter('body_class', function($classes) use ($preferences) {
                $classes[] = 'theme-' . $preferences['theme'];
                return $classes;
            });
        }

        if (isset($preferences['layout'])) {
            add_filter('template_include', function($template) use ($preferences) {
                return $this->get_layout_template($preferences['layout']);
            });
        }
    }

    public function track_engagement() {
        $user_id = get_current_user_id();
        if (!$user_id) return;

        // Track page views
        $this->track_page_view($user_id);
        
        // Track feature usage
        $this->track_feature_usage($user_id);
        
        // Track time on site
        $this->track_session_time($user_id);
        
        // Calculate engagement score
        $this->calculate_engagement_score($user_id);
    }

    private function track_feature_usage($user_id) {
        $features = [
            'ai_chat' => $this->get_chat_usage(),
            'customization' => $this->get_customization_usage(),
            'workflow' => $this->get_workflow_usage()
        ];

        foreach ($features as $feature => $usage) {
            $this->store_feature_metric($user_id, $feature, $usage);
        }
    }

    public function get_user_insights($user_id) {
        return [
            'progress' => $this->get_progress_insights($user_id),
            'engagement' => $this->get_engagement_insights($user_id),
            'preferences' => $this->get_preference_insights($user_id),
            'recommendations' => $this->generate_recommendations($user_id)
        ];
    }

    private function generate_recommendations($user_id) {
        // Analyze user behavior
        $behavior = $this->analyze_user_behavior($user_id);
        
        // Get feature usage
        $usage = $this->get_feature_usage($user_id);
        
        // Generate personalized recommendations
        return $this->create_recommendations($behavior, $usage);
    }

    public function export_onboarding_data() {
        return [
            'onboarding_flows' => $this->onboarding_flows,
            'user_progress' => $this->user_progress,
            'engagement_metrics' => $this->engagement_metrics,
            'personalization' => $this->personalization
        ];
    }

    public function import_onboarding_data($data) {
        if (!empty($data['onboarding_flows'])) {
            foreach ($data['onboarding_flows'] as $flow) {
                $this->store_onboarding_flow($flow);
            }
        }

        if (!empty($data['user_progress'])) {
            foreach ($data['user_progress'] as $progress) {
                $this->store_user_progress($progress);
            }
        }

        if (!empty($data['engagement_metrics'])) {
            foreach ($data['engagement_metrics'] as $metric) {
                $this->store_engagement_metric($metric);
            }
        }

        if (!empty($data['personalization'])) {
            foreach ($data['personalization'] as $pref) {
                $this->store_personalization($pref);
            }
        }
    }
}
