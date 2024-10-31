<?php

namespace Pacific\GatewayWordpress\App\Component;

use Pacific\GatewayWordpress\Kernel\App;

class PaymentBox extends BaseComponent {

    public function boot()
    {
        if (App::get('credentialsValid')) {
            $this->hookInitializer->addAction('woocommerce_before_add_to_cart_form', $this, 'renderButton');
            $this->hookInitializer->addAction('wp_footer', $this, 'renderModal');
        }
    }

    public function renderButton()
    {
        echo App::get('templateLoader')->getTemplateContent('pacific_box');
    }

    public function renderModal()
    {
        if (function_exists('is_product') && (is_product() || is_cart())) {
            echo App::get('templateLoader')->includeHtmlFile('modal');
        }
    }
}
