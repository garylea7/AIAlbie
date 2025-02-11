<?php
class AIAlbieBlockConverter {
    private $dom;
    private $block_mappings;
    
    public function __construct() {
        $this->dom = new DOMDocument();
        $this->block_mappings = [
            'header' => [
                'selector' => 'header',
                'block' => 'core/header',
                'attributes' => ['align' => 'full']
            ],
            'nav' => [
                'selector' => 'nav',
                'block' => 'core/navigation',
                'attributes' => ['layout' => 'flex']
            ],
            'main_content' => [
                'selector' => 'main, article',
                'block' => 'core/group',
                'attributes' => ['tagName' => 'main']
            ],
            'sidebar' => [
                'selector' => 'aside, .sidebar',
                'block' => 'core/sidebar',
                'attributes' => []
            ],
            'footer' => [
                'selector' => 'footer',
                'block' => 'core/footer',
                'attributes' => ['align' => 'full']
            ]
        ];
    }

    /**
     * Convert HTML content to WordPress blocks
     */
    public function convert_to_blocks($html_content) {
        // Load HTML
        $this->dom->loadHTML($html_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        // Start conversion
        $blocks = [];
        $this->process_node($this->dom->documentElement, $blocks);
        
        return $this->format_blocks($blocks);
    }

    /**
     * Process a DOM node and convert to blocks
     */
    private function process_node($node, &$blocks) {
        // Skip empty text nodes
        if ($node->nodeType === XML_TEXT_NODE && trim($node->textContent) === '') {
            return;
        }

        // Process based on node type
        switch ($node->nodeName) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $blocks[] = $this->create_heading_block($node);
                break;

            case 'p':
                $blocks[] = $this->create_paragraph_block($node);
                break;

            case 'img':
                $blocks[] = $this->create_image_block($node);
                break;

            case 'ul':
            case 'ol':
                $blocks[] = $this->create_list_block($node);
                break;

            case 'table':
                $blocks[] = $this->create_table_block($node);
                break;

            case 'blockquote':
                $blocks[] = $this->create_quote_block($node);
                break;

            case 'div':
                $this->process_div($node, $blocks);
                break;

            default:
                // Process child nodes
                if ($node->hasChildNodes()) {
                    foreach ($node->childNodes as $child) {
                        $this->process_node($child, $blocks);
                    }
                }
        }
    }

    /**
     * Process div elements which might need special handling
     */
    private function process_div($node, &$blocks) {
        $classes = $node->getAttribute('class');
        
        // Check for special div types
        if (strpos($classes, 'gallery') !== false) {
            $blocks[] = $this->create_gallery_block($node);
        }
        elseif (strpos($classes, 'video') !== false) {
            $blocks[] = $this->create_video_block($node);
        }
        elseif (strpos($classes, 'columns') !== false) {
            $blocks[] = $this->create_columns_block($node);
        }
        else {
            // Process as regular container
            $this->process_container($node, $blocks);
        }
    }

    /**
     * Create various block types
     */
    private function create_heading_block($node) {
        $level = substr($node->nodeName, 1);
        return [
            'blockName' => 'core/heading',
            'attrs' => [
                'level' => (int)$level,
                'content' => $node->textContent,
                'align' => $this->get_alignment($node)
            ],
            'innerHTML' => $node->textContent
        ];
    }

    private function create_paragraph_block($node) {
        return [
            'blockName' => 'core/paragraph',
            'attrs' => [
                'content' => $node->textContent,
                'dropCap' => false,
                'align' => $this->get_alignment($node)
            ],
            'innerHTML' => $node->textContent
        ];
    }

    private function create_image_block($node) {
        return [
            'blockName' => 'core/image',
            'attrs' => [
                'url' => $node->getAttribute('src'),
                'alt' => $node->getAttribute('alt'),
                'caption' => $node->getAttribute('title'),
                'align' => $this->get_alignment($node),
                'sizeSlug' => 'large'
            ],
            'innerHTML' => $node->outerHTML
        ];
    }

    private function create_gallery_block($node) {
        $images = [];
        $imageNodes = $node->getElementsByTagName('img');
        
        foreach ($imageNodes as $img) {
            $images[] = [
                'url' => $img->getAttribute('src'),
                'alt' => $img->getAttribute('alt'),
                'caption' => $img->getAttribute('title')
            ];
        }

        return [
            'blockName' => 'core/gallery',
            'attrs' => [
                'images' => $images,
                'columns' => $this->determine_gallery_columns($images),
                'linkTo' => 'none'
            ],
            'innerHTML' => $node->outerHTML
        ];
    }

    private function create_list_block($node) {
        $items = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeName === 'li') {
                $items[] = $child->textContent;
            }
        }

        return [
            'blockName' => 'core/list',
            'attrs' => [
                'ordered' => $node->nodeName === 'ol',
                'values' => $items
            ],
            'innerHTML' => $node->outerHTML
        ];
    }

    private function create_table_block($node) {
        $table = [
            'blockName' => 'core/table',
            'attrs' => [
                'hasFixedLayout' => true,
                'head' => [],
                'body' => []
            ],
            'innerHTML' => $node->outerHTML
        ];

        // Process table head
        $thead = $node->getElementsByTagName('thead')->item(0);
        if ($thead) {
            foreach ($thead->getElementsByTagName('tr') as $row) {
                $table['attrs']['head'][] = $this->process_table_row($row);
            }
        }

        // Process table body
        $tbody = $node->getElementsByTagName('tbody')->item(0);
        if ($tbody) {
            foreach ($tbody->getElementsByTagName('tr') as $row) {
                $table['attrs']['body'][] = $this->process_table_row($row);
            }
        }

        return $table;
    }

    private function create_quote_block($node) {
        return [
            'blockName' => 'core/quote',
            'attrs' => [
                'value' => $node->textContent,
                'citation' => $node->getAttribute('cite')
            ],
            'innerHTML' => $node->outerHTML
        ];
    }

    /**
     * Helper functions
     */
    private function get_alignment($node) {
        $style = $node->getAttribute('style');
        $class = $node->getAttribute('class');
        
        if (strpos($style, 'text-align: center') !== false || 
            strpos($class, 'center') !== false) {
            return 'center';
        }
        if (strpos($style, 'text-align: right') !== false || 
            strpos($class, 'right') !== false) {
            return 'right';
        }
        
        return 'left';
    }

    private function determine_gallery_columns($images) {
        $count = count($images);
        if ($count <= 3) return $count;
        if ($count <= 6) return 3;
        return 4;
    }

    private function process_table_row($row) {
        $cells = [];
        foreach ($row->childNodes as $cell) {
            if ($cell->nodeType === XML_ELEMENT_NODE) {
                $cells[] = $cell->textContent;
            }
        }
        return $cells;
    }

    private function process_container($node, &$blocks) {
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $this->process_node($child, $blocks);
            }
        }
    }

    /**
     * Format blocks into WordPress block format
     */
    private function format_blocks($blocks) {
        $output = '';
        foreach ($blocks as $block) {
            $output .= $this->serialize_block($block);
        }
        return $output;
    }

    private function serialize_block($block) {
        $attrs = !empty($block['attrs']) ? json_encode($block['attrs']) : '';
        $output = "<!-- wp:{$block['blockName']}";
        if ($attrs) {
            $output .= " {$attrs}";
        }
        $output .= " -->\n";
        $output .= $block['innerHTML'] . "\n";
        $output .= "<!-- /wp:{$block['blockName']} -->\n\n";
        
        return $output;
    }
}
