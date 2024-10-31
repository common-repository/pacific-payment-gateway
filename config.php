<?php

return [
    "scripts" => [
        [
            "path" => '/public/dist/js/bundle.js',
            "name" => 'bundle',
            "in_footer" => true
        ]
    ],
    "styles" => [
        [
            "path" => '/public/dist/css/bundle.css',
            "name" => 'bundle.css'
        ]
    ],
    "PACIFIC_PLUGIN_DIR" => __DIR__,
    "PACIFIC_PLUGIN_VERSION" => get_plugin_data(__DIR__ . '/pacific-gateway.php')['Version'],
    "PACIFIC_PLUGIN_URL" => esc_url(plugins_url('', __FILE__)),
    "PACIFIC_TEMPLATES_DIR" => __DIR__ . '/templates',
    "PACIFIC_FRONTEND_ASSETS_DIR" => __DIR__ . '/public/dist',
    "PACIFIC_COMPONENTS_NAMESPACE" => "Pacific\GatewayWordpress\App\Component",
    "PACIFIC_API_NAMESPACE" => "Pacific\GatewayWordpress\App\Api",
    "PACIFIC_API_ROUTE_NAMESPACE" => "pacific-payment",
    "PACIFIC_API_ROUTE_VERSION" => "v1",
    "DATABASE_OPTIONS_KEY" => "pacific_gateway_plugin_settings",
];
