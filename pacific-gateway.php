<?php
/**
 * Plugin Name:     Pacific Payment Gateway
 * Description:     A payment gateway for Pacific.org.
 * Plugin URI:      https://checkout.pacific.org/
 * Requires PHP:    7.1
 * Author:          InterSynergy
 * Author URI:      https://www.intersynergy.pl
 * Text Domain:     pacific_gateway_plugin
 * Domain Path:     /languages
 * Version:         1.0.23
 */

use Pacific\GatewayWordpress\Kernel\App;

add_action('woocommerce_init',  function() {
    require __DIR__ . '/vendor/autoload.php';
    require 'src/bootstrap.php';

    App::get('hookInitializer')->run();
});
