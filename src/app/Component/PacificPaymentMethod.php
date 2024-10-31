<?php

namespace Pacific\GatewayWordpress\App\Component;

use Pacific\GatewayWordpress\Kernel\App;
use Pacific\GatewayWordpress\Kernel\Initializer\ComponentInitializationInterface;
use Pacific\GatewayWordpress\Kernel\Initializer\HookInitializer;

class PacificPaymentMethod extends \WC_Payment_Gateway implements ComponentInitializationInterface {

    public $domain;

    /** @var HookInitializer */
    private $hookInitializer;

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        $this->domain = "pacific_gateway_plugin";
        $this->id  = 'pacific';
        $this->title = __( 'Pacific payments', $this->domain);
        $this->method_title = __( 'Pacific payments', $this->domain);
        $this->icon = apply_filters('woocommerce_custom_gateway_icon', '');
        $this->has_fields = false;
        $this->method_description = sprintf(
            __( 'Payment settings can be set <a href="%s">here<a>', $this->domain ),
            admin_url( 'admin.php?page=' . AdminPage::PAGE_SLUG)
        );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = 'yes';
    }

    public function boot()
    {
        $this->hookInitializer = App::get('hookInitializer');
        $this->hookInitializer->addFilter('woocommerce_payment_gateways', $this, '_init');
        $this->hookInitializer->addFilter('option_woocommerce_pacific_settings', $this, '_disableToggleInWcSettings');
        $this->hookInitializer->addAction('woocommerce_before_settings_checkout', $this, '_disableSaveButton');
        $this->hookInitializer->addAction('init', $this, 'wpdocs_load_textdomain');

    }

    public function _init($paymentMethods)
    {
        if (!is_cart() && !is_checkout() && !is_account_page()) {
            $paymentMethods[] = self::class;
        }

        return $paymentMethods;
    }

    public function _disableToggleInWcSettings($settings)
    {
        if (wp_doing_ajax() && isset($_POST['action']) && $_POST['action'] == 'woocommerce_toggle_gateway_enabled') {
            $settings['enabled'] = 'no';
        }

        return $settings;
    }

    public function _disableSaveButton()
    {
        if (isset($_GET['section']) && $_GET['section'] === 'pacific') {
            $GLOBALS['hide_save_button'] = true;
        }
    }

    function wpdocs_load_textdomain() {
        $root = plugin_basename(dirname(__FILE__, 4));
        load_plugin_textdomain( 'pacific_gateway_plugin', false, "$root/languages/");
    }
}
