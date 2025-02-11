<?php
class AIAlbieLayoutAnalyzer {
    private $content_types = [];
    private $layout_patterns = [];
    
    public function __construct() {
        $this->initialize_content_types();
        $this->initialize_layout_patterns();
    }

    /**
     * Initialize content type detection rules
     */
    private function initialize_content_types() {
        $this->content_types = [
            'historical' => [
                'keywords' => ['history', 'historical', 'vintage', 'classic', 'era', 'period', 'ancient', 'traditional'],
                'patterns' => [
                    'dates' => '/\b\d{4}s?\b|\b\d{1,2}(st|nd|rd|th) century\b/i',
                    'historical_terms' => '/\b(war|battle|empire|dynasty|kingdom|civilization)\b/i'
                ]
            ],
            'technical' => [
                'keywords' => ['specifications', 'technical', 'engineering', 'performance', 'system', 'design'],
                'patterns' => [
                    'measurements' => '/\b\d+(\.\d+)?\s*(mm|cm|m|kg|mph|km\/h)\b/i',
                    'technical_terms' => '/\b(engine|power|capacity|speed|efficiency|output)\b/i'
                ]
            ],
            'gallery' => [
                'patterns' => [
                    'image_density' => 'high_image_ratio',
                    'gallery_elements' => '/<div[^>]*class="[^"]*gallery[^"]*"[^>]*>/',
                    'image_groups' => '/<img[^>]+>(?:\s*<img[^>]+>){2,}/'
                ]
            ],
            'article' => [
                'patterns' => [
                    'text_density' => 'high_text_ratio',
                    'article_structure' => '/<article|section|main[^>]*>.*?<\/article|section|main>/is',
                    'heading_hierarchy' => '/<h[1-6][^>]*>.*?<\/h[1-6]>/is'
                ]
            ],
            'showcase' => [
                'patterns' => [
                    'feature_sections' => '/<div[^>]*class="[^"]*feature[^"]*"[^>]*>/',
                    'hero_elements' => '/<div[^>]*class="[^"]*hero[^"]*"[^>]*>/',
                    'showcase_structure' => '/<div[^>]*class="[^"]*showcase[^"]*"[^>]*>/'
                ]
            ]
        ];
    }

    /**
     * Initialize layout pattern detection
     */
    private function initialize_layout_patterns() {
        $this->layout_patterns = [
            'timeline' => [
                'structure' => [
                    'chronological_order' => '/\b(before|after|during|since|until)\b/i',
                    'date_sequences' => '/\b\d{4}\b.*?\b\d{4}\b/',
                    'timeline_elements' => '/<div[^>]*class="[^"]*timeline[^"]*"[^>]*>/'
                ],
                'indicators' => [
                    'date_prominence' => 'high_date_density',
                    'sequential_content' => 'ordered_sections'
                ]
            ],
            'grid' => [
                'structure' => [
                    'grid_classes' => '/<div[^>]*class="[^"]*grid[^"]*"[^>]*>/',
                    'column_layout' => '/<div[^>]*class="[^"]*col[^"]*"[^>]*>/',
                    'card_elements' => '/<div[^>]*class="[^"]*card[^"]*"[^>]*>/'
                ],
                'indicators' => [
                    'repeated_elements' => 'similar_sections',
                    'grid_structure' => 'grid_layout'
                ]
            ],
            'comparison' => [
                'structure' => [
                    'table_elements' => '/<table[^>]*>/',
                    'comparison_classes' => '/<div[^>]*class="[^"]*compare[^"]*"[^>]*>/',
                    'spec_lists' => '/<dl[^>]*>.*?<\/dl>/is'
                ],
                'indicators' => [
                    'comparative_terms' => '/\b(vs\.|versus|compared to|better than)\b/i',
                    'spec_tables' => 'technical_comparison'
                ]
            ],
            'article' => [
                'structure' => [
                    'article_element' => '/<article[^>]*>/',
                    'content_hierarchy' => '/<main[^>]*>.*?<\/main>/is',
                    'section_breaks' => '/<hr[^>]*>/'
                ],
                'indicators' => [
                    'text_density' => 'high_text_ratio',
                    'heading_structure' => 'proper_hierarchy'
                ]
            ]
        ];
    }

