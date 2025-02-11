<?php
/**
 * Plugin Name: AI Albie Prompter
 * Plugin URI: https://aialbie.com
 * Description: AI-powered WordPress migration and optimization system
 * Version: 1.0.0
 * Author: AI Albie
 * Author URI: https://aialbie.com
 * Text Domain: ai-albie-prompter
 * License: GPL v2 or later
 */

defined('ABSPATH') || exit;

class AIAlbiePrompter {
    private static $instance = null;
    private $security;
    private $config;
    private $audit;
    private $wizard;
    private $bulk_handler;
    private $platform_analyzer;
    private $migration_optimizer;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_components();
        $this->register_hooks();
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        define('AIALBIE_VERSION', '1.0.0');
        define('AIALBIE_FILE', __FILE__);
        define('AIALBIE_PATH', plugin_dir_path(__FILE__));
        define('AIALBIE_URL', plugin_dir_url(__FILE__));
        define('AIALBIE_ASSETS_URL', AIALBIE_URL . 'assets/');
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once AIALBIE_PATH . 'includes/security-manager.php';
        require_once AIALBIE_PATH . 'includes/config-manager.php';
        require_once AIALBIE_PATH . 'includes/audit-manager.php';
        require_once AIALBIE_PATH . 'includes/template-manager.php';
        require_once AIALBIE_PATH . 'includes/content-optimizer.php';
        require_once AIALBIE_PATH . 'includes/block-converter.php';
        require_once AIALBIE_PATH . 'includes/layout-analyzer.php';
        require_once AIALBIE_PATH . 'includes/bulk-migration-handler.php';
        require_once AIALBIE_PATH . 'includes/platform-analyzer.php';
        require_once AIALBIE_PATH . 'includes/migration-optimizer.php';
        require_once AIALBIE_PATH . 'includes/code-protector.php';
    }

    /**
     * Initialize components
     */
    private function init_components() {
        $this->security = new AIAlbieSecurityManager();
        $this->config = new AIAlbieConfigManager();
        $this->audit = new AIAlbieAuditManager();
        $this->wizard = new AIAlbieMigrationWizard();
        $this->bulk_handler = new AIAlbieBulkMigrationHandler();
        $this->platform_analyzer = new AIAlbiePlatformAnalyzer();
        $this->migration_optimizer = new AIAlbieMigrationOptimizer();
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_aialbie_analyze_platform', [$this, 'handle_platform_analysis']);
        add_action('wp_ajax_aialbie_optimize_migration', [$this, 'handle_migration_optimization']);
        add_action('wp_ajax_aialbie_get_migration_status', [$this, 'handle_migration_status']);
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create required directories
        wp_mkdir_p(AIALBIE_PATH . 'logs');
        wp_mkdir_p(AIALBIE_PATH . 'temp');
        
        // Initialize database tables
        $this->init_database();
        
        // Set default options
        $this->set_default_options();
        
        // Clear any existing temporary files
        $this->cleanup_temp_files();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup temporary files
        $this->cleanup_temp_files();
        
        // Clear scheduled tasks
        wp_clear_scheduled_hook('aialbie_process_bulk_migration');
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'AI Albie Prompter',
            'AI Albie',
            'manage_options',
            'ai-albie-prompter',
            [$this, 'render_admin_page'],
            'dashicons-superhero',
            30
        );
        
        add_submenu_page(
            'ai-albie-prompter',
            'Migration Wizard',
            'Migration Wizard',
            'manage_options',
            'ai-albie-wizard',
            [$this, 'render_wizard_page']
        );
        
        add_submenu_page(
            'ai-albie-prompter',
            'Bulk Migrations',
            'Bulk Migrations',
            'manage_options',
            'ai-albie-bulk',
            [$this, 'render_bulk_page']
        );
        
        add_submenu_page(
            'ai-albie-prompter',
            'Settings',
            'Settings',
            'manage_options',
            'ai-albie-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ai-albie') === false) {
            return;
        }
        
        // Styles
        wp_enqueue_style(
            'ai-albie-admin',
            AIALBIE_ASSETS_URL . 'css/admin.css',
            [],
            AIALBIE_VERSION
        );
        
        // Scripts
        wp_enqueue_script(
            'ai-albie-admin',
            AIALBIE_ASSETS_URL . 'js/admin.js',
            ['jquery'],
            AIALBIE_VERSION,
            true
        );
        
        wp_enqueue_script(
            'ai-albie-wizard',
            AIALBIE_ASSETS_URL . 'js/migration-wizard.js',
            ['jquery'],
            AIALBIE_VERSION,
            true
        );
        
        wp_localize_script('ai-albie-admin', 'aiAlbieAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ai-albie-admin'),
            'strings' => [
                'analyzing' => __('Analyzing website...', 'ai-albie-prompter'),
                'optimizing' => __('Optimizing migration...', 'ai-albie-prompter'),
                'error' => __('An error occurred', 'ai-albie-prompter')
            ]
        ]);
    }

    /**
     * AJAX handlers
     */
    public function handle_platform_analysis() {
        check_ajax_referer('ai-albie-admin');
        
        $url = sanitize_text_field($_POST['url']);
        $result = $this->platform_analyzer->analyze_platform($url);
        
        wp_send_json($result);
    }
    
    public function handle_migration_optimization() {
        check_ajax_referer('ai-albie-admin');
        
        $url = sanitize_text_field($_POST['url']);
        $options = json_decode(stripslashes($_POST['options']), true);
        
        $result = $this->migration_optimizer->optimize_migration($url, $options);
        
        wp_send_json($result);
    }
    
    public function handle_migration_status() {
        check_ajax_referer('ai-albie-admin');
        
        $process_id = sanitize_text_field($_POST['process_id']);
        $result = $this->bulk_handler->get_migration_status($process_id);
        
        wp_send_json($result);
    }

    /**
     * Render admin pages
     */
    public function render_admin_page() {
        include AIALBIE_PATH . 'templates/admin-page.php';
    }
    
    public function render_wizard_page() {
        include AIALBIE_PATH . 'templates/ai-migration-wizard.php';
    }
    
    public function render_bulk_page() {
        include AIALBIE_PATH . 'templates/bulk-migration.php';
    }
    
    public function render_settings_page() {
        include AIALBIE_PATH . 'templates/settings.php';
    }

    /**
     * Initialize database
     */
    private function init_database() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Migration logs table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}aialbie_migration_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            process_id varchar(50) NOT NULL,
            type varchar(50) NOT NULL,
            message text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY process_id (process_id),
            KEY type (type)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        $defaults = [
            'security_level' => 'high',
            'api_rate_limit' => 60,
            'backup_enabled' => true,
            'notification_email' => get_option('admin_email'),
            'log_retention_days' => 30
        ];
        
        foreach ($defaults as $key => $value) {
            if (get_option("aialbie_{$key}") === false) {
                update_option("aialbie_{$key}", $value);
            }
        }
    }

    /**
     * Cleanup temporary files
     */
    private function cleanup_temp_files() {
        $temp_dir = AIALBIE_PATH . 'temp';
        
        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '/*');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}

// Initialize plugin
function ai_albie_prompter() {
    return AIAlbiePrompter::get_instance();
}

// Start the plugin
ai_albie_prompter();
