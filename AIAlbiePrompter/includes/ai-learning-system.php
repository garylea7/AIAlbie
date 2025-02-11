<?php
defined('ABSPATH') || exit;

class AIAlbieLearningSystem {
    private $db;
    private $user_profiles = [];
    private $site_patterns = [];
    private $interaction_history = [];
    private $learning_data = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_tables();
        add_action('init', [$this, 'load_learning_data']);
    }

    private function init_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // User Profiles Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_user_profiles (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            site_url varchar(255) NOT NULL,
            site_type varchar(50) NOT NULL,
            hosting_provider varchar(100),
            common_issues text,
            preferences text,
            interaction_patterns text,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Site Patterns Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_site_patterns (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            pattern_type varchar(50) NOT NULL,
            pattern_data text NOT NULL,
            frequency int(11) DEFAULT 1,
            success_rate float DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY pattern_type (pattern_type)
        ) $charset_collate;";

        // Interaction History Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_interactions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            query text NOT NULL,
            response text NOT NULL,
            success_rating int(1),
            context text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Learning Data Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_learning_data (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            data_type varchar(50) NOT NULL,
            data_key varchar(255) NOT NULL,
            data_value text NOT NULL,
            confidence float DEFAULT 0,
            last_used datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY data_key (data_type, data_key)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function load_learning_data() {
        $this->user_profiles = $this->get_user_profiles();
        $this->site_patterns = $this->get_site_patterns();
        $this->interaction_history = $this->get_recent_interactions();
        $this->learning_data = $this->get_learning_data();
    }

    public function analyze_user_site($user_id, $site_url) {
        $site_info = $this->get_site_info($site_url);
        $this->update_user_profile($user_id, $site_info);
        return $this->get_personalized_recommendations($user_id);
    }

    private function get_site_info($site_url) {
        $site_info = [
            'url' => $site_url,
            'type' => $this->detect_site_type($site_url),
            'hosting' => $this->detect_hosting_provider($site_url),
            'plugins' => $this->analyze_plugins($site_url),
            'theme' => $this->analyze_theme($site_url),
            'performance' => $this->analyze_performance($site_url),
            'security' => $this->analyze_security($site_url)
        ];

        return $site_info;
    }

    private function detect_site_type($site_url) {
        $response = wp_remote_get($site_url);
        if (is_wp_error($response)) return 'unknown';

        $body = wp_remote_retrieve_body($response);
        $headers = wp_remote_retrieve_headers($response);

        // Analyze page content and structure
        $type_indicators = [
            'ecommerce' => ['woocommerce', 'shop', 'cart', 'product'],
            'blog' => ['post', 'article', 'blog', 'author'],
            'portfolio' => ['portfolio', 'gallery', 'work', 'project'],
            'business' => ['service', 'about', 'contact', 'company']
        ];

        $scores = [];
        foreach ($type_indicators as $type => $indicators) {
            $score = 0;
            foreach ($indicators as $indicator) {
                if (stripos($body, $indicator) !== false) {
                    $score++;
                }
            }
            $scores[$type] = $score;
        }

        return array_search(max($scores), $scores) ?: 'general';
    }

    private function detect_hosting_provider($site_url) {
        $response = wp_remote_get($site_url);
        if (is_wp_error($response)) return 'unknown';

        $headers = wp_remote_retrieve_headers($response);
        $server = $headers['server'] ?? '';

        $hosting_signatures = [
            'cloudways' => ['cloudways'],
            'wpengine' => ['wpengine'],
            'siteground' => ['siteground'],
            'bluehost' => ['bluehost'],
            'godaddy' => ['godaddy'],
            'hostgator' => ['hostgator']
        ];

        foreach ($hosting_signatures as $provider => $signatures) {
            foreach ($signatures as $signature) {
                if (stripos($server, $signature) !== false) {
                    return $provider;
                }
            }
        }

        return $this->guess_hosting_provider($site_url);
    }

    private function analyze_plugins($site_url) {
        // Detect active plugins through HTML analysis
        $response = wp_remote_get($site_url);
        if (is_wp_error($response)) return [];

        $body = wp_remote_retrieve_body($response);
        
        $known_plugins = [
            'woocommerce' => ['woocommerce', 'wc-'],
            'yoast' => ['yoast-seo', 'yoast'],
            'elementor' => ['elementor'],
            'contact-form-7' => ['wpcf7'],
            'wordfence' => ['wordfence']
        ];

        $detected_plugins = [];
        foreach ($known_plugins as $plugin => $signatures) {
            foreach ($signatures as $signature) {
                if (stripos($body, $signature) !== false) {
                    $detected_plugins[] = $plugin;
                    break;
                }
            }
        }

        return array_unique($detected_plugins);
    }

    public function learn_from_interaction($user_id, $query, $response, $success_rating) {
        // Store interaction
        $this->store_interaction($user_id, $query, $response, $success_rating);

        // Update pattern confidence
        $this->update_pattern_confidence($query, $response, $success_rating);

        // Learn from successful interactions
        if ($success_rating >= 4) {
            $this->extract_patterns($query, $response);
        }

        // Update user profile
        $this->update_user_preferences($user_id, $query, $success_rating);
    }

    private function store_interaction($user_id, $query, $response, $success_rating) {
        $this->db->insert(
            $this->db->prefix . 'aialbie_interactions',
            [
                'user_id' => $user_id,
                'query' => $query,
                'response' => $response,
                'success_rating' => $success_rating,
                'context' => json_encode($this->get_interaction_context())
            ]
        );
    }

    private function extract_patterns($query, $response) {
        // Extract common patterns from successful interactions
        $patterns = [
            'query_type' => $this->classify_query($query),
            'response_structure' => $this->analyze_response_structure($response),
            'context_requirements' => $this->extract_context_requirements($query, $response)
        ];

        $this->store_pattern($patterns);
    }

    private function update_pattern_confidence($query, $response, $success_rating) {
        $pattern_type = $this->classify_query($query);
        $existing_pattern = $this->get_pattern($pattern_type);

        if ($existing_pattern) {
            $new_confidence = ($existing_pattern->confidence * $existing_pattern->frequency + $success_rating) / ($existing_pattern->frequency + 1);
            $this->update_pattern_stats($pattern_type, $new_confidence);
        }
    }

    public function get_personalized_response($user_id, $query) {
        $user_profile = $this->get_user_profile($user_id);
        $similar_cases = $this->find_similar_cases($query);
        $site_specific_data = $this->get_site_specific_data($user_profile['site_url']);

        return $this->generate_response([
            'query' => $query,
            'user_profile' => $user_profile,
            'similar_cases' => $similar_cases,
            'site_data' => $site_specific_data
        ]);
    }

    private function find_similar_cases($query) {
        $query_type = $this->classify_query($query);
        $similar_interactions = $this->db->get_results($this->db->prepare(
            "SELECT * FROM {$this->db->prefix}aialbie_interactions 
            WHERE success_rating >= 4 
            AND query_type = %s 
            ORDER BY created_at DESC 
            LIMIT 5",
            $query_type
        ));

        return array_map(function($interaction) {
            return [
                'query' => $interaction->query,
                'response' => $interaction->response,
                'success_rating' => $interaction->success_rating,
                'context' => json_decode($interaction->context, true)
            ];
        }, $similar_interactions);
    }

    public function update_learning_data($data_type, $data) {
        $existing = $this->db->get_row($this->db->prepare(
            "SELECT * FROM {$this->db->prefix}aialbie_learning_data 
            WHERE data_type = %s AND data_key = %s",
            $data_type,
            $data['key']
        ));

        if ($existing) {
            $this->db->update(
                $this->db->prefix . 'aialbie_learning_data',
                [
                    'data_value' => json_encode($data['value']),
                    'confidence' => $data['confidence'],
                    'last_used' => current_time('mysql')
                ],
                [
                    'data_type' => $data_type,
                    'data_key' => $data['key']
                ]
            );
        } else {
            $this->db->insert(
                $this->db->prefix . 'aialbie_learning_data',
                [
                    'data_type' => $data_type,
                    'data_key' => $data['key'],
                    'data_value' => json_encode($data['value']),
                    'confidence' => $data['confidence'],
                    'last_used' => current_time('mysql')
                ]
            );
        }
    }

    public function get_site_recommendations($site_url) {
        $site_info = $this->get_site_info($site_url);
        $similar_sites = $this->find_similar_sites($site_info);
        
        return [
            'common_issues' => $this->get_common_issues($similar_sites),
            'best_practices' => $this->get_best_practices($site_info['type']),
            'security_recommendations' => $this->get_security_recommendations($site_info),
            'performance_tips' => $this->get_performance_tips($site_info)
        ];
    }

    private function find_similar_sites($site_info) {
        return $this->db->get_results($this->db->prepare(
            "SELECT * FROM {$this->db->prefix}aialbie_user_profiles 
            WHERE site_type = %s 
            AND hosting_provider = %s 
            LIMIT 10",
            $site_info['type'],
            $site_info['hosting']
        ));
    }

    public function export_learning_data() {
        return [
            'user_profiles' => $this->user_profiles,
            'site_patterns' => $this->site_patterns,
            'learning_data' => $this->learning_data
        ];
    }

    public function import_learning_data($data) {
        if (isset($data['user_profiles'])) {
            foreach ($data['user_profiles'] as $profile) {
                $this->update_user_profile($profile['user_id'], $profile);
            }
        }

        if (isset($data['site_patterns'])) {
            foreach ($data['site_patterns'] as $pattern) {
                $this->store_pattern($pattern);
            }
        }

        if (isset($data['learning_data'])) {
            foreach ($data['learning_data'] as $item) {
                $this->update_learning_data($item['type'], $item['data']);
            }
        }
    }
}
