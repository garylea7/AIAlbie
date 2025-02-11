<?php
defined('ABSPATH') || exit;

class AIAlbieSEOEngine {
    private $db;
    private $seo_config = [];
    private $content_insights = [];
    private $keyword_analytics = [];
    private $performance_metrics = [];

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->init_seo_tables();
        add_action('init', [$this, 'initialize_seo']);
        add_filter('the_content', [$this, 'enhance_content_seo']);
    }

    private function init_seo_tables() {
        $charset_collate = $this->db->get_charset_collate();

        // SEO Configuration Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_seo_config (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            config_key varchar(50) NOT NULL,
            config_value text NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY config_key (config_key)
        ) $charset_collate;";

        // Content Insights Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_content_insights (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            content_url varchar(255) NOT NULL,
            content_type varchar(50) NOT NULL,
            insights text NOT NULL,
            score float DEFAULT 0,
            analyzed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY content_url (content_url)
        ) $charset_collate;";

        // Keyword Analytics Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_keyword_analytics (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            keyword varchar(255) NOT NULL,
            search_volume int,
            competition float,
            ranking_position int,
            tracked_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY keyword (keyword)
        ) $charset_collate;";

        // Performance Metrics Table
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->db->prefix}aialbie_seo_performance (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            page_url varchar(255) NOT NULL,
            metric_type varchar(50) NOT NULL,
            metric_value float,
            measured_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY page_url (page_url)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function initialize_seo() {
        // Add meta tags
        add_action('wp_head', [$this, 'add_meta_tags']);
        
        // Generate sitemaps
        add_action('init', [$this, 'generate_sitemaps']);
        
        // Enhance breadcrumbs
        add_filter('woocommerce_breadcrumb_defaults', [$this, 'enhance_breadcrumbs']);
        
        // Implement schema markup
        add_filter('the_content', [$this, 'add_schema_markup']);
    }

    public function enhance_content_seo($content) {
        // Analyze content
        $analysis = $this->analyze_content($content);
        
        // Optimize headings
        $content = $this->optimize_headings($content);
        
        // Add internal links
        $content = $this->add_internal_links($content);
        
        // Optimize images
        $content = $this->optimize_images($content);
        
        return $content;
    }

    public function analyze_content($content) {
        return [
            'readability' => $this->analyze_readability($content),
            'keywords' => $this->extract_keywords($content),
            'structure' => $this->analyze_structure($content),
            'links' => $this->analyze_links($content)
        ];
    }

    private function analyze_readability($content) {
        // Calculate readability scores
        $scores = [
            'flesch_kincaid' => $this->calculate_flesch_kincaid($content),
            'sentence_length' => $this->analyze_sentence_length($content),
            'paragraph_length' => $this->analyze_paragraph_length($content),
            'passive_voice' => $this->detect_passive_voice($content)
        ];

        return $this->calculate_readability_score($scores);
    }

    public function generate_sitemaps() {
        // Generate main sitemap
        $this->generate_main_sitemap();
        
        // Generate post sitemap
        $this->generate_post_sitemap();
        
        // Generate page sitemap
        $this->generate_page_sitemap();
        
        // Generate image sitemap
        $this->generate_image_sitemap();
        
        // Ping search engines
        $this->ping_search_engines();
    }

    private function generate_main_sitemap() {
        $sitemap = new DOMDocument('1.0', 'UTF-8');
        $sitemap->formatOutput = true;

        // Create sitemap index
        $sitemapIndex = $sitemap->createElement('sitemapindex');
        $sitemapIndex->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $sitemap->appendChild($sitemapIndex);

        // Add sitemap references
        $sitemaps = ['post', 'page', 'image'];
        foreach ($sitemaps as $type) {
            $sitemap_url = home_url("/sitemap-{$type}.xml");
            $this->add_sitemap_url($sitemapIndex, $sitemap, $sitemap_url);
        }

        // Save sitemap
        $sitemap->save(ABSPATH . 'sitemap.xml');
    }

    public function add_schema_markup($content) {
        // Get post type
        $post_type = get_post_type();
        
        // Generate appropriate schema
        $schema = $this->generate_schema($post_type);
        
        // Add schema to content
        return $this->inject_schema($content, $schema);
    }

    private function generate_schema($post_type) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->get_schema_type($post_type),
            'url' => get_permalink(),
            'name' => get_the_title(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => $this->get_author_schema(),
            'publisher' => $this->get_publisher_schema()
        ];

        return json_encode($schema);
    }

    public function track_keywords() {
        // Get tracked keywords
        $keywords = $this->get_tracked_keywords();
        
        // Update rankings
        foreach ($keywords as $keyword) {
            $this->update_keyword_ranking($keyword);
        }
        
        // Generate report
        return $this->generate_keyword_report();
    }

    public function optimize_for_voice_search($content) {
        // Extract question patterns
        $questions = $this->extract_questions($content);
        
        // Structure answers
        $answers = $this->structure_answers($questions);
        
        // Add voice-friendly markup
        return $this->add_voice_markup($content, $answers);
    }

    public function export_seo_data() {
        return [
            'seo_config' => $this->seo_config,
            'content_insights' => $this->content_insights,
            'keyword_analytics' => $this->keyword_analytics,
            'performance_metrics' => $this->performance_metrics
        ];
    }

    public function import_seo_data($data) {
        if (!empty($data['seo_config'])) {
            foreach ($data['seo_config'] as $config) {
                $this->store_seo_config($config);
            }
        }

        if (!empty($data['content_insights'])) {
            foreach ($data['content_insights'] as $insight) {
                $this->store_content_insight($insight);
            }
        }

        if (!empty($data['keyword_analytics'])) {
            foreach ($data['keyword_analytics'] as $analytic) {
                $this->store_keyword_analytic($analytic);
            }
        }

        if (!empty($data['performance_metrics'])) {
            foreach ($data['performance_metrics'] as $metric) {
                $this->store_performance_metric($metric);
            }
        }
    }
}
