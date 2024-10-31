<div class="wrap">
    <h1><?php esc_html_e('Pacific Gateway Settings', 'pacific_gateway_plugin') ?></h1>
    <form method="post" action="options.php">
        <?php
        // This prints out all hidden setting fields
        settings_fields('pacific_gateway_plugin_settings_group');
        do_settings_sections('pacific-gateway-admin');
        submit_button();
        ?>
    </form>
</div>