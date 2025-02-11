<?php
class AIAlbieMigrationManager {
    private $analyzer;
    private $media_handler;
    private $current_page;
    private $migration_log = array();

    public function __construct($html_content, $base_url = '') {
        $this->analyzer = new AIAlbieHTMLAnalyzer($html_content);
        $this->media_handler = new AIAlbieMediaHandler($base_url);
    }

    public function start_migration($options = array()) {
        $this->log_step('Starting migration process');

        // Step 1: Analyze HTML
        $analysis = $this->analyzer->analyze();
        $this->log_step('HTML analysis complete', $analysis);

        // Step 2: Process Images
        $processed_images = $this->media_handler->process_images($analysis['images']);
        $this->log_step('Image processing complete', $processed_images);

        // Step 3: Create WordPress Page
        $page_id = $this->create_wordpress_page($analysis, $processed_images, $options);
        $this->log_step('WordPress page created', array('page_id' => $page_id));

        // Step 4: Process additional elements
        $this->process_additional_elements($analysis, $page_id);
        
        return array(
            'success' => true,
            'page_id' => $page_id,
            'log' => $this->migration_log,
            'stats' => $this->get_migration_stats()
        );
    }

    private function create_wordpress_page($analysis, $processed_images, $options) {
        // Prepare content sections
        $blocks = array();

        foreach ($analysis['content_sections'] as $section) {
            // Replace image sources
            $content = $this->replace_image_sources($section['html'], $processed_images);
            
            // Convert to blocks
            $blocks = array_merge(
                $blocks,
                $this->convert_to_gutenberg_blocks($content)
            );
        }

        // Create the page
        $page_data = array(
            'post_title' => $options['title'] ?? $analysis['metadata']['title'],
            'post_content' => implode("\n\n", $blocks),
            'post_status' => 'draft',
            'post_type' => 'page',
            'meta_input' => array(
                '_wp_page_template' => $options['template'] ?? 'default',
                '_aialbie_migration_data' => array(
                    'original_url' => $options['source_url'] ?? '',
                    'migration_date' => current_time('mysql'),
                    'migration_options' => $options
                )
            )
        );

        return wp_insert_post($page_data);
    }

    private function convert_to_gutenberg_blocks($html) {
        $blocks = array();
        
        // Create a temporary DOMDocument for this content
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($dom);

        // Process each element
        $elements = $xpath->query('//*');
        foreach ($elements as $element) {
            $block = $this->element_to_block($element);
            if ($block) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    private function element_to_block($element) {
        switch ($element->nodeName) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                return $this->create_heading_block($element);
            
            case 'p':
                return $this->create_paragraph_block($element);
            
            case 'img':
                return $this->create_image_block($element);
            
            case 'ul':
            case 'ol':
                return $this->create_list_block($element);
            
            case 'table':
                return $this->create_table_block($element);
            
            case 'blockquote':
                return $this->create_quote_block($element);
            
            case 'div':
                // Check for special div types
                if ($this->is_gallery($element)) {
                    return $this->create_gallery_block($element);
                }
                if ($this->is_columns($element)) {
                    return $this->create_columns_block($element);
                }
                break;
        }

        // Default to paragraph if no specific block type
        return $this->create_paragraph_block($element);
    }

    private function create_heading_block($element) {
        $level = substr($element->nodeName, 1);
        return '<!-- wp:heading {"level":' . $level . '} -->'
             . '<h' . $level . '>' . $element->textContent . '</h' . $level . '>'
             . '<!-- /wp:heading -->';
    }

    private function create_paragraph_block($element) {
        return '<!-- wp:paragraph -->'
             . '<p>' . $this->get_inner_html($element) . '</p>'
             . '<!-- /wp:paragraph -->';
    }

    private function create_image_block($element) {
        $src = $element->getAttribute('src');
        $alt = $element->getAttribute('alt');
        $class = $element->getAttribute('class');
        
        return '<!-- wp:image {"src":"' . esc_url($src) . '","alt":"' . esc_attr($alt) . '"} -->'
             . '<figure class="wp-block-image">'
             . '<img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($class) . '"/>'
             . '</figure>'
             . '<!-- /wp:image -->';
    }

    private function create_list_block($element) {
        $type = $element->nodeName === 'ol' ? 'ordered' : 'unordered';
        return '<!-- wp:list {"type":"' . $type . '"} -->'
             . $this->get_outer_html($element)
             . '<!-- /wp:list -->';
    }

    private function create_table_block($element) {
        return '<!-- wp:table -->'
             . '<figure class="wp-block-table">'
             . $this->get_outer_html($element)
             . '</figure>'
             . '<!-- /wp:table -->';
    }

    private function create_quote_block($element) {
        return '<!-- wp:quote -->'
             . '<blockquote class="wp-block-quote">'
             . $this->get_inner_html($element)
             . '</blockquote>'
             . '<!-- /wp:quote -->';
    }

    private function create_gallery_block($element) {
        $images = array();
        $img_elements = $element->getElementsByTagName('img');
        
        foreach ($img_elements as $img) {
            $images[] = array(
                'src' => $img->getAttribute('src'),
                'alt' => $img->getAttribute('alt')
            );
        }

        return $this->media_handler->create_gallery($images);
    }

    private function create_columns_block($element) {
        $columns = array();
        $children = $element->childNodes;
        
        foreach ($children as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $columns[] = $this->convert_to_gutenberg_blocks($this->get_outer_html($child));
            }
        }

        $column_count = count($columns);
        
        return '<!-- wp:columns -->'
             . '<div class="wp-block-columns">'
             . implode('', array_map(function($column) use ($column_count) {
                 return '<!-- wp:column {"width":"' . (100/$column_count) . '%"} -->'
                      . '<div class="wp-block-column">'
                      . $column
                      . '</div>'
                      . '<!-- /wp:column -->';
             }, $columns))
             . '</div>'
             . '<!-- /wp:columns -->';
    }

