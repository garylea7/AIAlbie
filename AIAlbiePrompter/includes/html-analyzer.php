<?php
class AIAlbieHTMLAnalyzer {
    private $dom;
    private $xpath;
    private $content_selectors = array(
        'main',
        'article',
        'div[class*="content"]',
        'div[class*="main"]',
        'div[id*="content"]',
        'div[id*="main"]',
        '.post-content',
        '.entry-content',
        '#main-content'
    );

    public function __construct($html) {
        $this->dom = new DOMDocument();
        @$this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $this->xpath = new DOMXPath($this->dom);
    }

    public function analyze() {
        return array(
            'content_sections' => $this->find_content_sections(),
            'images' => $this->analyze_images(),
            'structure' => $this->analyze_structure(),
            'metadata' => $this->extract_metadata()
        );
    }

    private function find_content_sections() {
        $sections = array();
        
        // Try each content selector
        foreach ($this->content_selectors as $selector) {
            $elements = $this->css_select($selector);
            if ($elements->length > 0) {
                foreach ($elements as $element) {
                    $sections[] = array(
                        'type' => 'content',
                        'html' => $this->dom->saveHTML($element),
                        'text_length' => strlen(strip_tags($this->dom->saveHTML($element))),
                        'has_images' => $this->has_images($element),
                        'selector' => $selector
                    );
                }
            }
        }

        // If no content found, try to identify the most likely content area
        if (empty($sections)) {
            $sections = $this->identify_content_by_density();
        }

        return $sections;
    }

    private function analyze_images() {
        $images = array();
        $img_elements = $this->xpath->query('//img');
        
        foreach ($img_elements as $img) {
            $images[] = array(
                'src' => $img->getAttribute('src'),
                'alt' => $img->getAttribute('alt'),
                'width' => $img->getAttribute('width'),
                'height' => $img->getAttribute('height'),
                'class' => $img->getAttribute('class'),
                'in_gallery' => $this->is_in_gallery($img),
                'caption' => $this->find_caption($img),
                'dimensions' => $this->get_image_dimensions($img->getAttribute('src'))
            );
        }

        return $images;
    }

    private function analyze_structure() {
        return array(
            'layout' => $this->analyze_layout(),
            'navigation' => $this->find_navigation(),
            'forms' => $this->find_forms(),
            'headings' => $this->analyze_headings()
        );
    }

    private function analyze_layout() {
        $layout = array();
        
        // Find grid/column layouts
        $grid_selectors = array(
            'div[class*="row"]',
            'div[class*="grid"]',
            'div[class*="column"]',
            '.row', '.grid', '.columns'
        );

        foreach ($grid_selectors as $selector) {
            $elements = $this->css_select($selector);
            foreach ($elements as $element) {
                $layout[] = array(
                    'type' => 'grid',
                    'html' => $this->dom->saveHTML($element),
                    'classes' => $element->getAttribute('class'),
                    'children' => $element->childNodes->length
                );
            }
        }

        return $layout;
    }

    private function find_navigation() {
        $nav_elements = $this->xpath->query('//nav|//div[contains(@class, "menu")]|//ul[contains(@class, "menu")]');
        $navigation = array();

        foreach ($nav_elements as $nav) {
            $navigation[] = array(
                'type' => 'navigation',
                'html' => $this->dom->saveHTML($nav),
                'items' => $this->xpath->query('.//a', $nav)->length,
                'classes' => $nav->getAttribute('class')
            );
        }

        return $navigation;
    }

    private function find_forms() {
        $forms = array();
        $form_elements = $this->xpath->query('//form');

        foreach ($form_elements as $form) {
            $forms[] = array(
                'type' => 'form',
                'html' => $this->dom->saveHTML($form),
                'fields' => $this->analyze_form_fields($form),
                'action' => $form->getAttribute('action'),
                'method' => $form->getAttribute('method')
            );
        }

        return $forms;
    }

    private function analyze_form_fields($form) {
        $fields = array();
        $inputs = $this->xpath->query('.//input|.//select|.//textarea', $form);

        foreach ($inputs as $input) {
            $fields[] = array(
                'type' => $input->getAttribute('type') ?: $input->nodeName,
                'name' => $input->getAttribute('name'),
                'required' => $input->hasAttribute('required'),
                'validation' => $this->get_field_validation($input)
            );
        }

        return $fields;
    }

    private function analyze_headings() {
        $headings = array();
        for ($i = 1; $i <= 6; $i++) {
            $elements = $this->xpath->query("//h$i");
            foreach ($elements as $element) {
                $headings[] = array(
                    'level' => $i,
                    'text' => $element->textContent,
                    'html' => $this->dom->saveHTML($element)
                );
            }
        }
        return $headings;
    }

