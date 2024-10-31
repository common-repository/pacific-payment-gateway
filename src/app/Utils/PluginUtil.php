<?php

namespace Pacific\GatewayWordpress\App\Utils;

class PluginUtil {

    /**
     * @param $name
     * @return bool
     */
    public static function checkActiveByName($name) {
        foreach (get_plugins() as $pluginFile => $plugin) {
            if ($plugin['Name'] === $name) {
                if (is_plugin_active($pluginFile)) {
                    return true;
                }
            }
        }
        return false;
    }

}
