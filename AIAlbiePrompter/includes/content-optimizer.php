<?php
class AIAlbieContentOptimizer {
    private $block_patterns = [
        'header' => [
            'pattern' => '/<header.*?>(.*?)<\/header>/is',
            'block' => 'core/header',
            'settings' => ['align' => 'full']
        ],
        'paragraph' => [
            'pattern' => '/<p.*?>(.*?)<\/p>/is',
            'block' => 'core/paragraph',
            'settings' => ['dropCap' => false]
        ],
        'image' => [
            'pattern' => '/<img[^>]+>/i',
            'block' => 'core/image',
            'settings' => ['sizeSlug' => 'large']
        ],
        'gallery' => [
            'pattern' => '/<div class="gallery".*?>(.*?)<\/div>/is',
            'block' => 'core/gallery',
            'settings' => ['columns' => 3]
        ],
        'list' => [
            'pattern' => '/<ul.*?>(.*?)<\/ul>/is',
            'block' => 'core/list',
            'settings' => ['ordered' => false]
        ],
        'table' => [
            'pattern' => '/<table.*?>(.*?)<\/table>/is',
            'block' => 'core/table',
            'settings' => ['hasFixedLayout' => true]
        ]
    ];

    /**
     * Analyze content and suggest optimizations
     */
    public function analyze_content($html_content) {
        return [
            'structure' => $this->analyze_structure($html_content),
            'blocks' => $this->suggest_blocks($html_content),
            'improvements' => $this->suggest_improvements($html_content),
            'seo_suggestions' => $this->analyze_seo($html_content)
        ];
    }

    /**
     * Analyze content structure
     */
    private function analyze_structure($html) {
        $structure = [
            'sections' => $this->identify_sections($html),
            'hierarchy' => $this->analyze_hierarchy($html),
            'content_flow' => $this->analyze_content_flow($html)
        ];

        return $structure;
    }

    /**
     * Identify main sections of content
     */
    private function identify_sections($html) {
        $sections = [];
        
        // Find main content divisions
        preg_match_all('/<div[^>]*class="([^"]*)"[^>]*>/i', $html, $matches);
        
        foreach ($matches[1] as $class) {
            if (strpos($class, 'section') !== false || 
                strpos($class, 'content') !== false ||
                strpos($class, 'wrapper') !== false) {
                $sections[] = $class;
            }
        }

        return array_unique($sections);
    }

    /**
     * Analyze content hierarchy
     */
    private function analyze_hierarchy($html) {
        $hierarchy = [];
        
        // Analyze heading structure
        for ($i = 1; $i <= 6; $i++) {
            preg_match_all("/<h{$i}.*?>(.*?)<\/h{$i}>/is", $html, $matches);
            if (!empty($matches[1])) {
                $hierarchy["h{$i}"] = count($matches[1]);
            }
        }

        return $hierarchy;
    }

