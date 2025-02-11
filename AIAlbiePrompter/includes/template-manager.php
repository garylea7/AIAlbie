<?php
class AIAlbieTemplateManager {
    private $templates = [];
    private $current_template;
    
    public function __construct() {
        $this->load_templates();
    }

    /**
     * Load available templates
     */
    private function load_templates() {
        $this->templates = [
            'historic-modern' => [
                'id' => 'historic-modern',
                'name' => 'Historic Modern',
                'description' => 'Perfect for historical content with a modern touch',
                'preview_image' => 'templates/historic-modern/preview.jpg',
                'features' => [
                    'timeline-layout',
                    'gallery-showcase',
                    'artifact-display',
                    'historical-navigation'
                ],
                'layouts' => [
                    'timeline' => [
                        'name' => 'Timeline View',
                        'description' => 'Display content in chronological order',
                        'preview' => 'templates/historic-modern/timeline.jpg'
                    ],
                    'gallery' => [
                        'name' => 'Gallery View',
                        'description' => 'Showcase historical images and artifacts',
                        'preview' => 'templates/historic-modern/gallery.jpg'
                    ],
                    'article' => [
                        'name' => 'Article View',
                        'description' => 'Detailed historical articles and research',
                        'preview' => 'templates/historic-modern/article.jpg'
                    ]
                ],
                'styles' => [
                    'colors' => [
                        'primary' => '#2C3E50',
                        'secondary' => '#E74C3C',
                        'background' => '#ECF0F1',
                        'text' => '#2C3E50'
                    ],
                    'fonts' => [
                        'heading' => 'Playfair Display',
                        'body' => 'Source Sans Pro'
                    ],
                    'spacing' => [
                        'content' => '1.6em',
                        'sections' => '4em'
                    ]
                ],
                'blocks' => [
                    'timeline' => [
                        'name' => 'Historical Timeline',
                        'icon' => 'calendar',
                        'template' => 'templates/blocks/timeline.php'
                    ],
                    'artifact' => [
                        'name' => 'Artifact Display',
                        'icon' => 'archive',
                        'template' => 'templates/blocks/artifact.php'
                    ],
                    'gallery' => [
                        'name' => 'Historical Gallery',
                        'icon' => 'format-gallery',
                        'template' => 'templates/blocks/gallery.php'
                    ]
                ]
            ],
            'aviation-tech' => [
                'id' => 'aviation-tech',
                'name' => 'Aviation Technology',
                'description' => 'Modern design for aviation and technology content',
                'preview_image' => 'templates/aviation-tech/preview.jpg',
                'features' => [
                    'technical-specs',
                    'aircraft-showcase',
                    'comparison-tables',
                    'technical-diagrams'
                ],
                'layouts' => [
                    'specs' => [
                        'name' => 'Technical Specifications',
                        'description' => 'Display detailed aircraft specifications',
                        'preview' => 'templates/aviation-tech/specs.jpg'
                    ],
                    'comparison' => [
                        'name' => 'Aircraft Comparison',
                        'description' => 'Compare different aircraft models',
                        'preview' => 'templates/aviation-tech/comparison.jpg'
                    ],
                    'showcase' => [
                        'name' => 'Aircraft Showcase',
                        'description' => 'Feature specific aircraft models',
                        'preview' => 'templates/aviation-tech/showcase.jpg'
                    ]
                ],
                'styles' => [
                    'colors' => [
                        'primary' => '#1E88E5',
                        'secondary' => '#FFC107',
                        'background' => '#FAFAFA',
                        'text' => '#212121'
                    ],
                    'fonts' => [
                        'heading' => 'Roboto',
                        'body' => 'Open Sans'
                    ],
                    'spacing' => [
                        'content' => '1.5em',
                        'sections' => '3em'
                    ]
                ],
                'blocks' => [
                    'specs' => [
                        'name' => 'Aircraft Specifications',
                        'icon' => 'clipboard',
                        'template' => 'templates/blocks/specs.php'
                    ],
                    'comparison' => [
                        'name' => 'Aircraft Comparison',
                        'icon' => 'columns',
                        'template' => 'templates/blocks/comparison.php'
                    ],
                    'showcase' => [
                        'name' => 'Aircraft Showcase',
                        'icon' => 'visibility',
                        'template' => 'templates/blocks/showcase.php'
                    ]
                ]
            ]
        ];
    }

    /**
     * Get all available templates
     */
    public function get_templates() {
        return $this->templates;
    }