    /**
     * Analyze content and suggest layout
     */
    public function analyze_content($html_content) {
        $analysis = [
            'content_types' => $this->detect_content_types($html_content),
            'layout_matches' => $this->detect_layouts($html_content),
            'structure_score' => $this->analyze_structure($html_content),
            'recommendations' => []
        ];

        // Generate recommendations based on analysis
        $analysis['recommendations'] = $this->generate_recommendations($analysis);

        return $analysis;
    }

    /**
     * Detect content types in HTML
     */
    private function detect_content_types($html) {
        $detected = [];
        
        foreach ($this->content_types as $type => $rules) {
            $score = 0;
            
            // Check keywords
            if (isset($rules['keywords'])) {
                foreach ($rules['keywords'] as $keyword) {
                    if (stripos($html, $keyword) !== false) {
                        $score += 1;
                    }
                }
            }
            
            // Check patterns
            if (isset($rules['patterns'])) {
                foreach ($rules['patterns'] as $pattern) {
                    if (is_string($pattern) && preg_match($pattern, $html)) {
                        $score += 2;
                    }
                }
            }
            
            if ($score > 0) {
                $detected[$type] = [
                    'score' => $score,
                    'confidence' => $this->calculate_confidence($score)
                ];
            }
        }
        
        return $detected;
    }

    /**
     * Detect suitable layouts
     */
    private function detect_layouts($html) {
        $matches = [];
        
        foreach ($this->layout_patterns as $layout => $patterns) {
            $score = 0;
            
            // Check structure patterns
            foreach ($patterns['structure'] as $pattern) {
                if (is_string($pattern) && preg_match($pattern, $html)) {
                    $score += 2;
                }
            }
            
            // Check indicators
            foreach ($patterns['indicators'] as $indicator => $pattern) {
                if (is_string($pattern) && preg_match($pattern, $html)) {
                    $score += 1;
                }
            }
            
            if ($score > 0) {
                $matches[$layout] = [
                    'score' => $score,
                    'confidence' => $this->calculate_confidence($score)
                ];
            }
        }
        
        return $matches;
    }

    /**
     * Analyze content structure
     */
    private function analyze_structure($html) {
        $structure = [
            'heading_hierarchy' => $this->analyze_heading_hierarchy($html),
            'content_sections' => $this->analyze_content_sections($html),
            'navigation_elements' => $this->analyze_navigation($html),
            'media_distribution' => $this->analyze_media_distribution($html)
        ];

        return $this->calculate_structure_score($structure);
    }

    /**
     * Analyze heading hierarchy
     */
    private function analyze_heading_hierarchy($html) {
        $headings = [];
        for ($i = 1; $i <= 6; $i++) {
            preg_match_all("/<h{$i}[^>]*>(.*?)<\/h{$i}>/is", $html, $matches);
            $headings["h{$i}"] = count($matches[0]);
        }
        
        return [
            'counts' => $headings,
            'proper_order' => $this->check_heading_order($headings)
        ];
    }

    /**
     * Check if headings are in proper order
     */
    private function check_heading_order($headings) {
        $last_level = 0;
        $proper_order = true;
        
        for ($i = 1; $i <= 6; $i++) {
            if ($headings["h{$i}"] > 0) {
                if ($last_level > 0 && $i - $last_level > 1) {
                    $proper_order = false;
                    break;
                }
                $last_level = $i;
            }
        }
        
        return $proper_order;
    }

    /**
     * Analyze content sections
     */
    private function analyze_content_sections($html) {
        preg_match_all('/<(article|section|div)[^>]*class="[^"]*"[^>]*>/i', $html, $matches);
        
        return [
            'count' => count($matches[0]),
            'types' => array_count_values($matches[1])
        ];
    }

    /**
     * Analyze navigation elements
     */
    private function analyze_navigation($html) {
        return [
            'has_nav' => preg_match('/<nav[^>]*>/i', $html) === 1,
            'menu_items' => preg_match_all('/<li[^>]*>/i', $html),
            'links' => preg_match_all('/<a[^>]*>/i', $html)
        ];
    }

    /**
     * Analyze media distribution
     */
    private function analyze_media_distribution($html) {
        return [
            'images' => preg_match_all('/<img[^>]*>/i', $html),
            'videos' => preg_match_all('/<video[^>]*>/i', $html),
            'galleries' => preg_match_all('/<div[^>]*class="[^"]*gallery[^"]*"[^>]*>/i', $html)
        ];
    }

