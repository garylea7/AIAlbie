<?php
class AIAlbieURLConverter {
    private $url;
    private $html_content;
    private $migration_manager;

    public function __construct($url) {
        $this->url = $url;
    }

    public function convert_single_page() {
        // 1. Fetch the page content
        $this->html_content = $this->fetch_url_content();
        if (!$this->html_content) {
            return array(
                'success' => false,
                'message' => 'Could not fetch page content'
            );
        }

        // 2. Extract main content
        $content = $this->extract_main_content();

        // 3. Convert to blocks
        return $this->convert_to_blocks($content);
    }

    private function fetch_url_content() {
        $response = wp_remote_get($this->url);
        if (is_wp_error($response)) {
            return false;
        }
        return wp_remote_retrieve_body($response);
    }

    private function extract_main_content() {
        $dom = new DOMDocument();
        @$dom->loadHTML($this->html_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        // Find main content area (adjust selectors based on your site structure)
        $content_queries = array(
            "//div[contains(@class, 'content')]",
            "//main",
            "//article",
            "//td[contains(text(), 'Hello and welcome')]" // Specific to your site
        );

        foreach ($content_queries as $query) {
            $content = $xpath->query($query);
            if ($content->length > 0) {
                return $dom->saveHTML($content->item(0));
            }
        }

        return $this->html_content; // Fallback to full content
    }

    private function convert_to_blocks($content) {
        // Clean up content
        $content = $this->clean_content($content);

        // Split into sections
        $sections = $this->split_into_sections($content);

        // Convert each section to a block
        $blocks = array();
        foreach ($sections as $section) {
            $blocks[] = $this->section_to_block($section);
        }

        return array(
            'success' => true,
            'blocks' => $blocks,
            'url' => $this->url
        );
    }

    private function clean_content($content) {
        // Remove unwanted elements
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
        
        // Remove border images but keep content images
        $content = preg_replace('/<img[^>]*border[^>]*>/i', '', $content);
        
        return $content;
    }

    private function split_into_sections($content) {
        $dom = new DOMDocument();
        @$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        $sections = array();

        // Find all content elements
        $elements = $xpath->query('//*[self::p or self::img or self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');
        
        foreach ($elements as $element) {
            if ($element->nodeName === 'img') {
                $sections[] = array(
                    'type' => 'image',
                    'content' => $dom->saveHTML($element)
                );
            } else {
                $sections[] = array(
                    'type' => 'text',
                    'content' => $dom->saveHTML($element)
                );
            }
        }

        return $sections;
    }

    private function section_to_block($section) {
        switch ($section['type']) {
            case 'image':
                return $this->create_image_block($section['content']);
            case 'text':
                return $this->create_text_block($section['content']);
            default:
                return $section['content'];
        }
    }

    private function create_image_block($html) {
        // Extract image attributes
        preg_match('/<img[^>]+src="([^"]+)"[^>]*>/i', $html, $src);
        preg_match('/<img[^>]+alt="([^"]+)"[^>]*>/i', $html, $alt);

        return array(
            'blockName' => 'core/image',
            'attrs' => array(
                'url' => $src[1] ?? '',
                'alt' => $alt[1] ?? ''
            )
        );
    }

    private function create_text_block($html) {
        return array(
            'blockName' => 'core/paragraph',
            'attrs' => array(),
            'innerHTML' => $html
        );
    }

    public function batch_convert_site() {
        $crawler = new AIAlbieSiteCrawler($this->url);
        return $crawler->start_crawl();
    }
}

// Usage example in a WordPress page:
function aialbie_url_converter_form() {
    ?>
    <div class="aialbie-converter">
        <h2>Convert Web Page to WordPress</h2>
        <form method="post" action="">
            <input type="url" name="page_url" placeholder="Enter page URL" required>
            <select name="conversion_type">
                <option value="single">Convert Single Page</option>
                <option value="batch">Convert Entire Site</option>
            </select>
            <button type="submit">Convert</button>
        </form>
    </div>
    <?php
}

// Handle form submission
function handle_url_conversion() {
    if (isset($_POST['page_url'])) {
        $converter = new AIAlbieURLConverter($_POST['page_url']);
        
        if ($_POST['conversion_type'] === 'single') {
            $result = $converter->convert_single_page();
        } else {
            $result = $converter->batch_convert_site();
        }

        // Create WordPress page with converted content
        if ($result['success']) {
            $post_data = array(
                'post_title'    => 'Converted: ' . basename($_POST['page_url']),
                'post_content'  => serialize($result['blocks']),
                'post_status'   => 'draft',
                'post_type'     => 'page'
            );
            wp_insert_post($post_data);
        }
    }
}
