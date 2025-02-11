<?php
defined('ABSPATH') || exit;

class AIAlbieGrowthEngine {
    private $db;
    private $experiments = [];
    private $growth_metrics = [];
    private $optimization_data = [];
    private $user_segments = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_growth_tables();
        add_action('init', [$this, 'initialize_growth']);
        add_action('template_redirect', [$this, 'run_experiments']);
    }

    private function init_growth_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // A/B Test Experiments Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_experiments (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            experiment_name varchar(100) NOT NULL,
            variants text NOT NULL,
            conditions text,
            start_date datetime,
            end_date datetime,
            status varchar(20),
            PRIMARY KEY  (id),
            KEY experiment_name (experiment_name)
        ) $charset_collate;";

        // Growth Metrics Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_growth_metrics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            metric_name varchar(50) NOT NULL,
            metric_value float,
            dimension varchar(50),
            recorded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY metric_name (metric_name)
        ) $charset_collate;";

        // Optimization Data Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_optimization_data (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            optimization_type varchar(50) NOT NULL,
            target_data text NOT NULL,
            results text,
            applied_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY optimization_type (optimization_type)
        ) $charset_collate;";

        // User Segments Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_user_segments (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            segment_name varchar(50) NOT NULL,
            conditions text NOT NULL,
            user_count int DEFAULT 0,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY segment_name (segment_name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function initialize_growth() {
        // Initialize A/B testing
        $this->init_ab_testing();
        
        // Setup growth tracking
        $this->setup_growth_tracking();
        
        // Initialize optimization
        $this->init_optimization();
        
        // Setup user segmentation
        $this->setup_user_segments();
    }

    private function init_ab_testing() {
        // Setup experiment framework
        add_action('template_redirect', [$this, 'assign_experiment_variants']);
        add_action('wp_footer', [$this, 'track_experiment_data']);
        
        // Initialize default experiments
        $this->create_default_experiments();
    }

    public function create_experiment($data) {
        // Validate experiment data
        if (!$this->validate_experiment($data)) {
            return false;
        }

        // Create variants
        $variants = $this->create_variants($data['variants']);
        
        // Setup tracking
        $tracking = $this->setup_experiment_tracking($data['metrics']);
        
        // Create experiment
        $experiment_id = $this->db->insert(
            $this->db->prefix . 'aialbie_experiments',
            [
                'experiment_name' => $data['name'],
                'variants' => json_encode($variants),
                'conditions' => json_encode($data['conditions']),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'active'
            ]
        );

        return $experiment_id;
    }

    public function run_experiments() {
        // Get active experiments
        $experiments = $this->get_active_experiments();
        
        foreach ($experiments as $experiment) {
            // Check if user should be included
            if ($this->should_include_user($experiment)) {
                // Assign variant
                $variant = $this->assign_variant($experiment);
                
                // Apply variant
                $this->apply_variant($experiment, $variant);
                
                // Track exposure
                $this->track_experiment_exposure($experiment, $variant);
            }
        }
    }

    private function should_include_user($experiment) {
        // Check conditions
        $conditions = json_decode($experiment['conditions'], true);
        
        // Check user segment
        if (!empty($conditions['segment'])) {
            if (!$this->is_user_in_segment($conditions['segment'])) {
                return false;
            }
        }
        
        // Check traffic allocation
        if (!empty($conditions['traffic_percentage'])) {
            if (!$this->should_allocate_traffic($conditions['traffic_percentage'])) {
                return false;
            }
        }
        
        return true;
    }

    public function track_growth_metrics() {
        $metrics = [
            'acquisition' => $this->track_acquisition_metrics(),
            'activation' => $this->track_activation_metrics(),
            'retention' => $this->track_retention_metrics(),
            'revenue' => $this->track_revenue_metrics(),
            'referral' => $this->track_referral_metrics()
        ];

        foreach ($metrics as $type => $data) {
            $this->store_growth_metrics($type, $data);
        }
    }

    private function track_acquisition_metrics() {
        return [
            'new_users' => $this->count_new_users(),
            'traffic_sources' => $this->analyze_traffic_sources(),
            'conversion_rates' => $this->calculate_conversion_rates(),
            'campaign_performance' => $this->analyze_campaign_performance()
        ];
    }

    public function optimize_user_experience() {
        // Get user behavior data
        $behavior_data = $this->get_user_behavior_data();
        
        // Analyze pain points
        $pain_points = $this->analyze_pain_points($behavior_data);
        
        // Generate optimizations
        $optimizations = $this->generate_ux_optimizations($pain_points);
        
        // Apply changes
        return $this->apply_ux_optimizations($optimizations);
    }

    private function analyze_pain_points($data) {
        return [
            'navigation' => $this->analyze_navigation_issues($data),
            'performance' => $this->analyze_performance_issues($data),
            'content' => $this->analyze_content_issues($data),
            'technical' => $this->analyze_technical_issues($data)
        ];
    }

    public function segment_users() {
        // Get user data
        $user_data = $this->get_user_data();
        
        // Create segments
        $segments = $this->create_user_segments($user_data);
        
        // Analyze segments
        $analysis = $this->analyze_segments($segments);
        
        // Store results
        return $this->store_segment_data($segments, $analysis);
    }

    private function create_user_segments($data) {
        $segments = [
            'behavior' => $this->segment_by_behavior($data),
            'engagement' => $this->segment_by_engagement($data),
            'value' => $this->segment_by_value($data),
            'lifecycle' => $this->segment_by_lifecycle($data)
        ];

        return $this->refine_segments($segments);
    }

    public function analyze_growth_opportunities() {
        // Analyze current performance
        $performance = $this->analyze_current_performance();
        
        // Identify opportunities
        $opportunities = $this->identify_opportunities($performance);
        
        // Prioritize opportunities
        $prioritized = $this->prioritize_opportunities($opportunities);
        
        return $this->create_growth_plan($prioritized);
    }

    private function identify_opportunities($performance) {
        return [
            'acquisition' => $this->identify_acquisition_opportunities($performance),
            'activation' => $this->identify_activation_opportunities($performance),
            'retention' => $this->identify_retention_opportunities($performance),
            'revenue' => $this->identify_revenue_opportunities($performance),
            'referral' => $this->identify_referral_opportunities($performance)
        ];
    }

    public function export_growth_data() {
        return [
            'experiments' => $this->experiments,
            'growth_metrics' => $this->growth_metrics,
            'optimization_data' => $this->optimization_data,
            'user_segments' => $this->user_segments
        ];
    }

    public function import_growth_data($data) {
        if (!empty($data['experiments'])) {
            foreach ($data['experiments'] as $experiment) {
                $this->store_experiment($experiment);
            }
        }

        if (!empty($data['growth_metrics'])) {
            foreach ($data['growth_metrics'] as $metric) {
                $this->store_growth_metric($metric);
            }
        }

        if (!empty($data['optimization_data'])) {
            foreach ($data['optimization_data'] as $optimization) {
                $this->store_optimization_data($optimization);
            }
        }

        if (!empty($data['user_segments'])) {
            foreach ($data['user_segments'] as $segment) {
                $this->store_user_segment($segment);
            }
        }
    }
}
