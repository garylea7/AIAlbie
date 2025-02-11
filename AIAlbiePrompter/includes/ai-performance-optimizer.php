<?php
defined('ABSPATH') || exit;

class AIAlbiePerformanceOptimizer {
    private $db;
    private $optimization_rules = [];
    private $resource_cache = [];
    private $performance_logs = [];
    private $optimization_scores = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_optimization_tables();
        add_action('init', [$this, 'load_optimization_rules']);
        add_action('template_redirect', [$this, 'optimize_page_load']);
    }

    private function init_optimization_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // Optimization Rules Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_optimization_rules (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            rule_type varchar(50) NOT NULL,
            rule_conditions text NOT NULL,
            optimization_actions text NOT NULL,
            priority int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY rule_type (rule_type)
        ) $charset_collate;";

        // Resource Cache Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_resource_cache (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            resource_url varchar(255) NOT NULL,
            resource_type varchar(50) NOT NULL,
            optimized_content longtext,
            cache_key varchar(32) NOT NULL,
            expires_at datetime,
            PRIMARY KEY  (id),
            UNIQUE KEY cache_key (cache_key)
        ) $charset_collate;";

        // Performance Logs Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_performance_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_url varchar(255) NOT NULL,
            device_type varchar(50),
            metrics text NOT NULL,
            optimization_impact float,
            logged_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY page_url (page_url)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function optimize_page_load() {
        // Critical CSS optimization
        add_action('wp_head', [$this, 'inject_critical_css'], 1);
        
        // Resource optimization
        add_filter('script_loader_src', [$this, 'optimize_scripts'], 10, 2);
        add_filter('style_loader_src', [$this, 'optimize_styles'], 10, 2);
        
        // Image optimization
        add_filter('wp_get_attachment_image_src', [$this, 'optimize_images'], 10, 4);
        
        // Database query optimization
        add_action('pre_get_posts', [$this, 'optimize_queries']);
    }

    public function inject_critical_css() {
        $critical_css = $this->generate_critical_css();
        echo "<style id='critical-css'>{$critical_css}</style>";
        
        // Defer non-critical CSS
        add_filter('style_loader_tag', function($tag, $handle) {
            if (!$this->is_critical_stylesheet($handle)) {
                return str_replace(' href', ' rel="preload" as="style" onload="this.rel=\'stylesheet\'" href', $tag);
            }
            return $tag;
        }, 10, 2);
    }

    private function generate_critical_css() {
        $template = get_template();
        $cache_key = "critical_css_{$template}";
        $critical_css = wp_cache_get($cache_key);

        if (false === $critical_css) {
            $critical_selectors = $this->analyze_above_fold_content();
            $critical_css = $this->extract_critical_styles($critical_selectors);
            wp_cache_set($cache_key, $critical_css, '', HOUR_IN_SECONDS);
        }

        return $this->minify_css($critical_css);
    }

    public function optimize_scripts($src, $handle) {
        if (!$src) return $src;

        // Check cache
        $cache_key = md5($src);
        $cached = wp_cache_get($cache_key, 'script_cache');
        if (false !== $cached) return $cached;

        // Optimize script
        $optimized_src = $this->process_script($src);
        
        // Cache result
        wp_cache_set($cache_key, $optimized_src, 'script_cache', HOUR_IN_SECONDS);
        
        return $optimized_src;
    }

    private function process_script($src) {
        // Add async/defer attributes
        if ($this->should_defer_script($src)) {
            return $this->defer_script($src);
        }

        // Combine and minify if local
        if ($this->is_local_script($src)) {
            return $this->minify_and_combine_script($src);
        }

        // Proxy external scripts
        if ($this->should_proxy_script($src)) {
            return $this->proxy_external_script($src);
        }

        return $src;
    }

    public function optimize_images($image, $attachment_id, $size, $icon) {
        if (!$image) return $image;

        // Generate WebP version
        $webp_url = $this->convert_to_webp($image[0]);
        
        // Create responsive sizes
        $srcset = $this->generate_responsive_srcset($attachment_id);
        
        // Add lazy loading
        $lazy_load = $this->should_lazy_load($image[0]);

        return [
            'url' => $image[0],
            'webp' => $webp_url,
            'srcset' => $srcset,
            'lazy' => $lazy_load,
            'width' => $image[1],
            'height' => $image[2]
        ];
    }

    private function convert_to_webp($image_url) {
        $cache_key = md5($image_url . 'webp');
        $webp_url = wp_cache_get($cache_key);

        if (false === $webp_url) {
            $webp_url = $this->create_webp_version($image_url);
            wp_cache_set($cache_key, $webp_url, '', DAY_IN_SECONDS);
        }

        return $webp_url;
    }

    public function optimize_queries($query) {
        if (is_admin()) return;

        // Add query optimization rules
        $this->add_index_hints($query);
        $this->limit_post_fields($query);
        $this->cache_query_results($query);
    }

    private function add_index_hints($query) {
        global $wpdb;
        
        // Analyze query and add appropriate index hints
        $sql = $query->request;
        $tables = $this->extract_tables_from_query($sql);
        
        foreach ($tables as $table) {
            $indexes = $this->get_table_indexes($table);
            $optimal_index = $this->find_optimal_index($sql, $indexes);
            
            if ($optimal_index) {
                $sql = $this->add_index_hint($sql, $table, $optimal_index);
            }
        }
        
        $query->request = $sql;
    }

    public function measure_optimization_impact() {
        $before_metrics = $this->get_performance_metrics();
        $this->apply_optimizations();
        $after_metrics = $this->get_performance_metrics();
        
        return $this->calculate_impact($before_metrics, $after_metrics);
    }

    private function get_performance_metrics() {
        return [
            'load_time' => $this->measure_load_time(),
            'ttfb' => $this->measure_ttfb(),
            'fcp' => $this->measure_first_contentful_paint(),
            'lcp' => $this->measure_largest_contentful_paint(),
            'cls' => $this->measure_cumulative_layout_shift()
        ];
    }

    public function export_optimization_data() {
        return [
            'optimization_rules' => $this->optimization_rules,
            'resource_cache' => $this->resource_cache,
            'performance_logs' => $this->performance_logs,
            'optimization_scores' => $this->optimization_scores
        ];
    }

    public function import_optimization_data($data) {
        if (!empty($data['optimization_rules'])) {
            foreach ($data['optimization_rules'] as $rule) {
                $this->store_optimization_rule($rule);
            }
        }

        if (!empty($data['resource_cache'])) {
            foreach ($data['resource_cache'] as $resource) {
                $this->store_resource_cache($resource);
            }
        }

        if (!empty($data['performance_logs'])) {
            foreach ($data['performance_logs'] as $log) {
                $this->store_performance_log($log);
            }
        }

        if (!empty($data['optimization_scores'])) {
            foreach ($data['optimization_scores'] as $score) {
                $this->store_optimization_score($score);
            }
        }
    }
}
