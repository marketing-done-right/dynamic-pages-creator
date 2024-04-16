<div class="wrap">
    <h2>SEO Settings for Dynamic Pages</h2>
    <?php settings_errors('dynamic_pages_creator_seo_settings'); ?>
    <form method="post" action="options.php">
        <?php
        settings_fields('dynamic_pages_creator_seo_settings');
        do_settings_sections('dynamic_pages_creator_seo_settings');
        submit_button();
        ?>
    </form>
</div>