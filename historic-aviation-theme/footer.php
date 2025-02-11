    </div><!-- .site-content -->
    
    <footer class="site-footer">
        <nav class="footer-navigation">
            <?php wp_nav_menu(array('theme_location' => 'footer')); ?>
        </nav>
        <div class="site-info">
            <?php echo sprintf('© %s Historic Aviation Military', date('Y')); ?>
        </div>
    </footer>
    
    <?php wp_footer(); ?>
</body>
</html>
