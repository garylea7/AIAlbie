<?php
defined('ABSPATH') || exit;

class AIAlbieCustomizationEngine {
    private $db;
    private $customization_data = [];
    private $personality_profiles = [];
    private $learning_preferences = [];
    private $interaction_styles = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_customization_tables();
        add_action('init', [$this, 'initialize_customization']);
        add_action('wp_ajax_process_customization', [$this, 'handle_customization']);
    }

    private function init_customization_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // Personality Profiles Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_personality_profiles (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            profile_name varchar(100) NOT NULL,
            traits text NOT NULL,
            behavior_patterns text,
            communication_style text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY profile_name (profile_name)
        ) $charset_collate;";

        // Learning Preferences Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_learning_preferences (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            learning_style text NOT NULL,
            pace_preferences text,
            feedback_style text,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Interaction Styles Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_interaction_styles (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            style_name varchar(50) NOT NULL,
            interaction_rules text NOT NULL,
            response_patterns text,
            context_handling text,
            PRIMARY KEY  (id),
            KEY style_name (style_name)
        ) $charset_collate;";

        // Customization Settings Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_customization_settings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            agent_id bigint(20) NOT NULL,
            setting_type varchar(50) NOT NULL,
            setting_value text,
            active_status boolean DEFAULT true,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY agent_id (agent_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function initialize_customization() {
        // Initialize personality system
        $this->init_personality_system();
        
        // Setup learning preferences
        $this->init_learning_preferences();
        
        // Initialize interaction styles
        $this->init_interaction_styles();
        
        // Setup customization settings
        $this->init_customization_settings();
    }

    private function init_personality_system() {
        // Load base personalities
        $this->load_base_personalities();
        
        // Setup trait mapping
        $this->setup_trait_mapping();
        
        // Initialize behavior patterns
        $this->init_behavior_patterns();
    }

    public function create_personality_profile($profile_data) {
        // Validate profile data
        if (!$this->validate_profile_data($profile_data)) {
            return new WP_Error('invalid_profile', 'Invalid personality profile data');
        }

        // Process traits
        $processed_traits = $this->process_personality_traits($profile_data['traits']);
        
        // Generate behavior patterns
        $behavior_patterns = $this->generate_behavior_patterns($processed_traits);
        
        // Create profile
        $profile_id = $this->db->insert(
            $this->db->prefix . 'aialbie_personality_profiles',
            [
                'profile_name' => $profile_data['name'],
                'traits' => json_encode($processed_traits),
                'behavior_patterns' => json_encode($behavior_patterns),
                'communication_style' => json_encode($profile_data['communication_style'])
            ]
        );

        return $profile_id;
    }

    private function process_personality_traits($traits) {
        // Analyze traits
        $analyzed_traits = $this->analyze_traits($traits);
        
        // Balance traits
        $balanced_traits = $this->balance_traits($analyzed_traits);
        
        // Generate combinations
        $combinations = $this->generate_trait_combinations($balanced_traits);
        
        return $combinations;
    }

    public function customize_learning_preferences($user_id, $preferences) {
        // Validate preferences
        if (!$this->validate_learning_preferences($preferences)) {
            return new WP_Error('invalid_preferences', 'Invalid learning preferences');
        }

        // Process preferences
        $processed_preferences = $this->process_learning_preferences($preferences);
        
        // Apply preferences
        $this->apply_learning_preferences($user_id, $processed_preferences);
        
        return $processed_preferences;
    }

    private function process_learning_preferences($preferences) {
        // Analyze style
        $learning_style = $this->analyze_learning_style($preferences);
        
        // Determine pace
        $pace = $this->determine_learning_pace($preferences);
        
        // Configure feedback
        $feedback = $this->configure_feedback_style($preferences);
        
        return [
            'style' => $learning_style,
            'pace' => $pace,
            'feedback' => $feedback
        ];
    }

    public function create_interaction_style($style_data) {
        // Validate style data
        if (!$this->validate_style_data($style_data)) {
            return new WP_Error('invalid_style', 'Invalid interaction style data');
        }

        // Process rules
        $processed_rules = $this->process_interaction_rules($style_data['rules']);
        
        // Generate patterns
        $patterns = $this->generate_response_patterns($processed_rules);
        
        // Create style
        $style_id = $this->db->insert(
            $this->db->prefix . 'aialbie_interaction_styles',
            [
                'style_name' => $style_data['name'],
                'interaction_rules' => json_encode($processed_rules),
                'response_patterns' => json_encode($patterns),
                'context_handling' => json_encode($style_data['context_handling'])
            ]
        );

        return $style_id;
    }

    private function process_interaction_rules($rules) {
        // Analyze rules
        $analyzed_rules = $this->analyze_rules($rules);
        
        // Validate consistency
        $consistent_rules = $this->validate_rule_consistency($analyzed_rules);
        
        // Generate patterns
        $patterns = $this->generate_rule_patterns($consistent_rules);
        
        return $patterns;
    }

    public function apply_customization($agent_id, $customization_data) {
        // Get current settings
        $current_settings = $this->get_agent_settings($agent_id);
        
        // Merge changes
        $merged_settings = $this->merge_customization_changes($current_settings, $customization_data);
        
        // Validate changes
        if (!$this->validate_customization_changes($merged_settings)) {
            return new WP_Error('invalid_changes', 'Invalid customization changes');
        }

        // Apply changes
        return $this->update_agent_settings($agent_id, $merged_settings);
    }

    private function merge_customization_changes($current, $new) {
        // Merge personality
        $personality = $this->merge_personality_changes($current['personality'], $new['personality']);
        
        // Merge learning
        $learning = $this->merge_learning_changes($current['learning'], $new['learning']);
        
        // Merge interaction
        $interaction = $this->merge_interaction_changes($current['interaction'], $new['interaction']);
        
        return [
            'personality' => $personality,
            'learning' => $learning,
            'interaction' => $interaction
        ];
    }

    public function analyze_customization_impact() {
        // Get customization data
        $customization_data = $this->get_customization_data();
        
        // Analyze impact
        $impact = $this->analyze_impact($customization_data);
        
        // Generate insights
        $insights = $this->generate_customization_insights($impact);
        
        return [
            'impact' => $impact,
            'insights' => $insights,
            'recommendations' => $this->generate_customization_recommendations($insights)
        ];
    }

    private function analyze_impact($data) {
        return [
            'personality' => $this->analyze_personality_impact($data),
            'learning' => $this->analyze_learning_impact($data),
            'interaction' => $this->analyze_interaction_impact($data),
            'overall' => $this->analyze_overall_impact($data)
        ];
    }

    public function export_customization_data() {
        return [
            'customization_data' => $this->customization_data,
            'personality_profiles' => $this->personality_profiles,
            'learning_preferences' => $this->learning_preferences,
            'interaction_styles' => $this->interaction_styles
        ];
    }

    public function import_customization_data($data) {
        if (!empty($data['customization_data'])) {
            foreach ($data['customization_data'] as $customization) {
                $this->store_customization($customization);
            }
        }

        if (!empty($data['personality_profiles'])) {
            foreach ($data['personality_profiles'] as $profile) {
                $this->store_personality_profile($profile);
            }
        }

        if (!empty($data['learning_preferences'])) {
            foreach ($data['learning_preferences'] as $preference) {
                $this->store_learning_preference($preference);
            }
        }

        if (!empty($data['interaction_styles'])) {
            foreach ($data['interaction_styles'] as $style) {
                $this->store_interaction_style($style);
            }
        }
    }
}
