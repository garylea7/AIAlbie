<?php
class AIAlbieAuditManager {
    private $security_manager;
    private $config_manager;
    private $audit_storage;
    
    public function __construct() {
        $this->security_manager = new AIAlbieSecurityManager();
        $this->config_manager = new AIAlbieConfigManager();
        $this->audit_storage = new AIAlbieAuditStorage();
    }

    /**
     * Log security event
     */
    public function log_security_event($event_type, $details) {
        $event = [
            'type' => $event_type,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'details' => $details
        ];
        
        // Encrypt and store event
        $encrypted_event = $this->security_manager->encrypt($event);
        $this->audit_storage->store_event($encrypted_event);
        
        // Check for security alerts
        $this->check_security_alerts($event);
    }

    /**
     * Log API request
     */
    public function log_api_request($endpoint, $method, $response_code) {
        $request = [
            'endpoint' => $endpoint,
            'method' => $method,
            'response_code' => $response_code,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'headers' => getallheaders()
        ];
        
        $this->log_security_event('api_request', $request);
    }

    /**
     * Log file access
     */
    public function log_file_access($file_path, $operation) {
        $access = [
            'file' => $file_path,
            'operation' => $operation,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $this->log_security_event('file_access', $access);
    }

    /**
     * Log authentication attempt
     */
    public function log_auth_attempt($success, $details) {
        $attempt = [
            'success' => $success,
            'details' => $details,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $this->log_security_event('authentication', $attempt);
    }

    /**
     * Check for security alerts
     */
    private function check_security_alerts($event) {
        // Check for multiple failed auth attempts
        if ($event['type'] === 'authentication' && !$event['details']['success']) {
            $this->check_failed_auth_attempts($event['ip']);
        }
        
        // Check for suspicious API activity
        if ($event['type'] === 'api_request') {
            $this->check_api_activity($event['ip']);
        }
        
        // Check for unauthorized file access
        if ($event['type'] === 'file_access') {
            $this->check_file_access_patterns($event['ip']);
        }
    }

    /**
     * Check failed authentication attempts
     */
    private function check_failed_auth_attempts($ip) {
        $attempts = $this->audit_storage->get_recent_events('authentication', [
            'ip' => $ip,
            'success' => false,
            'window' => 3600 // 1 hour
        ]);
        
        if (count($attempts) >= 5) {
            $this->trigger_security_alert('multiple_failed_auth', [
                'ip' => $ip,
                'attempts' => count($attempts)
            ]);
        }
    }

    /**
     * Check API activity patterns
     */
    private function check_api_activity($ip) {
        $requests = $this->audit_storage->get_recent_events('api_request', [
            'ip' => $ip,
            'window' => 60 // 1 minute
        ]);
        
        if (count($requests) >= 100) {
            $this->trigger_security_alert('api_abuse', [
                'ip' => $ip,
                'request_count' => count($requests)
            ]);
        }
    }

    /**
     * Check file access patterns
     */
    private function check_file_access_patterns($ip) {
        $accesses = $this->audit_storage->get_recent_events('file_access', [
            'ip' => $ip,
            'window' => 300 // 5 minutes
        ]);
        
        if (count($accesses) >= 50) {
            $this->trigger_security_alert('file_access_abuse', [
                'ip' => $ip,
                'access_count' => count($accesses)
            ]);
        }
    }

    /**
     * Trigger security alert
     */
    private function trigger_security_alert($type, $details) {
        $alert = [
            'type' => $type,
            'details' => $details,
            'timestamp' => time()
        ];
        
        // Store alert
        $this->audit_storage->store_alert($alert);
        
        // Send notification
        $this->send_alert_notification($alert);
        
        // Take automatic action
        $this->handle_security_alert($alert);
    }

    /**
     * Send alert notification
     */
    private function send_alert_notification($alert) {
        // Get notification settings
        $settings = $this->config_manager->get('security.notifications');
        
        if ($settings['email_enabled']) {
            // Send email notification
            $this->send_email_alert($alert);
        }
        
        if ($settings['webhook_enabled']) {
            // Send webhook notification
            $this->send_webhook_alert($alert);
        }
    }

    /**
     * Handle security alert
     */
    private function handle_security_alert($alert) {
        switch ($alert['type']) {
            case 'multiple_failed_auth':
                // Block IP temporarily
                $this->block_ip($alert['details']['ip'], 3600);
                break;
                
            case 'api_abuse':
                // Revoke API access
                $this->revoke_api_access($alert['details']['ip']);
                break;
                
            case 'file_access_abuse':
                // Block file access
                $this->block_file_access($alert['details']['ip']);
                break;
        }
    }

    /**
     * Block IP address
     */
    private function block_ip($ip, $duration) {
        $blocked_ips = $this->config_manager->get('security.blocked_ips', []);
        $blocked_ips[$ip] = time() + $duration;
        $this->config_manager->set('security.blocked_ips', $blocked_ips);
    }

    /**
     * Revoke API access
     */
    private function revoke_api_access($ip) {
        $revoked = $this->config_manager->get('security.revoked_access', []);
        $revoked[$ip] = time();
        $this->config_manager->set('security.revoked_access', $revoked);
    }

    /**
     * Block file access
     */
    private function block_file_access($ip) {
        $blocked = $this->config_manager->get('security.blocked_file_access', []);
        $blocked[$ip] = time();
        $this->config_manager->set('security.blocked_file_access', $blocked);
    }
}

/**
 * Secure audit storage
 */
class AIAlbieAuditStorage {
    private $storage_dir;
    private $security_manager;
    
    public function __construct() {
        $this->storage_dir = dirname(__FILE__) . '/audit-logs';
        $this->security_manager = new AIAlbieSecurityManager();
        $this->ensure_storage_dir();
    }
    
    private function ensure_storage_dir() {
        if (!file_exists($this->storage_dir)) {
            mkdir($this->storage_dir, 0700, true);
        }
    }
    
    public function store_event($encrypted_event) {
        $filename = date('Y-m-d') . '.log';
        $path = $this->storage_dir . '/' . $filename;
        
        file_put_contents(
            $path,
            $encrypted_event . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
    
    public function store_alert($alert) {
        $filename = 'alerts.log';
        $path = $this->storage_dir . '/' . $filename;
        
        $encrypted_alert = $this->security_manager->encrypt($alert);
        
        file_put_contents(
            $path,
            $encrypted_alert . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
    
    public function get_recent_events($type, $filters) {
        $events = [];
        $start_time = time() - $filters['window'];
        
        // Read recent log files
        $files = glob($this->storage_dir . '/*.log');
        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $event = $this->security_manager->decrypt($line);
                
                if ($event['type'] === $type && 
                    $event['timestamp'] >= $start_time &&
                    $this->matches_filters($event, $filters)) {
                    $events[] = $event;
                }
            }
        }
        
        return $events;
    }
    
    private function matches_filters($event, $filters) {
        foreach ($filters as $key => $value) {
            if ($key === 'window') continue;
            
            if (!isset($event[$key]) || $event[$key] !== $value) {
                return false;
            }
        }
        
        return true;
    }
}
