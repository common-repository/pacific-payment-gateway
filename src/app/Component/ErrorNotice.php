<?php

namespace Pacific\GatewayWordpress\App\Component;

use Pacific\GatewayWordpress\Kernel\App;

class ErrorNotice extends BaseComponent {

    public function boot()
    {
        if (!App::get('credentialsValid')) {
            $this->hookInitializer->addAction('admin_notices', $this, 'renderError');
        }
    }

    public function renderError()
    {
        $link = menu_page_url(AdminPage::PAGE_SLUG, false);
        $message = sprintf(
            __('To use the Pacific Payment Gateway, you need to configure your <a href="%s">merchant access data and shipping methods.</a>', 'pacific_gateway_plugin'),
            $link
        );

        echo App::get('templateLoader')->getTemplateContent('admin/empty_merchant_data_notice', ['message' => $message]);
    }
}
