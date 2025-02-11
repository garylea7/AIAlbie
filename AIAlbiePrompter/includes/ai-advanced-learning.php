<?php
defined('ABSPATH') || exit;

class AIAlbieAdvancedLearning {
    private $db;
    private $neural_patterns = [];
    private $behavior_clusters = [];
    private $site_fingerprints = [];
    private $user_insights = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_advanced_tables();
        add_action('init', [$this, 'load_advanced_data']);
    }

    private function init_advanced_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // Neural Patterns Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_neural_patterns (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            pattern_hash varchar(64) NOT NULL,
            input_vector text NOT NULL,
            output_vector text NOT NULL,
            context_vector text NOT NULL,
            weight float DEFAULT 1.0,
            success_count int DEFAULT 0,
            fail_count int DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY pattern_hash (pattern_hash)
        ) $charset_collate;";

        // Behavior Clusters Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_behavior_clusters (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            cluster_name varchar(100) NOT NULL,
            behavior_pattern text NOT NULL,
            user_count int DEFAULT 0,
            success_rate float DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY cluster_name (cluster_name)
        ) $charset_collate;";

        // Site Fingerprints Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_site_fingerprints (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            site_url varchar(255) NOT NULL,
            fingerprint_data text NOT NULL,
            tech_stack text,
            performance_metrics text,
            security_profile text,
            update_frequency varchar(50),
            last_scanned datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY site_url (site_url)
        ) $charset_collate;";

        // User Insights Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_user_insights (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            expertise_level varchar(50),
            learning_style varchar(50),
            communication_preference varchar(50),
            problem_patterns text,
            success_patterns text,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function analyze_user_behavior($user_id, $interactions) {
        $behavior_vector = $this->create_behavior_vector($interactions);
        $cluster = $this->assign_behavior_cluster($behavior_vector);
        $this->update_user_insights($user_id, $cluster);
        return $this->get_personalized_strategy($user_id, $cluster);
    }

    private function create_behavior_vector($interactions) {
        $vector = [
            'query_complexity' => $this->analyze_query_complexity($interactions),
            'technical_level' => $this->assess_technical_level($interactions),
            'learning_pattern' => $this->detect_learning_pattern($interactions),
            'problem_solving_style' => $this->analyze_problem_solving($interactions),
            'communication_style' => $this->analyze_communication($interactions)
        ];

        return $this->normalize_vector($vector);
    }

    private function analyze_query_complexity($interactions) {
        $complexity_scores = [];
        foreach ($interactions as $interaction) {
            $score = 0;
            // Analyze technical terms
            $score += $this->count_technical_terms($interaction['query']) * 0.5;
            // Analyze query structure
            $score += $this->analyze_query_structure($interaction['query']) * 0.3;
            // Consider context requirements
            $score += $this->analyze_context_requirements($interaction['query']) * 0.2;
            
            $complexity_scores[] = $score;
        }
        
        return array_sum($complexity_scores) / count($complexity_scores);
    }

    private function assess_technical_level($interactions) {
        $technical_indicators = [
            'basic' => ['login', 'password', 'forgot', 'help', 'how to'],
            'intermediate' => ['plugin', 'theme', 'customize', 'settings', 'backup'],
            'advanced' => ['code', 'database', 'API', 'optimize', 'security']
        ];

        $scores = ['basic' => 0, 'intermediate' => 0, 'advanced' => 0];
        
        foreach ($interactions as $interaction) {
            foreach ($technical_indicators as $level => $terms) {
                foreach ($terms as $term) {
                    if (stripos($interaction['query'], $term) !== false) {
                        $scores[$level]++;
                    }
                }
            }
        }

        return $this->calculate_expertise_level($scores);
    }

    private function detect_learning_pattern($interactions) {
        $patterns = [
            'sequential' => 0,
            'global' => 0,
            'active' => 0,
            'reflective' => 0
        ];

        $previous_topics = [];
        foreach ($interactions as $i => $interaction) {
            // Sequential vs Global
            if ($i > 0) {
                $topic_similarity = $this->calculate_topic_similarity(
                    $interaction['query'],
                    $interactions[$i - 1]['query']
                );
                if ($topic_similarity > 0.7) {
                    $patterns['sequential']++;
                } else {
                    $patterns['global']++;
                }
            }

            // Active vs Reflective
            if (strpos($interaction['query'], '?') !== false) {
                $patterns['reflective']++;
            }
            if (strpos(strtolower($interaction['query']), 'try') !== false ||
                strpos(strtolower($interaction['query']), 'do') !== false) {
                $patterns['active']++;
            }
        }

        return $this->determine_dominant_pattern($patterns);
    }

    public function create_site_fingerprint($site_url) {
        $fingerprint = [
            'technology' => $this->analyze_technology_stack($site_url),
            'performance' => $this->measure_performance($site_url),
            'security' => $this->assess_security($site_url),
            'content' => $this->analyze_content($site_url),
            'updates' => $this->track_update_patterns($site_url)
        ];

        $this->store_site_fingerprint($site_url, $fingerprint);
        return $fingerprint;
    }

    private function analyze_technology_stack($site_url) {
        $stack = [
            'server' => $this->detect_server_technology($site_url),
            'cms_version' => $this->get_wordpress_version($site_url),
            'plugins' => $this->detect_active_plugins($site_url),
            'theme' => $this->analyze_theme_technology($site_url),
            'apis' => $this->detect_active_apis($site_url)
        ];

        return $this->enrich_stack_data($stack);
    }

    private function measure_performance($site_url) {
        return [
            'load_time' => $this->measure_load_time($site_url),
            'ttfb' => $this->measure_ttfb($site_url),
            'resource_usage' => $this->analyze_resource_usage($site_url),
            'optimization_score' => $this->calculate_optimization_score($site_url)
        ];
    }

    public function learn_from_success($interaction_data) {
        $pattern = $this->extract_success_pattern($interaction_data);
        $this->reinforce_neural_pattern($pattern);
        $this->update_behavior_clusters($pattern);
        $this->adapt_response_strategies($pattern);
    }

    private function extract_success_pattern($interaction_data) {
        return [
            'input_features' => $this->extract_input_features($interaction_data),
            'context_features' => $this->extract_context_features($interaction_data),
            'solution_features' => $this->extract_solution_features($interaction_data),
            'success_metrics' => $this->calculate_success_metrics($interaction_data)
        ];
    }

    private function reinforce_neural_pattern($pattern) {
        $pattern_hash = $this->generate_pattern_hash($pattern);
        $existing_pattern = $this->get_neural_pattern($pattern_hash);

        if ($existing_pattern) {
            $this->update_pattern_weights($pattern_hash, $pattern);
        } else {
            $this->store_new_pattern($pattern);
        }
    }

    public function get_personalized_insights($user_id) {
        $user_data = $this->get_user_insights($user_id);
        $behavior_cluster = $this->get_user_cluster($user_id);
        $site_data = $this->get_user_site_data($user_id);

        return [
            'expertise' => $this->analyze_expertise_progression($user_data),
            'learning_recommendations' => $this->generate_learning_recommendations($user_data),
            'common_patterns' => $this->identify_common_patterns($user_data),
            'success_strategies' => $this->extract_success_strategies($user_data),
            'site_specific_insights' => $this->generate_site_insights($site_data)
        ];
    }

    private function analyze_expertise_progression($user_data) {
        $timeline = [];
        foreach ($user_data['interactions'] as $interaction) {
            $expertise_markers = $this->extract_expertise_markers($interaction);
            $timeline[] = [
                'timestamp' => $interaction['timestamp'],
                'expertise_level' => $expertise_markers['level'],
                'confidence' => $expertise_markers['confidence']
            ];
        }

        return $this->calculate_progression_trend($timeline);
    }

    public function export_advanced_learning_data() {
        return [
            'neural_patterns' => $this->neural_patterns,
            'behavior_clusters' => $this->behavior_clusters,
            'site_fingerprints' => $this->site_fingerprints,
            'user_insights' => $this->user_insights
        ];
    }

    public function import_advanced_learning_data($data) {
        if (!empty($data['neural_patterns'])) {
            foreach ($data['neural_patterns'] as $pattern) {
                $this->store_neural_pattern($pattern);
            }
        }

        if (!empty($data['behavior_clusters'])) {
            foreach ($data['behavior_clusters'] as $cluster) {
                $this->store_behavior_cluster($cluster);
            }
        }

        if (!empty($data['site_fingerprints'])) {
            foreach ($data['site_fingerprints'] as $fingerprint) {
                $this->store_site_fingerprint($fingerprint['url'], $fingerprint['data']);
            }
        }

        if (!empty($data['user_insights'])) {
            foreach ($data['user_insights'] as $insight) {
                $this->store_user_insight($insight);
            }
        }
    }
}
