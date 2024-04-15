<div class="wrap">
    <h2>Dynamic Pages Creator</h2>
    <p>Use this page to create dynamic pages with SEO meta tags based on page titles.</p>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php
        settings_fields('dynamic_pages_creator_options');
        do_settings_sections('dynamic-pages-creator');
        submit_button('Create Pages');
        ?>
    </form>
</div>