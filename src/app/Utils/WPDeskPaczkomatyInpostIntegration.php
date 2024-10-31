<?php

namespace Pacific\GatewayWordpress\App\Utils;

use WPDesk\FS\TableRate\ShippingMethodSingle;

class WPDeskPaczkomatyInpostIntegration {

    public static function getShippingMethod() {
        if ( class_exists( 'WPDesk_Paczkomaty_FS' ) ) {

            // Get all your existing shipping zones IDS
            $zone_ids = array_keys( [''] + \WC_Shipping_Zones::get_zones() );

            // Loop through shipping Zones IDs
            foreach ( $zone_ids as $zone_id ) {
                // Get the shipping Zone object
                $shipping_zone = new \WC_Shipping_Zone($zone_id);

                // Get all shipping method values for the shipping zone
                $shipping_methods = $shipping_zone->get_shipping_methods( true, 'values' );

                // Loop through each shipping methods set for the current shipping zone
                foreach ( $shipping_methods as $instance_id => $shipping_method )
                {
                    if ($shipping_method->id === ShippingMethodSingle::SHIPPING_METHOD_ID) {
                        if (
                            $shipping_method->instance_settings['method_integration'] === 'paczkomaty' &&
                            $shipping_method->instance_settings['paczkomaty_usluga'] === 'paczkomaty'
                        ) {
                            return $shipping_method->id;
                        }
                    }
                }
            }
        }
        return null;
    }

    public static function orderAddLockerAddress($orderId, $lockerId, $shippingMethod, $wcShipping) {
        if (PluginUtil::checkActiveByName("WooCommerce InPost") === true) {

            $order = wc_get_order($orderId);
            $_POST[\WPDesk_Paczkomaty_Checkout::FIELD_PACZKOMAT_ID] = $lockerId;
            $shipment = fs_create_shipment($order, $shippingMethod->instance_settings);
            $shipment->set_meta('_fs_method', $shippingMethod->instance_settings);
            $shipment->set_meta('_shipping_id', $wcShipping->get_id());
            $shipment->set_meta('_shipping_method', $wcShipping);
            $shipment->set_created_via_checkout();
            $shipment->checkout($shippingMethod->instance_settings, []);
            $shipment->save();

            return true;
        } else {
            return new \Exception('Selected plugin not enabled.');
        }
    }
}
