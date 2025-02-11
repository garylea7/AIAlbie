<?php
class AIAlbieInsights {
    private $security;
    private $config;
    private $audit;
    
    public function __construct() {
        $this->security = new AIAlbieSecurityManager();
        $this->config = new AIAlbieConfigManager();
        $this->audit = new AIAlbieAuditManager();
    }

    /**
     * Get AI insights dashboard data
     */
    public function get_dashboard_insights() {
        try {
            return [
                'success' => true,
                'performance' => $this->analyze_performance(),
                'trends' => $this->analyze_trends(),
                'recommendations' => $this->get_recommendations(),
                'optimization_opportunities' => $this->find_optimization_opportunities(),
                'security_insights' => $this->analyze_security(),
                'resource_usage' => $this->analyze_resources()
            ];
        } catch (Exception $e) {
            $this->audit->log_security_event('insights_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Analyze migration performance
     */
    private function analyze_performance() {
        global $wpdb;
        
        // Get migration stats
        $stats = $wpdb->get_results("
            SELECT 
                COUNT(*) as total_migrations,
                AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM {$wpdb->prefix}aialbie_migration_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        // Calculate success rate
        $success_rate = $stats->total_migrations > 0 
            ? ($stats->successful / $stats->total_migrations) * 100 
            : 0;
        
        // Get performance by type
        $type_performance = $wpdb->get_results("
            SELECT 
                type,
                COUNT(*) as count,
                AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration
            FROM {$wpdb->prefix}aialbie_migration_logs
            GROUP BY type
        ");
        
        return [
            'summary' => [
                'total_migrations' => $stats->total_migrations,
                'success_rate' => round($success_rate, 2),
                'avg_duration' => round($stats->avg_duration, 2),
                'failed_migrations' => $stats->failed
            ],
            'by_type' => $type_performance
        ];
    }

    /**
     * Analyze migration trends
     */
    private function analyze_trends() {
        global $wpdb;
        
        // Get daily trends
        $daily_trends = $wpdb->get_results("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as migrations,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful
            FROM {$wpdb->prefix}aialbie_migration_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        
        // Get popular source platforms
        $platforms = $wpdb->get_results("
            SELECT 
                source_platform,
                COUNT(*) as count
            FROM {$wpdb->prefix}aialbie_migration_logs
            GROUP BY source_platform
            ORDER BY count DESC
            LIMIT 5
        ");
        
        // Get common issues
        $issues = $wpdb->get_results("
            SELECT 
                error_type,
                COUNT(*) as count
            FROM {$wpdb->prefix}aialbie_migration_logs
            WHERE status = 'failed'
            GROUP BY error_type
            ORDER BY count DESC
            LIMIT 5
        ");
        
        return [
            'daily' => $daily_trends,
            'platforms' => $platforms,
            'common_issues' => $issues
        ];
    }

    /**
     * Get smart recommendations
     */
    private function get_recommendations() {
        $recommendations = [];
        
        // Check performance
        $performance = $this->analyze_performance();
        if ($performance['summary']['success_rate'] < 90) {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'high',
                'title' => 'Improve Migration Success Rate',
                'description' => 'Success rate is below 90%. Review common failure patterns and implement preventive measures.',
                'actions' => [
                    'Review error logs',
                    'Update error handling',
                    'Add pre-migration checks'
                ]
            ];
        }
        
        // Check resource usage
        $resources = $this->analyze_resources();
        if ($resources['disk_usage'] > 80) {
            $recommendations[] = [
                'type' => 'resource',
                'priority' => 'high',
                'title' => 'High Disk Usage',
                'description' => 'Disk usage is above 80%. Consider cleanup or expansion.',
                'actions' => [
                    'Clean temporary files',
                    'Archive old migrations',
                    'Increase disk space'
                ]
            ];
        }
        
        // Check security
        $security = $this->analyze_security();
        foreach ($security['vulnerabilities'] as $vuln) {
            $recommendations[] = [
                'type' => 'security',
                'priority' => 'critical',
                'title' => $vuln['title'],
                'description' => $vuln['description'],
                'actions' => $vuln['remediation_steps']
            ];
        }
        
        return $recommendations;
    }

    /**
     * Find optimization opportunities
     */
    private function find_optimization_opportunities() {
        $opportunities = [];
        
        // Check migration patterns
        $trends = $this->analyze_trends();
        
        // Look for patterns in successful migrations
        foreach ($trends['platforms'] as $platform) {
            $success_rate = $this->get_platform_success_rate($platform->source_platform);
            
            if ($success_rate < 80) {
                $opportunities[] = [
                    'type' => 'platform',
                    'target' => $platform->source_platform,
                    'title' => "Optimize {$platform->source_platform} Migrations",
                    'potential_impact' => 'high',
                    'description' => "Success rate for {$platform->source_platform} migrations is {$success_rate}%",
                    'suggestions' => $this->get_platform_optimization_suggestions($platform->source_platform)
                ];
            }
        }
        
        // Check resource usage patterns
        $resources = $this->analyze_resources();
        if ($resources['memory_spikes']) {
            $opportunities[] = [
                'type' => 'resource',
                'target' => 'memory',
                'title' => 'Optimize Memory Usage',
                'potential_impact' => 'medium',
                'description' => 'Detected memory usage spikes during migrations',
                'suggestions' => [
                    'Implement batch processing',
                    'Optimize image handling',
                    'Add memory monitoring'
                ]
            ];
        }
        
        return $opportunities;
    }

    /**
     * Analyze security status
     */
    private function analyze_security() {
        $vulnerabilities = [];
        $warnings = [];
        
        // Check file permissions
        $file_perms = $this->check_file_permissions();
        if (!empty($file_perms['issues'])) {
            $vulnerabilities[] = [
                'type' => 'file_permissions',
                'title' => 'Insecure File Permissions',
                'description' => 'Detected potentially insecure file permissions',
                'affected_files' => $file_perms['issues'],
                'remediation_steps' => [
                    'Update file permissions',
                    'Review ownership settings',
                    'Implement proper access controls'
                ]
            ];
        }
        
        // Check API security
        $api_security = $this->check_api_security();
        if (!empty($api_security['issues'])) {
            $warnings[] = [
                'type' => 'api_security',
                'title' => 'API Security Concerns',
                'description' => 'Potential API security improvements needed',
                'details' => $api_security['issues']
            ];
        }
        
        return [
            'vulnerabilities' => $vulnerabilities,
            'warnings' => $warnings,
            'last_scan' => current_time('mysql')
        ];
    }

    /**
     * Analyze resource usage
     */
    private function analyze_resources() {
        // Get disk usage
        $disk_total = disk_total_space(ABSPATH);
        $disk_free = disk_free_space(ABSPATH);
        $disk_used = $disk_total - $disk_free;
        $disk_usage = ($disk_used / $disk_total) * 100;
        
        // Check memory usage
        $memory_limit = ini_get('memory_limit');
        $memory_usage = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);
        
        // Detect memory spikes
        $memory_spikes = $this->detect_memory_spikes();
        
        return [
            'disk_total' => $disk_total,
            'disk_free' => $disk_free,
            'disk_used' => $disk_used,
            'disk_usage' => round($disk_usage, 2),
            'memory_limit' => $memory_limit,
            'memory_usage' => $memory_usage,
            'memory_peak' => $memory_peak,
            'memory_spikes' => $memory_spikes
        ];
    }

    /**
     * Get platform success rate
     */
    private function get_platform_success_rate($platform) {
        global $wpdb;
        
        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful
            FROM {$wpdb->prefix}aialbie_migration_logs
            WHERE source_platform = %s
        ", $platform));
        
        return $stats->total > 0 ? ($stats->successful / $stats->total) * 100 : 0;
    }

    /**
     * Get platform-specific optimization suggestions
     */
    private function get_platform_optimization_suggestions($platform) {
        $suggestions = [
            'shopify' => [
                'Use API rate limiting',
                'Implement product variant batching',
                'Optimize image downloads'
            ],
            'magento' => [
                'Batch process product attributes',
                'Optimize category imports',
                'Handle complex product types'
            ],
            'woocommerce' => [
                'Use direct database migration',
                'Preserve product relationships',
                'Maintain order history'
            ]
        ];
        
        return isset($suggestions[$platform]) ? $suggestions[$platform] : [];
    }

    /**
     * Check file permissions
     */
    private function check_file_permissions() {
        $issues = [];
        
        // Check plugin directory
        if (substr(sprintf('%o', fileperms(AIALBIE_PATH)), -4) > '0755') {
            $issues[] = [
                'path' => AIALBIE_PATH,
                'current_perms' => substr(sprintf('%o', fileperms(AIALBIE_PATH)), -4),
                'recommended_perms' => '0755'
            ];
        }
        
        // Check log directory
        $log_dir = AIALBIE_PATH . 'logs';
        if (substr(sprintf('%o', fileperms($log_dir)), -4) > '0755') {
            $issues[] = [
                'path' => $log_dir,
                'current_perms' => substr(sprintf('%o', fileperms($log_dir)), -4),
                'recommended_perms' => '0755'
            ];
        }
        
        return [
            'issues' => $issues,
            'checked_at' => current_time('mysql')
        ];
    }

    /**
     * Check API security
     */
    private function check_api_security() {
        $issues = [];
        
        // Check rate limiting
        if (!$this->config->get('api_rate_limit')) {
            $issues[] = [
                'type' => 'rate_limiting',
                'message' => 'API rate limiting is not enabled'
            ];
        }
        
        // Check API authentication
        if (!$this->config->get('api_auth_required')) {
            $issues[] = [
                'type' => 'authentication',
                'message' => 'API authentication is not enforced'
            ];
        }
        
        return [
            'issues' => $issues,
            'checked_at' => current_time('mysql')
        ];
    }

    /**
     * Detect memory spikes
     */
    private function detect_memory_spikes() {
        global $wpdb;
        
        $spikes = $wpdb->get_results("
            SELECT 
                process_id,
                MAX(memory_usage) as peak_memory,
                created_at
            FROM {$wpdb->prefix}aialbie_process_metrics
            WHERE memory_usage > 50000000  -- 50MB threshold
            GROUP BY process_id
            ORDER BY peak_memory DESC
            LIMIT 5
        ");
        
        return !empty($spikes);
    }
}
