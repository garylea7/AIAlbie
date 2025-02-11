<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header class="site-header">
        <?php if(has_custom_header()): ?>
            <img src="<?php header_image(); ?>" class="header-image" alt="<?php bloginfo('name'); ?>">
        <?php endif; ?>
        
        <nav class="main-navigation">
            <?php wp_nav_menu(array('theme_location' => 'primary')); ?>
        </nav>
    </header>
    
    <div class="site-content">
