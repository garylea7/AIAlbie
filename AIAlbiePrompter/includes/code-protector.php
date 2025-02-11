<?php
class AIAlbieCodeProtector {
    private $security_manager;
    private $config_manager;
    private $obfuscation_key;
    
    public function __construct() {
        $this->security_manager = new AIAlbieSecurityManager();
        $this->config_manager = new AIAlbieConfigManager();
        $this->obfuscation_key = $this->generate_obfuscation_key();
    }

    /**
     * Generate unique obfuscation key
     */
    private function generate_obfuscation_key() {
        return hash_hmac(
            'sha256',
            uniqid('', true),
            $this->security_manager->get_encryption_key()
        );
    }

    /**
     * Protect PHP code
     */
    public function protect_code($code) {
        // Step 1: Remove comments and whitespace
        $code = $this->strip_comments($code);
        
        // Step 2: Encrypt strings
        $code = $this->encrypt_strings($code);
        
        // Step 3: Obfuscate variable names
        $code = $this->obfuscate_variables($code);
        
        // Step 4: Add code fingerprint
        $code = $this->add_fingerprint($code);
        
        // Step 5: Add anti-tampering checks
        $code = $this->add_integrity_checks($code);
        
        return $code;
    }

    /**
     * Strip comments and whitespace
     */
    private function strip_comments($code) {
        // Remove single-line comments
        $code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code);
        $code = preg_replace('/\/\/[^\n]*/', '', $code);
        
        // Remove whitespace
        $code = preg_replace('/\s+/', ' ', $code);
        
        return $code;
    }

    /**
     * Encrypt strings
     */
    private function encrypt_strings($code) {
        return preg_replace_callback(
            '/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/m',
            function($matches) {
                $string = isset($matches[2]) ? $matches[2] : $matches[1];
                return '$this->decrypt("' . $this->encrypt_string($string) . '")';
            },
            $code
        );
    }

    /**
     * Encrypt individual string
     */
    private function encrypt_string($string) {
        return openssl_encrypt(
            $string,
            'AES-256-CBC',
            $this->obfuscation_key,
            0,
            str_repeat("\0", 16)
        );
    }

    /**
     * Obfuscate variable names
     */
    private function obfuscate_variables($code) {
        $variables = [];
        $pattern = '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
        
        return preg_replace_callback($pattern, function($matches) use (&$variables) {
            $var = $matches[1];
            
            if (!isset($variables[$var])) {
                $variables[$var] = $this->generate_variable_name();
            }
            
            return '$' . $variables[$var];
        }, $code);
    }

    /**
     * Generate obfuscated variable name
     */
    private function generate_variable_name() {
        $length = rand(10, 20);
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $name = '';
        
        for ($i = 0; $i < $length; $i++) {
            $name .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        return '_' . $name;
    }

    /**
     * Add code fingerprint
     */
    private function add_fingerprint($code) {
        $fingerprint = $this->generate_fingerprint($code);
        
        return "<?php
            if(!defined('CODE_FINGERPRINT')) {
                define('CODE_FINGERPRINT', '{$fingerprint}');
            }
            if(!function_exists('verify_fingerprint')) {
                function verify_fingerprint(\$code) {
                    \$fingerprint = substr(hash_hmac('sha256', \$code, CODE_FINGERPRINT), 0, 32);
                    return hash_equals(CODE_FINGERPRINT, \$fingerprint);
                }
            }
            if(!verify_fingerprint(__FILE__)) {
                throw new Exception('Code integrity check failed');
            }
        ?>{$code}";
    }

    /**
     * Generate code fingerprint
     */
    private function generate_fingerprint($code) {
        return substr(
            hash_hmac('sha256', $code, $this->obfuscation_key),
            0,
            32
        );
    }

    /**
     * Add integrity checks
     */
    private function add_integrity_checks($code) {
        $checks = $this->generate_integrity_checks();
        
        return "<?php
            if(!function_exists('verify_integrity')) {
                function verify_integrity() {
                    \$checks = {$checks};
                    foreach(\$checks as \$check) {
                        if(!eval('return ' . \$check . ';')) {
                            throw new Exception('Integrity check failed');
                        }
                    }
                }
            }
            verify_integrity();
        ?>{$code}";
    }

    /**
     * Generate integrity checks
     */
    private function generate_integrity_checks() {
        $checks = [
            'defined("ABSPATH")',
            'defined("CODE_FINGERPRINT")',
            'function_exists("verify_fingerprint")',
            'function_exists("verify_integrity")'
        ];
        
        return var_export($checks, true);
    }

    /**
     * Protect file
     */
    public function protect_file($file_path) {
        // Read file
        $code = file_get_contents($file_path);
        
        // Protect code
        $protected = $this->protect_code($code);
        
        // Create backup
        $backup_path = $file_path . '.bak';
        rename($file_path, $backup_path);
        
        // Write protected code
        file_put_contents($file_path, $protected);
        
        return [
            'original' => $backup_path,
            'protected' => $file_path
        ];
    }

    /**
     * Protect directory
     */
    public function protect_directory($dir_path) {
        $results = [];
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir_path)
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $results[$file->getPathname()] = $this->protect_file($file->getPathname());
            }
        }
        
        return $results;
    }

    /**
     * Add license check
     */
    public function add_license_check($code, $license_key) {
        $encrypted_key = $this->encrypt_string($license_key);
        
        return "<?php
            if(!function_exists('verify_license')) {
                function verify_license(\$key) {
                    \$valid_key = '{$encrypted_key}';
                    return hash_equals(\$valid_key, \$key);
                }
            }
            if(!verify_license(LICENSE_KEY)) {
                throw new Exception('Invalid license key');
            }
        ?>{$code}";
    }

    /**
     * Add execution time limit
     */
    public function add_time_limit($code, $expiry_date) {
        $encrypted_date = $this->encrypt_string($expiry_date);
        
        return "<?php
            if(!function_exists('verify_expiry')) {
                function verify_expiry() {
                    \$expiry = '{$encrypted_date}';
                    return time() <= strtotime(\$expiry);
                }
            }
            if(!verify_expiry()) {
                throw new Exception('Code has expired');
            }
        ?>{$code}";
    }

    /**
     * Add domain lock
     */
    public function add_domain_lock($code, $allowed_domains) {
        $encrypted_domains = array_map([$this, 'encrypt_string'], $allowed_domains);
        $domains_array = var_export($encrypted_domains, true);
        
        return "<?php
            if(!function_exists('verify_domain')) {
                function verify_domain() {
                    \$allowed = {$domains_array};
                    \$current = \$_SERVER['HTTP_HOST'];
                    return in_array(\$current, \$allowed);
                }
            }
            if(!verify_domain()) {
                throw new Exception('Invalid domain');
            }
        ?>{$code}";
    }
}
