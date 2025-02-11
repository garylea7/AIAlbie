<?php
defined('ABSPATH') || exit;

class AIAlbiePWAEngine {
    private $db;
    private $pwa_config = [];
    private $offline_cache = [];
    private $sync_queue = [];
    private $push_subscriptions = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_pwa_tables();
        add_action('init', [$this, 'initialize_pwa']);
        add_action('wp_head', [$this, 'add_pwa_meta']);
    }

    private function init_pwa_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // PWA Configuration Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_pwa_config (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            config_key varchar(50) NOT NULL,
            config_value text NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY config_key (config_key)
        ) $charset_collate;";

        // Offline Cache Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_offline_cache (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            resource_url varchar(255) NOT NULL,
            cache_strategy varchar(50) NOT NULL,
            cache_duration int,
            last_cached datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY resource_url (resource_url)
        ) $charset_collate;";

        // Sync Queue Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_sync_queue (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            action_type varchar(50) NOT NULL,
            action_data text NOT NULL,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status)
        ) $charset_collate;";

        // Push Subscriptions Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_push_subscriptions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            endpoint varchar(255) NOT NULL,
            auth_token varchar(100),
            public_key varchar(100),
            user_id bigint(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY endpoint (endpoint)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function initialize_pwa() {
        // Register service worker
        add_action('wp_footer', function() {
            if ('https' === parse_url(home_url(), PHP_URL_SCHEME)) {
                echo "<script>
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/sw.js')
                            .then(registration => console.log('ServiceWorker registered'))
                            .catch(error => console.log('ServiceWorker registration failed:', error));
                    }
                </script>";
            }
        });

        // Generate manifest
        add_action('wp_head', function() {
            echo '<link rel="manifest" href="' . home_url('/manifest.json') . '">';
        });

        // Handle offline functionality
        $this->setup_offline_support();
    }

    public function generate_manifest() {
        $manifest = [
            'name' => get_bloginfo('name'),
            'short_name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'start_url' => home_url('/'),
            'background_color' => '#ffffff',
            'theme_color' => '#2271b1',
            'display' => 'standalone',
            'orientation' => 'any',
            'icons' => $this->generate_app_icons(),
            'shortcuts' => $this->generate_app_shortcuts(),
            'related_applications' => [],
            'prefer_related_applications' => false,
            'screenshots' => $this->generate_app_screenshots()
        ];

        return json_encode($manifest);
    }

    private function generate_app_icons() {
        $custom_logo_id = get_theme_mod('custom_logo');
        $icons = [];

        if ($custom_logo_id) {
            $sizes = [72, 96, 128, 144, 152, 192, 384, 512];
            foreach ($sizes as $size) {
                $icons[] = [
                    'src' => $this->generate_icon($custom_logo_id, $size),
                    'sizes' => "{$size}x{$size}",
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ];
            }
        }

        return $icons;
    }

    public function setup_offline_support() {
        // Generate service worker
        add_action('wp', function() {
            if (!is_admin()) {
                $sw_path = ABSPATH . 'sw.js';
                $sw_content = $this->generate_service_worker();
                file_put_contents($sw_path, $sw_content);
            }
        });

        // Cache essential resources
        add_action('wp_enqueue_scripts', function() {
            $this->cache_essential_resources();
        });

        // Setup offline page
        add_action('template_redirect', function() {
            if (isset($_GET['offline'])) {
                $this->show_offline_page();
            }
        });
    }

    private function generate_service_worker() {
        $cache_version = 'v1';
        $offline_page = home_url('/?offline=1');
        
        return "
            const CACHE_NAME = 'aialbie-{$cache_version}';
            const OFFLINE_PAGE = '{$offline_page}';

            // Install event
            self.addEventListener('install', event => {
                event.waitUntil(
                    caches.open(CACHE_NAME)
                        .then(cache => cache.addAll([
                            OFFLINE_PAGE,
                            // Add other essential resources
                            ...{$this->get_essential_resources()}
                        ]))
                );
            });

            // Fetch event
            self.addEventListener('fetch', event => {
                if (event.request.mode === 'navigate') {
                    event.respondWith(
                        fetch(event.request)
                            .catch(() => caches.match(OFFLINE_PAGE))
                    );
                } else {
                    event.respondWith(
                        caches.match(event.request)
                            .then(response => response || fetch(event.request))
                    );
                }
            });

            // Sync event
            self.addEventListener('sync', event => {
                if (event.tag === 'sync-data') {
                    event.waitUntil(syncData());
                }
            });

            // Push event
            self.addEventListener('push', event => {
                const options = {
                    body: event.data.text(),
                    icon: '{$this->get_notification_icon()}',
                    badge: '{$this->get_notification_badge()}'
                };

                event.waitUntil(
                    self.registration.showNotification('AIAlbie', options)
                );
            });
        ";
    }

    public function setup_push_notifications() {
        // Generate VAPID keys
        if (!get_option('aialbie_vapid_keys')) {
            $this->generate_vapid_keys();
        }

        // Add subscription endpoint
        add_action('rest_api_init', function() {
            register_rest_route('aialbie/v1', '/push/subscribe', [
                'methods' => 'POST',
                'callback' => [$this, 'handle_push_subscription'],
                'permission_callback' => function() {
                    return is_user_logged_in();
                }
            ]);
        });

        // Add push notification support
        add_action('wp_enqueue_scripts', function() {
            wp_enqueue_script('push-notifications', 
                plugins_url('assets/js/push-notifications.js', dirname(__FILE__)),
                [],
                '1.0',
                true
            );
            wp_localize_script('push-notifications', 'pushConfig', [
                'publicKey' => get_option('aialbie_vapid_public_key')
            ]);
        });
    }

    public function handle_push_subscription($request) {
        $subscription = json_decode($request->get_body(), true);
        $user_id = get_current_user_id();

        $this->store_subscription($subscription, $user_id);
        return new WP_REST_Response(['status' => 'success'], 200);
    }

    public function send_push_notification($user_id, $message) {
        $subscriptions = $this->get_user_subscriptions($user_id);
        $vapid_keys = get_option('aialbie_vapid_keys');

        foreach ($subscriptions as $subscription) {
            $this->push_message($subscription, $message, $vapid_keys);
        }
    }

    public function export_pwa_data() {
        return [
            'pwa_config' => $this->pwa_config,
            'offline_cache' => $this->offline_cache,
            'sync_queue' => $this->sync_queue,
            'push_subscriptions' => $this->push_subscriptions
        ];
    }

    public function import_pwa_data($data) {
        if (!empty($data['pwa_config'])) {
            foreach ($data['pwa_config'] as $config) {
                $this->store_pwa_config($config);
            }
        }

        if (!empty($data['offline_cache'])) {
            foreach ($data['offline_cache'] as $cache) {
                $this->store_offline_cache($cache);
            }
        }

        if (!empty($data['sync_queue'])) {
            foreach ($data['sync_queue'] as $sync) {
                $this->store_sync_queue($sync);
            }
        }

        if (!empty($data['push_subscriptions'])) {
            foreach ($data['push_subscriptions'] as $subscription) {
                $this->store_push_subscription($subscription);
            }
        }
    }
}
