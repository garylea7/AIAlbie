<?php
defined('ABSPATH') || exit;

class AIAlbieSecurityEngine {
    private $db;
    private $security_config = [];
    private $threat_logs = [];
    private $access_controls = [];
    private $encryption_keys = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_security_tables();
        add_action('init', [$this, 'initialize_security']);
        add_filter('authenticate', [$this, 'enhance_authentication'], 30, 3);
    }

    private function init_security_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // Security Configuration Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_security_config (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            config_key varchar(50) NOT NULL,
            config_value text NOT NULL,
            is_encrypted tinyint(1) DEFAULT 0,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY config_key (config_key)
        ) $charset_collate;";

        // Threat Logs Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_threat_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            threat_type varchar(50) NOT NULL,
            threat_data text NOT NULL,
            ip_address varchar(45),
            user_agent text,
            severity varchar(20),
            logged_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY threat_type (threat_type)
        ) $charset_collate;";

        // Access Controls Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_access_controls (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            resource_type varchar(50) NOT NULL,
            resource_id varchar(255),
            permissions text NOT NULL,
            conditions text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY resource_type (resource_type)
        ) $charset_collate;";

        // Encryption Keys Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_encryption_keys (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            key_type varchar(50) NOT NULL,
            key_identifier varchar(100) NOT NULL,
            encrypted_key text NOT NULL,
            expiry_date datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY key_identifier (key_identifier)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function initialize_security() {
        // Set security headers
        add_action('send_headers', function() {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
            
            if (is_ssl()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
            }
        });

        // Enable SSL/HTTPS
        if (!is_ssl() && !is_admin()) {
            wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
            exit();
        }

        // Initialize encryption
        $this->initialize_encryption();
        
        // Set up access controls
        $this->setup_access_controls();
        
        // Configure rate limiting
        $this->setup_rate_limiting();
    }

    private function initialize_encryption() {
        if (!extension_loaded('openssl')) {
            error_log('OpenSSL extension is required for encryption');
            return;
        }

        // Generate encryption keys if not exists
        if (!get_option('aialbie_encryption_keys')) {
            $this->generate_encryption_keys();
        }

        // Set up encryption hooks
        add_filter('pre_update_option', [$this, 'encrypt_sensitive_data'], 10, 3);
        add_filter('option_', [$this, 'decrypt_sensitive_data'], 10, 2);
    }

    public function enhance_authentication($user, $username, $password) {
        if (!$username || !$password) {
            return $user;
        }

        // Check for suspicious patterns
        if ($this->detect_suspicious_login($username, $_SERVER['REMOTE_ADDR'])) {
            return new WP_Error('suspicious_activity', 'Suspicious login activity detected');
        }

        // Implement 2FA if enabled
        if ($this->is_2fa_enabled($username)) {
            return $this->handle_2fa($user, $username);
        }

        // Enhanced password validation
        if (!$this->validate_password_strength($password)) {
            return new WP_Error('weak_password', 'Password does not meet security requirements');
        }

        return $user;
    }

    private function setup_access_controls() {
        // Define role-based access controls
        $this->define_rbac_rules();
        
        // Set up API access controls
        $this->setup_api_access_controls();
        
        // Configure file access controls
        $this->setup_file_access_controls();
    }

    private function setup_rate_limiting() {
        // Configure rate limits
        $rate_limits = [
            'login' => ['attempts' => 5, 'period' => 300],
            'api' => ['requests' => 100, 'period' => 3600],
            'registration' => ['attempts' => 3, 'period' => 3600]
        ];

        foreach ($rate_limits as $type => $config) {
            $this->add_rate_limit($type, $config);
        }
    }

    public function encrypt_data($data, $context = 'default') {
        $key = $this->get_encryption_key($context);
        $iv = random_bytes(16);
        
        $encrypted = openssl_encrypt(
            json_encode($data),
            'AES-256-CBC',
            base64_decode($key),
            0,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    public function decrypt_data($encrypted_data, $context = 'default') {
        $key = $this->get_encryption_key($context);
        $data = base64_decode($encrypted_data);
        
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            base64_decode($key),
            0,
            $iv
        );

        return json_decode($decrypted, true);
    }

    public function log_security_event($type, $data, $severity = 'info') {
        $this->db->insert(
            $this->db->prefix . 'aialbie_threat_logs',
            [
                'threat_type' => $type,
                'threat_data' => json_encode($data),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'severity' => $severity
            ]
        );

        if ($severity === 'critical') {
            $this->notify_admin_security_event($type, $data);
        }
    }

    public function check_permissions($user_id, $resource_type, $resource_id, $action) {
        // Get user's roles and capabilities
        $user = get_user_by('ID', $user_id);
        if (!$user) return false;

        // Get resource permissions
        $permissions = $this->get_resource_permissions($resource_type, $resource_id);
        
        // Check if user has required permissions
        return $this->validate_permissions($user, $permissions, $action);
    }

    public function export_security_data() {
        return [
            'security_config' => $this->security_config,
            'threat_logs' => $this->threat_logs,
            'access_controls' => $this->access_controls,
            'encryption_keys' => $this->encryption_keys
        ];
    }

    public function import_security_data($data) {
        if (!empty($data['security_config'])) {
            foreach ($data['security_config'] as $config) {
                $this->store_security_config($config);
            }
        }

        if (!empty($data['threat_logs'])) {
            foreach ($data['threat_logs'] as $log) {
                $this->store_threat_log($log);
            }
        }

        if (!empty($data['access_controls'])) {
            foreach ($data['access_controls'] as $control) {
                $this->store_access_control($control);
            }
        }

        if (!empty($data['encryption_keys'])) {
            foreach ($data['encryption_keys'] as $key) {
                $this->store_encryption_key($key);
            }
        }
    }
}