    private function extract_metadata() {
        return array(
            'title' => $this->get_meta_content('title'),
            'description' => $this->get_meta_content('description'),
            'keywords' => $this->get_meta_content('keywords'),
            'og_tags' => $this->get_og_tags(),
            'schema' => $this->get_schema_data()
        );
    }

    private function get_meta_content($name) {
        $meta = $this->xpath->query("//meta[@name='$name']/@content");
        return $meta->length ? $meta->item(0)->nodeValue : '';
    }

    private function get_og_tags() {
        $og_tags = array();
        $metas = $this->xpath->query("//meta[starts-with(@property, 'og:')]");
        
        foreach ($metas as $meta) {
            $property = $meta->getAttribute('property');
            $content = $meta->getAttribute('content');
            $og_tags[str_replace('og:', '', $property)] = $content;
        }

        return $og_tags;
    }

    private function get_schema_data() {
        $schema = array();
        $scripts = $this->xpath->query("//script[@type='application/ld+json']");
        
        foreach ($scripts as $script) {
            $schema[] = json_decode($script->textContent, true);
        }

        return $schema;
    }

    private function css_select($selector) {
        // Convert CSS selector to XPath
        $converter = new CssSelectorConverter();
        $xpath = $converter->toXPath($selector);
        return $this->xpath->query($xpath);
    }

    private function identify_content_by_density() {
        // Find the element with the most text content
        $body = $this->xpath->query('//body')->item(0);
        $elements = $this->xpath->query('//*', $body);
        $best_candidate = null;
        $max_length = 0;

        foreach ($elements as $element) {
            $text_length = strlen(strip_tags($this->dom->saveHTML($element)));
            if ($text_length > $max_length) {
                $max_length = $text_length;
                $best_candidate = $element;
            }
        }

        return array(array(
            'type' => 'content',
            'html' => $best_candidate ? $this->dom->saveHTML($best_candidate) : '',
            'text_length' => $max_length,
            'has_images' => $best_candidate ? $this->has_images($best_candidate) : false,
            'selector' => 'auto-detected'
        ));
    }

    private function has_images($element) {
        return $this->xpath->query('.//img', $element)->length > 0;
    }

    private function is_in_gallery($img) {
        $parent = $img->parentNode;
        $gallery_indicators = array('gallery', 'slider', 'carousel');
        
        while ($parent && $parent->nodeType === XML_ELEMENT_NODE) {
            $class = $parent->getAttribute('class');
            foreach ($gallery_indicators as $indicator) {
                if (strpos(strtolower($class), $indicator) !== false) {
                    return true;
                }
            }
            $parent = $parent->parentNode;
        }
        
        return false;
    }

    private function find_caption($img) {
        // Check for figcaption
        $parent = $img->parentNode;
        while ($parent && $parent->nodeName !== 'figure') {
            $parent = $parent->parentNode;
        }
        
        if ($parent) {
            $figcaptions = $this->xpath->query('.//figcaption', $parent);
            if ($figcaptions->length > 0) {
                return $figcaptions->item(0)->textContent;
            }
        }

        // Check for adjacent caption div
        $next = $img->nextSibling;
        while ($next && $next->nodeType === XML_TEXT_NODE) {
            $next = $next->nextSibling;
        }
        
        if ($next && strpos(strtolower($next->getAttribute('class')), 'caption') !== false) {
            return $next->textContent;
        }

        return '';
    }

    private function get_image_dimensions($src) {
        if (strpos($src, 'http') === 0) {
            // For remote images, we might want to download or get headers
            return array('width' => null, 'height' => null);
        }
        
        $path = $_SERVER['DOCUMENT_ROOT'] . parse_url($src, PHP_URL_PATH);
        if (file_exists($path)) {
            list($width, $height) = getimagesize($path);
            return array('width' => $width, 'height' => $height);
        }
        
        return array('width' => null, 'height' => null);
    }

    private function get_field_validation($input) {
        $validation = array();
        
        if ($input->hasAttribute('pattern')) {
            $validation['pattern'] = $input->getAttribute('pattern');
        }
        
        if ($input->hasAttribute('minlength')) {
            $validation['minlength'] = $input->getAttribute('minlength');
        }
        
        if ($input->hasAttribute('maxlength')) {
            $validation['maxlength'] = $input->getAttribute('maxlength');
        }
        
        if ($input->hasAttribute('min')) {
            $validation['min'] = $input->getAttribute('min');
        }
        
        if ($input->hasAttribute('max')) {
            $validation['max'] = $input->getAttribute('max');
        }

        return $validation;
    }
}
