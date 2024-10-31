<input
    type="text"
    id="<?php echo esc_attr($id ?? "") ?>"
    name="pacific_gateway_plugin_settings[<?php echo esc_attr($id ?? "") ?>]"
    value="<?php echo esc_attr($options[$id] ?? '') ?>"
/>