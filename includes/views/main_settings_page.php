<div class="wrap">
    <h2>Dynamic Pages Creator</h2>
    <p>Use this page to create dynamic pages with SEO meta tags based on page keywords.</p>
    <?php settings_errors('dynamic_pages_creator_options'); ?>
    <form method="post" action="options.php">
        <?php
        settings_fields('dynamic_pages_creator_options');
        do_settings_sections('dynamic-pages-creator');
        submit_button('Create Pages');
        ?>
    </form>
    <!-- Progress bar and text for batch processing -->
    <div id="progress" style="display:none;">Processing...</div> <!-- Progress Indicator -->
    <div id="batch-status"></div>
    <div id="batch-process-controls" style="display: none;">
        <progress id="progress-bar" value="0" max="100" style="width: 100%;"></progress>
        <span id="progress-text">0%</span>
    </div>
</div>