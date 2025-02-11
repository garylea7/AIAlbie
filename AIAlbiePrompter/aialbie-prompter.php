<?php
/*
Plugin Name: AIAlbie Prompter
Plugin URI: https://prompter.aialbie.com
Description: Transform basic prompts into powerful AI instructions
Version: 1.0
Author: AIAlbie
Author URI: https://aialbie.com
*/

if (!defined('ABSPATH')) exit;

class AIAlbiePrompter {
    private $abacus_api_key;

    public function __construct() {
        $this->abacus_api_key = get_option('aialbie_prompter_api_key');
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_endpoints'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'AIAlbie Prompter',
            'AI Prompter',
            'manage_options',
            'aialbie-prompter',
            array($this, 'render_admin_page'),
            'dashicons-editor-quote'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_style(
            'aialbie-prompter',
            plugins_url('assets/css/style.css', __FILE__)
        );
        wp_enqueue_script(
            'aialbie-prompter',
            plugins_url('assets/js/prompter.js', __FILE__),
            array('jquery'),
            '1.0',
            true
        );
    }

    public function register_endpoints() {
        register_rest_route('aialbie-prompter/v1', '/optimize', array(
            'methods' => 'POST',
            'callback' => array($this, 'optimize_prompt'),
            'permission_callback' => '__return_true'
        ));
    }

    public function optimize_prompt($request) {
        $params = $request->get_params();
        $prompt = sanitize_text_field($params['prompt']);
        $category = sanitize_text_field($params['category']);

        // Call Abacus.ai API here
        $optimized = $this->call_abacus_api($prompt, $category);

        return rest_ensure_response(array(
            'success' => true,
            'optimized' => $optimized
        ));
    }

    private function call_abacus_api($prompt, $category) {
        // Implement Abacus.ai API call
        // This is a placeholder for the actual API call
        return "Optimized version of: " . $prompt;
    }

    public function render_admin_page() {
        include plugin_dir_path(__FILE__) . 'templates/admin.php';
    }

    public static function activate() {
        // Activation tasks
        add_option('aialbie_prompter_api_key', '');
    }

    public static function deactivate() {
        // Cleanup tasks
    }
}

// Initialize plugin
function run_aialbie_prompter() {
    $plugin = new AIAlbiePrompter();
}

// Hooks
register_activation_hook(__FILE__, array('AIAlbiePrompter', 'activate'));
register_deactivation_hook(__FILE__, array('AIAlbiePrompter', 'deactivate'));

// Start the plugin
run_aialbie_prompter();
