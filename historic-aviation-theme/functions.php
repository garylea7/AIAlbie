<?php
function historic_aviation_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'historic-aviation'),
        'footer' => __('Footer Menu', 'historic-aviation')
    ));
}
add_action('after_setup_theme', 'historic_aviation_setup');

// Add custom block patterns
function historic_aviation_register_block_patterns() {
    register_block_pattern(
        'historic-aviation/content-with-image',
        array(
            'title' => __('Historic Content with Image', 'historic-aviation'),
            'categories' => array('text'),
            'content' => '<!-- wp:group {"className":"historic-content-block"} -->
                         <div class="wp-block-group historic-content-block">
                         <!-- wp:image {"className":"historic-image"} -->
                         <figure class="wp-block-image historic-image"><img src="" alt=""/></figure>
                         <!-- /wp:image -->
                         <!-- wp:paragraph {"className":"historic-text"} -->
                         <p class="historic-text"></p>
                         <!-- /wp:paragraph -->
                         </div>
                         <!-- /wp:group -->'
        )
    );
}
add_action('init', 'historic_aviation_register_block_patterns');

// Add custom block styles
function historic_aviation_register_block_styles() {
    register_block_style(
        'core/paragraph',
        array(
            'name' => 'historic-text',
            'label' => __('Historic Text', 'historic-aviation'),
        )
    );
    
    register_block_style(
        'core/image',
        array(
            'name' => 'historic-image',
            'label' => __('Historic Image', 'historic-aviation'),
        )
    );
}
add_action('init', 'historic_aviation_register_block_styles');

// Add custom category for historic pages
function historic_aviation_register_taxonomies() {
    register_taxonomy(
        'historic_category',
        'page',
        array(
            'labels' => array(
                'name' => __('Historic Categories', 'historic-aviation'),
                'singular_name' => __('Historic Category', 'historic-aviation'),
            ),
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'historic_aviation_register_taxonomies');
