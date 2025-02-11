<?php
defined('ABSPATH') || exit;

class AIAlbieAnalyticsDashboard {
    private $db;
    private $metrics = [];
    private $insights = [];
    private $reports = [];
    private $visualizations = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_analytics_tables();
        add_action('init', [$this, 'initialize_analytics']);
        add_action('wp_ajax_fetch_analytics', [$this, 'handle_analytics_request']);
    }

    private function init_analytics_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // Metrics Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_metrics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            metric_name varchar(50) NOT NULL,
            metric_value float,
            dimension varchar(50),
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY metric_name (metric_name)
        ) $charset_collate;";

        // Insights Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_insights (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            insight_type varchar(50) NOT NULL,
            insight_data text NOT NULL,
            confidence float,
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY insight_type (insight_type)
        ) $charset_collate;";

        // Reports Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_reports (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            report_name varchar(100) NOT NULL,
            report_data text NOT NULL,
            schedule varchar(50),
            last_run datetime,
            PRIMARY KEY  (id),
            KEY report_name (report_name)
        ) $charset_collate;";

        // Visualizations Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_visualizations (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            viz_type varchar(50) NOT NULL,
            viz_data text NOT NULL,
            config text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY viz_type (viz_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function initialize_analytics() {
        // Initialize metrics tracking
        $this->init_metrics_tracking();
        
        // Setup insights generation
        $this->init_insights_generation();
        
        // Initialize reporting
        $this->init_reporting();
        
        // Setup visualizations
        $this->init_visualizations();
    }

    public function track_metrics($data) {
        // Process metrics
        $processed_metrics = $this->process_metrics($data);
        
        // Store metrics
        foreach ($processed_metrics as $metric) {
            $this->store_metric($metric);
        }
        
        // Generate real-time insights
        $this->generate_realtime_insights($processed_metrics);
        
        return $processed_metrics;
    }

    private function process_metrics($data) {
        return [
            'user_engagement' => $this->calculate_engagement_metrics($data),
            'agent_performance' => $this->calculate_performance_metrics($data),
            'system_health' => $this->calculate_health_metrics($data),
            'business_metrics' => $this->calculate_business_metrics($data)
        ];
    }

    public function generate_insights() {
        // Get recent metrics
        $metrics = $this->get_recent_metrics();
        
        // Analyze trends
        $trends = $this->analyze_trends($metrics);
        
        // Generate insights
        $insights = $this->generate_metric_insights($trends);
        
        // Store insights
        foreach ($insights as $insight) {
            $this->store_insight($insight);
        }
        
        return $insights;
    }

    private function analyze_trends($metrics) {
        return [
            'growth' => $this->analyze_growth_trends($metrics),
            'patterns' => $this->analyze_usage_patterns($metrics),
            'anomalies' => $this->detect_anomalies($metrics),
            'correlations' => $this->find_correlations($metrics)
        ];
    }

    public function create_report($report_data) {
        // Validate report data
        if (!$this->validate_report_data($report_data)) {
            return new WP_Error('invalid_report', 'Invalid report data');
        }

        // Generate report
        $report = $this->generate_report($report_data);
        
        // Store report
        $report_id = $this->store_report($report);
        
        // Schedule if needed
        if (!empty($report_data['schedule'])) {
            $this->schedule_report($report_id, $report_data['schedule']);
        }
        
        return $report_id;
    }

    private function generate_report($data) {
        // Get metrics
        $metrics = $this->get_report_metrics($data);
        
        // Get insights
        $insights = $this->get_report_insights($data);
        
        // Generate visualizations
        $visualizations = $this->generate_report_visualizations($metrics);
        
        return [
            'metrics' => $metrics,
            'insights' => $insights,
            'visualizations' => $visualizations,
            'metadata' => $this->generate_report_metadata($data)
        ];
    }

    public function create_visualization($viz_data) {
        // Validate visualization data
        if (!$this->validate_visualization_data($viz_data)) {
            return new WP_Error('invalid_viz', 'Invalid visualization data');
        }

        // Process data
        $processed_data = $this->process_visualization_data($viz_data);
        
        // Generate visualization
        $visualization = $this->generate_visualization($processed_data);
        
        // Store visualization
        return $this->store_visualization($visualization);
    }

    private function process_visualization_data($data) {
        // Clean data
        $clean_data = $this->clean_visualization_data($data);
        
        // Transform data
        $transformed_data = $this->transform_visualization_data($clean_data);
        
        // Optimize data
        return $this->optimize_visualization_data($transformed_data);
    }

    public function get_dashboard_data() {
        return [
            'metrics' => $this->get_dashboard_metrics(),
            'insights' => $this->get_dashboard_insights(),
            'reports' => $this->get_dashboard_reports(),
            'visualizations' => $this->get_dashboard_visualizations()
        ];
    }

    private function get_dashboard_metrics() {
        return [
            'real_time' => $this->get_realtime_metrics(),
            'daily' => $this->get_daily_metrics(),
            'weekly' => $this->get_weekly_metrics(),
            'monthly' => $this->get_monthly_metrics()
        ];
    }

    public function export_analytics_data() {
        return [
            'metrics' => $this->metrics,
            'insights' => $this->insights,
            'reports' => $this->reports,
            'visualizations' => $this->visualizations
        ];
    }

    public function import_analytics_data($data) {
        if (!empty($data['metrics'])) {
            foreach ($data['metrics'] as $metric) {
                $this->store_metric($metric);
            }
        }

        if (!empty($data['insights'])) {
            foreach ($data['insights'] as $insight) {
                $this->store_insight($insight);
            }
        }

        if (!empty($data['reports'])) {
            foreach ($data['reports'] as $report) {
                $this->store_report($report);
            }
        }

        if (!empty($data['visualizations'])) {
            foreach ($data['visualizations'] as $viz) {
                $this->store_visualization($viz);
            }
        }
    }
}