    private function process_additional_elements($analysis, $page_id) {
        // Process navigation menus
        if (!empty($analysis['structure']['navigation'])) {
            $this->process_navigation($analysis['structure']['navigation'], $page_id);
        }

        // Process forms
        if (!empty($analysis['structure']['forms'])) {
            $this->process_forms($analysis['structure']['forms'], $page_id);
        }

        // Process metadata
        if (!empty($analysis['metadata'])) {
            $this->process_metadata($analysis['metadata'], $page_id);
        }
    }

    private function process_navigation($navigation, $page_id) {
        foreach ($navigation as $nav) {
            $menu_name = 'Imported Menu ' . uniqid();
            $menu_id = wp_create_nav_menu($menu_name);
            
            if (is_wp_error($menu_id)) {
                continue;
            }

            // Process menu items
            $items = $nav['items'];
            foreach ($items as $item) {
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' => $item['text'],
                    'menu-item-url' => $item['url'],
                    'menu-item-status' => 'publish'
                ));
            }
        }
    }

    private function process_forms($forms, $page_id) {
        // Check if Contact Form 7 is active
        if (!function_exists('wpcf7_add_form')) {
            return;
        }

        foreach ($forms as $form) {
            $form_post = array(
                'post_title' => 'Imported Form ' . uniqid(),
                'post_type' => 'wpcf7_contact_form',
                'post_status' => 'publish'
            );
            
            $form_id = wp_insert_post($form_post);
            
            if (!is_wp_error($form_id)) {
                // Convert HTML form to CF7 format
                $cf7_content = $this->convert_to_cf7_format($form);
                update_post_meta($form_id, '_form', $cf7_content);
            }
        }
    }

    private function process_metadata($metadata, $page_id) {
        // Update SEO metadata if Yoast is active
        if (defined('WPSEO_VERSION')) {
            update_post_meta($page_id, '_yoast_wpseo_title', $metadata['title']);
            update_post_meta($page_id, '_yoast_wpseo_metadesc', $metadata['description']);
        }

        // Update standard WordPress metadata
        update_post_meta($page_id, '_aialbie_original_metadata', $metadata);
    }

    private function replace_image_sources($content, $processed_images) {
        foreach ($processed_images as $image) {
            $content = str_replace(
                $image['original_url'],
                $image['wordpress_url'],
                $content
            );
        }
        return $content;
    }

    private function log_step($message, $data = null) {
        $this->migration_log[] = array(
            'timestamp' => current_time('mysql'),
            'message' => $message,
            'data' => $data
        );
    }

    private function get_migration_stats() {
        return array(
            'images_processed' => $this->media_handler->get_stats(),
            'blocks_created' => count($this->blocks),
            'duration' => time() - $this->start_time
        );
    }

    private function get_inner_html($element) {
        $innerHTML = '';
        $children = $element->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }

    private function get_outer_html($element) {
        return $element->ownerDocument->saveHTML($element);
    }

    private function is_gallery($element) {
        $class = $element->getAttribute('class');
        return strpos($class, 'gallery') !== false || 
               strpos($class, 'slider') !== false || 
               strpos($class, 'carousel') !== false;
    }

    private function is_columns($element) {
        $class = $element->getAttribute('class');
        return strpos($class, 'row') !== false || 
               strpos($class, 'columns') !== false || 
               strpos($class, 'grid') !== false;
    }

    private function convert_to_cf7_format($form) {
        $cf7_content = '';
        foreach ($form['fields'] as $field) {
            switch ($field['type']) {
                case 'text':
                    $cf7_content .= '[text* ' . $field['name'] . ']';
                    break;
                case 'email':
                    $cf7_content .= '[email* ' . $field['name'] . ']';
                    break;
                case 'textarea':
                    $cf7_content .= '[textarea ' . $field['name'] . ']';
                    break;
                // Add more field types as needed
            }
            $cf7_content .= "\n";
        }
        $cf7_content .= '[submit "Send"]';
        return $cf7_content;
    }
}