    /**
     * Analyze content flow and suggest improvements
     */
    private function analyze_content_flow($html) {
        $flow_analysis = [
            'sections_count' => 0,
            'avg_section_length' => 0,
            'has_clear_hierarchy' => false,
            'suggestions' => []
        ];

        // Count sections
        $sections = $this->identify_sections($html);
        $flow_analysis['sections_count'] = count($sections);

        // Analyze section lengths
        preg_match_all('/<div[^>]*class="[^"]*section[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches);
        if (!empty($matches[1])) {
            $total_length = 0;
            foreach ($matches[1] as $section) {
                $total_length += strlen(strip_tags($section));
            }
            $flow_analysis['avg_section_length'] = $total_length / count($matches[1]);
        }

        // Check hierarchy
        $hierarchy = $this->analyze_hierarchy($html);
        $flow_analysis['has_clear_hierarchy'] = !empty($hierarchy) && isset($hierarchy['h1']);

        // Generate suggestions
        if ($flow_analysis['avg_section_length'] > 1000) {
            $flow_analysis['suggestions'][] = "Consider breaking up longer sections for better readability";
        }
        if (!$flow_analysis['has_clear_hierarchy']) {
            $flow_analysis['suggestions'][] = "Add clear heading structure starting with H1";
        }

        return $flow_analysis;
    }

    /**
     * Suggest appropriate WordPress blocks
     */
    private function suggest_blocks($html) {
        $suggestions = [];

        foreach ($this->block_patterns as $type => $pattern) {
            preg_match_all($pattern['pattern'], $html, $matches);
            if (!empty($matches[0])) {
                $suggestions[] = [
                    'type' => $type,
                    'block' => $pattern['block'],
                    'count' => count($matches[0]),
                    'settings' => $pattern['settings'],
                    'examples' => array_slice($matches[0], 0, 3) // Show first 3 examples
                ];
            }
        }

        // Suggest group blocks for related content
        $this->suggest_group_blocks($html, $suggestions);

        return $suggestions;
    }

    /**
     * Suggest improvements for content
     */
    private function suggest_improvements($html) {
        $improvements = [];

        // Check image optimization
        $this->check_images($html, $improvements);

        // Check content readability
        $this->check_readability($html, $improvements);

        // Check content structure
        $this->check_structure($html, $improvements);

        return $improvements;
    }

    /**
     * Check images and suggest improvements
     */
    private function check_images($html, &$improvements) {
        preg_match_all('/<img[^>]+>/i', $html, $matches);
        
        foreach ($matches[0] as $img) {
            // Check for alt text
            if (strpos($img, 'alt="') === false || strpos($img, 'alt=""') !== false) {
                $improvements['images'][] = "Add descriptive alt text to images";
            }

            // Check for responsive images
            if (strpos($img, 'srcset') === false) {
                $improvements['images'][] = "Use responsive images with srcset";
            }
        }
    }

    /**
     * Check content readability
     */
    private function check_readability($html, &$improvements) {
        $text = strip_tags($html);
        
        // Check paragraph length
        $paragraphs = explode("\n\n", $text);
        foreach ($paragraphs as $paragraph) {
            if (str_word_count($paragraph) > 100) {
                $improvements['readability'][] = "Break up long paragraphs for better readability";
                break;
            }
        }

        // Check sentence length
        $sentences = preg_split('/[.!?]+/', $text);
        foreach ($sentences as $sentence) {
            if (str_word_count($sentence) > 25) {
                $improvements['readability'][] = "Consider shortening complex sentences";
                break;
            }
        }
    }

    /**
     * Check content structure
     */
    private function check_structure($html, &$improvements) {
        // Check heading hierarchy
        $hierarchy = $this->analyze_hierarchy($html);
        if (!isset($hierarchy['h1']) || $hierarchy['h1'] > 1) {
            $improvements['structure'][] = "Use exactly one H1 heading per page";
        }
        
        // Check for skip levels in headings
        $prev_level = 1;
        for ($i = 2; $i <= 6; $i++) {
            if (isset($hierarchy["h{$i}"]) && !isset($hierarchy["h" . ($i-1)])) {
                $improvements['structure'][] = "Don't skip heading levels";
                break;
            }
        }
    }

    /**
     * Suggest group blocks for related content
     */
    private function suggest_group_blocks($html, &$suggestions) {
        // Find potential content groups
        preg_match_all('/<div[^>]*class="([^"]*)"[^>]*>.*?<\/div>/is', $html, $matches);
        
        foreach ($matches[1] as $i => $class) {
            if (strpos($class, 'section') !== false || 
                strpos($class, 'group') !== false ||
                strpos($class, 'container') !== false) {
                $suggestions[] = [
                    'type' => 'group',
                    'block' => 'core/group',
                    'original_class' => $class,
                    'content' => substr($matches[0][$i], 0, 100) . '...' // Preview
                ];
            }
        }
    }

    /**
     * Analyze SEO aspects
     */
    private function analyze_seo($html) {
        $seo = [
            'meta' => $this->check_meta_tags($html),
            'headings' => $this->check_heading_structure($html),
            'links' => $this->check_links($html),
            'suggestions' => []
        ];

        // Generate SEO suggestions
        if (!$seo['meta']['has_description']) {
            $seo['suggestions'][] = "Add meta description";
        }
        if (!$seo['meta']['has_title']) {
            $seo['suggestions'][] = "Add title tag";
        }
        if ($seo['links']['broken'] > 0) {
            $seo['suggestions'][] = "Fix broken links";
        }

        return $seo;
    }

    /**
     * Check meta tags
     */
    private function check_meta_tags($html) {
        return [
            'has_title' => strpos($html, '<title') !== false,
            'has_description' => strpos($html, 'meta name="description"') !== false,
            'has_keywords' => strpos($html, 'meta name="keywords"') !== false
        ];
    }

    /**
     * Check heading structure
     */
    private function check_heading_structure($html) {
        $headings = [];
        for ($i = 1; $i <= 6; $i++) {
            preg_match_all("/<h{$i}.*?>(.*?)<\/h{$i}>/is", $html, $matches);
            $headings["h{$i}"] = $matches[1];
        }
        return $headings;
    }

    /**
     * Check links
     */
    private function check_links($html) {
        preg_match_all('/<a[^>]+href=([\'"])(.*?)\1/i', $html, $matches);
        
        return [
            'total' => count($matches[0]),
            'external' => preg_grep('/^https?:\/\//i', $matches[2]),
            'internal' => preg_grep('/^(?!https?:\/\/)/i', $matches[2]),
            'broken' => 0 // Would need actual HTTP checks
        ];
    }
}
