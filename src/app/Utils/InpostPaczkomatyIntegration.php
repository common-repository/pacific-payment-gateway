<?php

namespace Pacific\GatewayWordpress\App\Utils;

class InpostPaczkomatyIntegration {

    public static function getShippingMethod() {
        return 'inpost_paczkomaty';
    }

    public static function orderAddLockerAddress($orderId, $lockerId, $lockerAddress1, $lockerAddress2) {
        if (PluginUtil::checkActiveByName("Inpost Paczkomaty") === true) {
            $val = $lockerId . ' , ' . $lockerAddress1 . ' , ' . $lockerAddress2;
            if (!empty($val)) {
                update_post_meta($orderId, 'Wybrany paczkomat', $val);
            }
            if (!empty($lockerId)) {
                update_post_meta($orderId, 'paczkomat_key', $lockerId);
            }
            return true;
        } else {
            return new \Exception('Selected plugin not enabled.');
        }
    }

}
