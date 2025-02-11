<?php
class AIAlbieTemplateAnalyzer {
    private $content_types = [
        'blog' => ['posts', 'articles', 'news'],
        'portfolio' => ['gallery', 'projects', 'work'],
        'business' => ['services', 'about', 'contact'],
        'ecommerce' => ['products', 'shop', 'store'],
        'educational' => ['courses', 'lessons', 'resources'],
        'historical' => ['timeline', 'artifacts', 'exhibits']
    ];

    private $layout_patterns = [
        'grid' => ['gallery', 'cards', 'portfolio'],
        'timeline' => ['chronological', 'history', 'events'],
        'catalog' => ['products', 'items', 'listings'],
        'blog' => ['posts', 'articles', 'news'],
        'showcase' => ['features', 'highlights', 'spotlight']
    ];

    /**
     * Analyze website content and suggest best templates
     */
    public function analyze_site($url, $user_intent) {
        $site_data = $this->fetch_site_data($url);
        $analysis = [
            'content_type' => $this->detect_content_type($site_data, $user_intent),
            'layout_needs' => $this->analyze_layout_needs($site_data),
            'style_preferences' => $this->extract_style_preferences($site_data),
            'content_structure' => $this->analyze_content_structure($site_data)
        ];

        return $this->get_template_recommendations($analysis);
    }

