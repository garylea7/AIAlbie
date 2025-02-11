<?php
class AIAlbieSiteCrawler {
    private $base_url;
    private $found_urls = array();
    private $processed_urls = array();
    private $sitemap = array();
    private $migration_manager;
    private $max_pages;
    private $current_depth = 0;
    private $progress = array();

    public function __construct($base_url, $max_pages = 100) {
        $this->base_url = rtrim($base_url, '/');
        $this->max_pages = $max_pages;
    }

    public function start_crawl() {
        $this->log_progress('Starting site crawl', array('base_url' => $this->base_url));
        
        // Start with homepage
        $this->found_urls[] = $this->base_url;
        
        // Crawl until we've processed all URLs or hit max pages
        while (!empty($this->found_urls) && count($this->processed_urls) < $this->max_pages) {
            $url = array_shift($this->found_urls);
            
            if (!in_array($url, $this->processed_urls)) {
                $this->crawl_page($url);
            }
        }

        return array(
            'pages_processed' => count($this->processed_urls),
            'sitemap' => $this->sitemap,
            'progress' => $this->progress
        );
    }

    private function crawl_page($url) {
        $this->log_progress('Processing page', array('url' => $url));

        // Get page content
        $html = $this->fetch_url($url);
        if (!$html) {
            $this->log_progress('Failed to fetch page', array('url' => $url));
            return;
        }

        // Parse HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        // Find all links
        $this->extract_links($xpath, $url);

        // Analyze page structure
        $page_data = $this->analyze_page($dom, $url);

        // Store in sitemap
        $this->sitemap[$url] = $page_data;

        // Mark as processed
        $this->processed_urls[] = $url;

        $this->log_progress('Page processed', array(
            'url' => $url,
            'title' => $page_data['title'],
            'links_found' => count($page_data['links'])
        ));
    }

    private function fetch_url($url) {
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            return false;
        }

        return wp_remote_retrieve_body($response);
    }

    private function extract_links($xpath, $current_url) {
        $links = $xpath->query('//a[@href]');
        
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $absolute_url = $this->make_absolute_url($href, $current_url);
            
            if ($this->should_crawl_url($absolute_url)) {
                $this->found_urls[] = $absolute_url;
            }
        }
    }

    private function analyze_page($dom, $url) {
        $xpath = new DOMXPath($dom);
        
        // Get page title
        $title = $xpath->query('//title');
        $title = $title->length > 0 ? $title->item(0)->textContent : '';

        // Get meta description
        $meta = $xpath->query('//meta[@name="description"]/@content');
        $description = $meta->length > 0 ? $meta->item(0)->nodeValue : '';

        // Find main content area
        $content_selectors = array(
            '//main',
            '//article',
            '//div[contains(@class, "content")]',
            '//div[contains(@class, "main")]'
        );

        $content = null;
        foreach ($content_selectors as $selector) {
            $content_element = $xpath->query($selector);
            if ($content_element->length > 0) {
                $content = $content_element->item(0);
                break;
            }
        }

        // Get all links
        $links = array();
        $link_elements = $xpath->query('//a[@href]');
        foreach ($link_elements as $link) {
            $href = $link->getAttribute('href');
            $text = $link->textContent;
            $links[] = array(
                'url' => $this->make_absolute_url($href, $url),
                'text' => trim($text)
            );
        }

        // Get all images
        $images = array();
        $img_elements = $xpath->query('//img');
        foreach ($img_elements as $img) {
            $images[] = array(
                'src' => $img->getAttribute('src'),
                'alt' => $img->getAttribute('alt'),
                'in_content' => $content ? $content->contains($img) : true
            );
        }

        return array(
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'links' => $links,
            'images' => $images,
            'has_content' => $content !== null
        );
    }

    private function make_absolute_url($href, $base) {
        if (strpos($href, '#') === 0) {
            return $base;
        }

        if (parse_url($href, PHP_URL_SCHEME) != '') {
            return $href;
        }

        if (strpos($href, '//') === 0) {
            return 'https:' . $href;
        }

        if (strpos($href, '/') === 0) {
            $parsed = parse_url($this->base_url);
            return $parsed['scheme'] . '://' . $parsed['host'] . $href;
        }

        return rtrim($base, '/') . '/' . ltrim($href, '/');
    }

    private function should_crawl_url($url) {
        // Skip if already found or processed
        if (in_array($url, $this->found_urls) || in_array($url, $this->processed_urls)) {
            return false;
        }

        // Only crawl URLs from the same domain
        $base_host = parse_url($this->base_url, PHP_URL_HOST);
        $url_host = parse_url($url, PHP_URL_HOST);
        
        if ($base_host !== $url_host) {
            return false;
        }

        // Skip non-HTML resources
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $skip_extensions = array('pdf', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'doc', 'docx');
        
        if (in_array($extension, $skip_extensions)) {
            return false;
        }

        return true;
    }

    public function migrate_site() {
        $this->log_progress('Starting site migration');
        
        $results = array();
        foreach ($this->sitemap as $url => $page_data) {
            // Create migration manager for this page
            $migration_manager = new AIAlbieMigrationManager(
                $this->fetch_url($url),
                $this->base_url
            );

            // Start migration
            $result = $migration_manager->start_migration(array(
                'title' => $page_data['title'],
                'source_url' => $url,
                'template' => $this->detect_template_type($page_data)
            ));

            $results[$url] = $result;
            
            $this->log_progress('Page migrated', array(
                'url' => $url,
                'page_id' => $result['page_id']
            ));
        }

        return array(
            'pages_migrated' => count($results),
            'results' => $results,
            'progress' => $this->progress
        );
    }

    private function detect_template_type($page_data) {
        // Logic to determine the best template based on content
        if (strpos(strtolower($page_data['url']), 'contact') !== false) {
            return 'contact-page';
        }
        if (strpos(strtolower($page_data['url']), 'about') !== false) {
            return 'about-page';
        }
        return 'default';
    }

    private function log_progress($message, $data = array()) {
        $this->progress[] = array(
            'timestamp' => current_time('mysql'),
            'message' => $message,
            'data' => $data
        );

        // You could add real-time progress updates here
        do_action('aialbie_migration_progress', $message, $data);
    }

    public function get_progress() {
        return array(
            'total_urls_found' => count($this->found_urls) + count($this->processed_urls),
            'urls_processed' => count($this->processed_urls),
            'current_url' => end($this->processed_urls),
            'progress_log' => $this->progress
        );
    }
}
