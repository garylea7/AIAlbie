<?php
class AIAlbieConfigManager {
    private $config_file;
    private $security_manager;
    private $config_data;
    
    public function __construct() {
        $this->security_manager = new AIAlbieSecurityManager();
        $this->config_file = dirname(__FILE__) . '/secure-config.php';
        $this->load_config();
    }

    /**
     * Load encrypted configuration
     */
    private function load_config() {
        if (file_exists($this->config_file)) {
            $encrypted_data = include $this->config_file;
            $this->config_data = $this->security_manager->decrypt($encrypted_data);
        } else {
            $this->config_data = $this->get_default_config();
            $this->save_config();
        }
    }

    /**
     * Get default configuration
     */
    private function get_default_config() {
        return [
            'security' => [
                'allowed_ips' => [],
                'api_keys' => [],
                'access_tokens' => []
            ],
            'features' => [
                'enabled_modules' => [
                    'template_analyzer' => true,
                    'content_optimizer' => true,
                    'block_converter' => true
                ],
                'advanced_features' => [
                    'ai_analysis' => true,
                    'code_protection' => true,
                    'secure_storage' => true
                ]
            ],
            'templates' => [
                'allowed_templates' => [
                    'historic-modern',
                    'aviation-tech'
                ],
                'custom_templates' => []
            ],
            'optimization' => [
                'content_rules' => [
                    'max_heading_depth' => 3,
                    'min_word_count' => 300,
                    'image_optimization' => true
                ],
                'seo_rules' => [
                    'meta_description' => true,
                    'heading_hierarchy' => true,
                    'keyword_density' => true
                ]
            ]
        ];
    }

    /**
     * Save encrypted configuration
     */
    private function save_config() {
        $encrypted_data = $this->security_manager->encrypt($this->config_data);
        $content = "<?php
            defined('ABSPATH') || exit;
            return '{$encrypted_data}';
        ";
        file_put_contents($this->config_file, $content);
    }

    /**
     * Get configuration value
     */
    public function get($key, $default = null) {
        $keys = explode('.', $key);
        $value = $this->config_data;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    /**
     * Set configuration value
     */
    public function set($key, $value) {
        $keys = explode('.', $key);
        $config = &$this->config_data;
        
        while (count($keys) > 1) {
            $k = array_shift($keys);
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }
        
        $config[array_shift($keys)] = $value;
        $this->save_config();
    }

    /**
     * Add allowed IP
     */
    public function add_allowed_ip($ip) {
        $ips = $this->get('security.allowed_ips', []);
        if (!in_array($ip, $ips)) {
            $ips[] = $ip;
            $this->set('security.allowed_ips', $ips);
        }
    }

    /**
     * Add API key
     */
    public function add_api_key($name, $key) {
        $keys = $this->get('security.api_keys', []);
        $keys[$name] = $key;
        $this->set('security.api_keys', $keys);
    }

    /**
     * Enable/disable feature
     */
    public function toggle_feature($feature, $enabled = true) {
        $this->set("features.enabled_modules.{$feature}", $enabled);
    }

    /**
     * Add custom template
     */
    public function add_custom_template($template) {
        $templates = $this->get('templates.custom_templates', []);
        $templates[] = $template;
        $this->set('templates.custom_templates', $templates);
    }

    /**
     * Update optimization rules
     */
    public function update_optimization_rules($rules) {
        $current_rules = $this->get('optimization.content_rules', []);
        $this->set('optimization.content_rules', array_merge($current_rules, $rules));
    }

    /**
     * Update SEO rules
     */
    public function update_seo_rules($rules) {
        $current_rules = $this->get('optimization.seo_rules', []);
        $this->set('optimization.seo_rules', array_merge($current_rules, $rules));
    }
}