    /**
     * Detect the primary type of content
     */
    private function detect_content_type($site_data, $user_intent) {
        $scores = [];
        
        // Score based on content analysis
        foreach ($this->content_types as $type => $keywords) {
            $scores[$type] = 0;
            foreach ($keywords as $keyword) {
                $count = substr_count(strtolower($site_data['content']), $keyword);
                $scores[$type] += $count;
            }
        }

        // Factor in user intent
        $intent_lower = strtolower($user_intent);
        foreach ($this->content_types as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($intent_lower, $keyword) !== false) {
                    $scores[$type] += 5; // Extra weight for user intent
                }
            }
        }

        // Get top content types
        arsort($scores);
        return array_slice($scores, 0, 3, true);
    }

    /**
     * Analyze layout requirements
     */
    private function analyze_layout_needs($site_data) {
        $layout_scores = [];
        
        // Analyze HTML structure
        foreach ($this->layout_patterns as $pattern => $indicators) {
            $layout_scores[$pattern] = 0;
            
            // Check for structural elements
            if (strpos($site_data['html'], '<div class="gallery"') !== false) {
                $layout_scores['grid'] += 3;
            }
            if (strpos($site_data['html'], '<div class="timeline"') !== false) {
                $layout_scores['timeline'] += 3;
            }
            
            // Check for common patterns
            foreach ($indicators as $indicator) {
                if (strpos($site_data['html'], $indicator) !== false) {
                    $layout_scores[$pattern]++;
                }
            }
        }

        return $layout_scores;
    }

    /**
     * Extract style preferences from existing site
     */
    private function extract_style_preferences($site_data) {
        $preferences = [
            'colors' => $this->extract_colors($site_data['css']),
            'fonts' => $this->extract_fonts($site_data['css']),
            'spacing' => $this->analyze_spacing($site_data['css']),
            'imagery' => $this->analyze_imagery($site_data)
        ];

        return $preferences;
    }

    /**
     * Analyze content structure and hierarchy
     */
    private function analyze_content_structure($site_data) {
        return [
            'hierarchy_depth' => $this->analyze_hierarchy_depth($site_data['html']),
            'section_types' => $this->identify_section_types($site_data['html']),
            'navigation_structure' => $this->analyze_navigation($site_data['html'])
        ];
    }

    /**
     * Get template recommendations based on analysis
     */
    private function get_template_recommendations($analysis) {
        $available_templates = $this->get_available_templates();
        $recommendations = [];

        foreach ($available_templates as $template) {
            $score = $this->calculate_template_match($template, $analysis);
            $recommendations[$template['id']] = [
                'template' => $template,
                'score' => $score,
                'reasons' => $this->get_match_reasons($template, $analysis)
            ];
        }

        // Sort by score
        uasort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($recommendations, 0, 5, true);
    }

    /**
     * Calculate how well a template matches the analysis
     */
    private function calculate_template_match($template, $analysis) {
        $score = 0;

        // Content type match
        foreach ($analysis['content_type'] as $type => $type_score) {
            if (in_array($type, $template['suitable_for'])) {
                $score += $type_score * 2;
            }
        }

        // Layout match
        foreach ($analysis['layout_needs'] as $layout => $layout_score) {
            if (in_array($layout, $template['layouts'])) {
                $score += $layout_score * 1.5;
            }
        }

        // Style match
        if ($this->styles_compatible($template['style'], $analysis['style_preferences'])) {
            $score += 10;
        }

        // Structure match
        if ($this->structure_compatible($template['structure'], $analysis['content_structure'])) {
            $score += 15;
        }

        return $score;
    }

    /**
     * Get specific reasons why a template matches
     */
    private function get_match_reasons($template, $analysis) {
        $reasons = [];

        // Content type reasons
        foreach ($analysis['content_type'] as $type => $score) {
            if (in_array($type, $template['suitable_for'])) {
                $reasons[] = "Perfect for {$type} content";
            }
        }

        // Layout reasons
        foreach ($analysis['layout_needs'] as $layout => $score) {
            if (in_array($layout, $template['layouts'])) {
                $reasons[] = "Supports {$layout} layout";
            }
        }

        // Style reasons
        if ($this->styles_compatible($template['style'], $analysis['style_preferences'])) {
            $reasons[] = "Matches your current style preferences";
        }

        // Structure reasons
        if ($this->structure_compatible($template['structure'], $analysis['content_structure'])) {
            $reasons[] = "Compatible with your content structure";
        }

        return $reasons;
    }

    /**
     * Helper functions for style and structure analysis
     */
    private function extract_colors($css) {
        preg_match_all('/#([a-f0-9]{3}){1,2}\b/i', $css, $matches);
        return array_unique($matches[0]);
    }

    private function extract_fonts($css) {
        preg_match_all('/font-family:\s*([^;]+);/i', $css, $matches);
        return array_unique($matches[1]);
    }

    private function analyze_spacing($css) {
        // Analyze margins, padding patterns
        return [
            'compact' => false,
            'spacious' => false,
            'balanced' => true
        ];
    }

    private function analyze_imagery($site_data) {
        return [
            'uses_hero' => strpos($site_data['html'], 'hero') !== false,
            'image_heavy' => substr_count($site_data['html'], '<img') > 10,
            'has_slider' => strpos($site_data['html'], 'slider') !== false
        ];
    }

    private function analyze_hierarchy_depth($html) {
        // Count heading levels used
        $depth = 1;
        for ($i = 1; $i <= 6; $i++) {
            if (strpos($html, "<h{$i}") !== false) {
                $depth = $i;
            }
        }
        return $depth;
    }

    private function identify_section_types($html) {
        $sections = [];
        if (strpos($html, 'header') !== false) $sections[] = 'header';
        if (strpos($html, 'footer') !== false) $sections[] = 'footer';
        if (strpos($html, 'sidebar') !== false) $sections[] = 'sidebar';
        if (strpos($html, 'nav') !== false) $sections[] = 'navigation';
        return $sections;
    }

    private function analyze_navigation($html) {
        return [
            'has_main_menu' => strpos($html, 'main-menu') !== false,
            'has_footer_menu' => strpos($html, 'footer-menu') !== false,
            'has_sidebar_menu' => strpos($html, 'sidebar-menu') !== false
        ];
    }

    private function styles_compatible($template_style, $preferences) {
        // Check if template can adapt to user's style preferences
        return true; // Simplified for example
    }

    private function structure_compatible($template_structure, $content_structure) {
        // Check if template can handle the content structure
        return true; // Simplified for example
    }

    /**
     * Get available templates from the system
     */
    private function get_available_templates() {
        // This would normally fetch from a database or file system
        return [
            [
                'id' => 'historic-modern',
                'name' => 'Historic Modern',
                'suitable_for' => ['historical', 'educational'],
                'layouts' => ['timeline', 'grid'],
                'style' => 'modern',
                'structure' => 'flexible'
            ],
            // Add more templates...
        ];
    }

    /**
     * Fetch site data from URL
     */
    private function fetch_site_data($url) {
        // This would normally use proper HTTP requests
        return [
            'html' => '',
            'css' => '',
            'content' => ''
        ];
    }
}