    /**
     * Calculate structure score
     */
    private function calculate_structure_score($structure) {
        $score = 0;
        
        // Score heading hierarchy
        if ($structure['heading_hierarchy']['proper_order']) {
            $score += 2;
        }
        
        // Score content sections
        if ($structure['content_sections']['count'] > 0) {
            $score += min(3, floor($structure['content_sections']['count'] / 5));
        }
        
        // Score navigation
        if ($structure['navigation_elements']['has_nav']) {
            $score += 1;
        }
        
        // Score media distribution
        $media_count = array_sum($structure['media_distribution']);
        if ($media_count > 0) {
            $score += min(2, floor($media_count / 10));
        }
        
        return [
            'score' => $score,
            'max_score' => 8,
            'percentage' => ($score / 8) * 100
        ];
    }

    /**
     * Calculate confidence score
     */
    private function calculate_confidence($score) {
        $max_score = 10;
        $confidence = ($score / $max_score) * 100;
        return min(100, $confidence);
    }

    /**
     * Generate layout recommendations
     */
    private function generate_recommendations($analysis) {
        $recommendations = [];
        
        // Sort content types by score
        arsort($analysis['content_types']);
        
        // Sort layouts by score
        arsort($analysis['layout_matches']);
        
        // Get top content type
        $primary_content_type = key($analysis['content_types']);
        
        // Get top layout match
        $primary_layout = key($analysis['layout_matches']);
        
        // Generate primary recommendation
        $recommendations[] = [
            'template' => $this->get_template_recommendation($primary_content_type),
            'layout' => $primary_layout,
            'confidence' => $analysis['content_types'][$primary_content_type]['confidence'],
            'reasons' => $this->get_recommendation_reasons($primary_content_type, $primary_layout)
        ];
        
        // Generate alternative recommendations
        $alternatives = array_slice($analysis['layout_matches'], 1, 2, true);
        foreach ($alternatives as $layout => $score) {
            $recommendations[] = [
                'template' => $this->get_template_recommendation($primary_content_type, $layout),
                'layout' => $layout,
                'confidence' => $score['confidence'],
                'reasons' => $this->get_recommendation_reasons($primary_content_type, $layout)
            ];
        }
        
        return $recommendations;
    }

    /**
     * Get template recommendation based on content type
     */
    private function get_template_recommendation($content_type, $layout = null) {
        $templates = [
            'historical' => 'historic-modern',
            'technical' => 'aviation-tech',
            'gallery' => 'historic-modern',
            'article' => 'aviation-tech',
            'showcase' => 'aviation-tech'
        ];
        
        return $templates[$content_type] ?? 'historic-modern';
    }

    /**
     * Get reasons for recommendation
     */
    private function get_recommendation_reasons($content_type, $layout) {
        $reasons = [];
        
        // Content type specific reasons
        $type_reasons = [
            'historical' => [
                'Optimized for historical content presentation',
                'Includes timeline and artifact display features',
                'Preserves historical context and chronology'
            ],
            'technical' => [
                'Designed for technical specifications',
                'Includes comparison and specification tables',
                'Optimized for technical documentation'
            ],
            'gallery' => [
                'Enhanced gallery and showcase features',
                'Optimized for visual content display',
                'Includes lightbox and zoom capabilities'
            ],
            'article' => [
                'Clean and readable article layout',
                'Optimized for long-form content',
                'Includes table of contents feature'
            ],
            'showcase' => [
                'Designed for feature highlighting',
                'Includes showcase and spotlight sections',
                'Optimized for promotional content'
            ]
        ];
        
        // Layout specific reasons
        $layout_reasons = [
            'timeline' => [
                'Chronological content organization',
                'Visual timeline navigation',
                'Date-based content filtering'
            ],
            'grid' => [
                'Organized grid layout',
                'Responsive card-based design',
                'Filterable grid categories'
            ],
            'comparison' => [
                'Side-by-side comparison layout',
                'Specification table format',
                'Feature comparison highlights'
            ],
            'article' => [
                'Clear content hierarchy',
                'Enhanced readability',
                'Proper section organization'
            ]
        ];
        
        // Add content type reasons
        if (isset($type_reasons[$content_type])) {
            $reasons = array_merge($reasons, $type_reasons[$content_type]);
        }
        
        // Add layout reasons
        if (isset($layout_reasons[$layout])) {
            $reasons = array_merge($reasons, $layout_reasons[$layout]);
        }
        
        return array_slice($reasons, 0, 5); // Return top 5 reasons
    }
}