    /**
     * Get template by ID
     */
    public function get_template($template_id) {
        return $this->templates[$template_id] ?? null;
    }

    /**
     * Apply template to current site
     */
    public function apply_template($template_id, $customizations = []) {
        $template = $this->get_template($template_id);
        if (!$template) {
            throw new Exception("Template not found: {$template_id}");
        }

        // Merge customizations with default template settings
        $settings = $this->merge_settings($template, $customizations);

        // Apply template settings
        $this->apply_styles($settings['styles']);
        $this->register_blocks($template['blocks']);
        $this->setup_layouts($template['layouts']);

        $this->current_template = $template_id;
        
        return [
            'success' => true,
            'template' => $template_id,
            'settings' => $settings
        ];
    }

    /**
     * Merge custom settings with template defaults
     */
    private function merge_settings($template, $customizations) {
        return array_merge_recursive($template, $customizations);
    }

    /**
     * Apply template styles
     */
    private function apply_styles($styles) {
        // Generate and enqueue custom CSS
        add_action('wp_enqueue_scripts', function() use ($styles) {
            $css = $this->generate_custom_css($styles);
            wp_add_inline_style('theme-styles', $css);
        });
    }

    /**
     * Register template-specific blocks
     */
    private function register_blocks($blocks) {
        add_action('init', function() use ($blocks) {
            foreach ($blocks as $block_name => $block) {
                register_block_type("aialbie/{$block_name}", [
                    'editor_script' => 'aialbie-blocks',
                    'editor_style' => 'aialbie-blocks-editor',
                    'style' => 'aialbie-blocks-style',
                    'render_callback' => [$this, "render_{$block_name}_block"]
                ]);
            }
        });
    }

    /**
     * Setup template layouts
     */
    private function setup_layouts($layouts) {
        // Register layout templates
        add_action('after_setup_theme', function() use ($layouts) {
            foreach ($layouts as $layout_name => $layout) {
                add_theme_support('aialbie-layout', [
                    'name' => $layout_name,
                    'template' => $layout['template']
                ]);
            }
        });
    }

    /**
     * Generate custom CSS from template styles
     */
    private function generate_custom_css($styles) {
        $css = '';
        
        // Colors
        foreach ($styles['colors'] as $name => $color) {
            $css .= "--color-{$name}: {$color};\n";
        }
        
        // Fonts
        $css .= "body { font-family: {$styles['fonts']['body']}; }\n";
        $css .= "h1, h2, h3, h4, h5, h6 { font-family: {$styles['fonts']['heading']}; }\n";
        
        // Spacing
        $css .= "p { line-height: {$styles['spacing']['content']}; }\n";
        $css .= "section { margin-bottom: {$styles['spacing']['sections']}; }\n";
        
        return $css;
    }

    /**
     * Preview template
     */
    public function preview_template($template_id, $content = '') {
        $template = $this->get_template($template_id);
        if (!$template) {
            throw new Exception("Template not found: {$template_id}");
        }

        // Generate preview HTML
        ob_start();
        include $template['preview_template'];
        $preview = ob_get_clean();

        return [
            'html' => $preview,
            'styles' => $template['styles'],
            'layouts' => $template['layouts']
        ];
    }

    /**
     * Get template customization options
     */
    public function get_customization_options($template_id) {
        $template = $this->get_template($template_id);
        if (!$template) {
            throw new Exception("Template not found: {$template_id}");
        }

        return [
            'colors' => [
                'primary' => [
                    'label' => 'Primary Color',
                    'type' => 'color',
                    'default' => $template['styles']['colors']['primary']
                ],
                'secondary' => [
                    'label' => 'Secondary Color',
                    'type' => 'color',
                    'default' => $template['styles']['colors']['secondary']
                ],
                'background' => [
                    'label' => 'Background Color',
                    'type' => 'color',
                    'default' => $template['styles']['colors']['background']
                ]
            ],
            'fonts' => [
                'heading' => [
                    'label' => 'Heading Font',
                    'type' => 'font',
                    'default' => $template['styles']['fonts']['heading']
                ],
                'body' => [
                    'label' => 'Body Font',
                    'type' => 'font',
                    'default' => $template['styles']['fonts']['body']
                ]
            ],
            'layouts' => array_map(function($layout) {
                return [
                    'label' => $layout['name'],
                    'description' => $layout['description'],
                    'preview' => $layout['preview']
                ];
            }, $template['layouts'])
        ];
    }
}
