<?php if (preg_match('/\[(.*?)\]/', $id, $matches)) : ?>
    <?php
        $firstKey = str_replace($matches[0], '', $id);
        $secondKey = $matches[1];
    ?>
    <select name="pacific_gateway_plugin_settings[<?php echo esc_attr($firstKey) ?>][<?php echo esc_attr($secondKey) ?>]"  id="<?php echo esc_attr($id ?? "") ?>">
        <option value="">-</option>
        <?php foreach($selectOptions as $value => $name) : ?>
            <?php if(isset($options[$firstKey][$secondKey]) && $options[$firstKey][$secondKey] === $value) : ?>
                <option value="<?php echo esc_attr($value) ?>" selected="selected"><?php echo esc_html($name) ?></option>
            <?php else: ?>
                <option value="<?php echo esc_attr($value) ?>"><?php echo esc_html($name) ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
<?php else : ?>
    <select name="pacific_gateway_plugin_settings[<?php echo esc_attr($id ?? "") ?>]"  id="<?= $id ?? "" ?>">
        <option value="">-</option>
        <?php foreach($selectOptions as $value => $name) : ?>
            <?php if(isset($options[$id]) && $options[$id] == $value) : ?>
                <option value="<?php echo esc_attr($value) ?>" selected="selected"><?php echo esc_html($name) ?></option>
            <?php else: ?>
                <option value="<?php echo esc_attr($value) ?>"><?php echo esc_html($name) ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
<?php endif; ?>
    </select>
