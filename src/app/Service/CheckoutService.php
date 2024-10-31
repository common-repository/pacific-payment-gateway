<?php

namespace Pacific\GatewayWordpress\App\Service;

use Pacific\Core\Dto\Checkout\CheckoutData;
use Pacific\Core\Dto\Checkout\CheckoutOrder;
use Pacific\Core\Dto\ShippingMethodOutput;
use Pacific\Core\Dto\Shop\Product;
use Pacific\Core\Dto\User;
use Pacific\Core\Utils\CredentialsSession;
use Pacific\GatewayWordpress\App\Component\PacificInpostShipmentMethod;
use Pacific\GatewayWordpress\App\Utils\InpostPaczkomatyIntegration;
use Pacific\GatewayWordpress\App\Utils\Shop;
use Pacific\GatewayWordpress\App\Utils\WPDeskPaczkomatyInpostIntegration;
use Pacific\GatewayWordpress\Kernel\App;
use Symfony\Component\Intl\Languages;

class CheckoutService {

	/**
	 * @param Product[] $products
	 * @param string $shippingAddressId
	 * @param string $shippingMethodId
	 * @param string $merchantId
	 * @param string|null $externalOrderId
	 *
	 * @return CheckoutOrder
	 */
	static public function prepareOrder(
		array $products,
		string $shippingAddressId,
		string $shippingMethodId,
		string $merchantId,
		?string $externalOrderId = null
	) {
		$order = new CheckoutOrder();
        $order->shippingAddressId = $shippingAddressId;

        $checkoutData = new CheckoutData();
        $checkoutData->shippingMethodId = $shippingMethodId;

        if ($externalOrderId !== null) {
            $checkoutData->externalOrderId = $externalOrderId;
        }

        $checkoutData->products = self::reCalculateProducts($products);

        $order->checkoutData = (object)[
            $merchantId => $checkoutData
        ];

        return $order;
	}

	/**
	 * @param Product[] $products
	 * @return Product[]
	 */
	public static function reCalculateProducts(array &$products)
	{
		foreach ($products as $product) {
			self::reCalculateProduct($product);
		}
		return $products;
	}

	public static function reCalculateProduct(Product &$product)
	{
		if($product->price->isTaxEnabled() && !$product->price->hasTax()) {
			$tax = \WC_Tax::calc_tax( $product->price->amount, [
				[
					"rate"     => $product->price->getTaxRate(),
					"label"    => "Tax",
					"shipping" => "yes",
					"compound" => "no",
				]
			], $product->price->hasTax());

//				$amount = (($product->price->getTaxRate() / 100) * $product->price->amount) + $product->price->amount;
			$product->price->amount = (string) ($product->price->amount + reset($tax));
		}
		return $product;
	}

	/**
	 * @param User $user
	 * @param \WC_Product[]|\WC_Product_Variation[] $products
	 * @param string $shippingAddressId
	 * @param string $shippingMethodId
	 *
	 * @return \WC_Order|\WP_Error
	 * @throws \WC_Data_Exception|\Exception
	 */
	public static function createWcOrder(
		User $user,
		array $products,
		string $shippingAddressId,
		string $shippingMethodId
	)
	{
        // Prepare address
        $address = self::prepareAddress($user, $shippingAddressId);

        // Prepare shipping
        $wcShippingMethod = null;
        if (!empty(App::get('databaseSettings')['shipping_methods'][$shippingMethodId])) {
            $wcShippingMethod = Shop::getWcShippingMethod(App::get('databaseSettings')['shipping_methods'][$shippingMethodId], $address['country']);
        }

        if (!$wcShippingMethod) {
            throw new \Exception("Shipping method doesn't exists.", 500);
        }

		$args = [
			'created_via' => 'Pacific gateway'
		];

        $wpUser = get_user_by('email', $user->email);
        if ($wpUser instanceof \WP_User && $wpUser->ID) {
            $args['customer_id'] = $wpUser->ID;
        }

		$order = wc_create_order($args);

		foreach ($products as $product) {
			$order->add_product($product, 1);
		}

        $pacificShippingMethod = App::get('pacificGateway')->shopContext()->getMerchantShippingMethod($shippingMethodId);

        $order->set_address($address, 'shipping');
        if ($pacificShippingMethod->type === ShippingMethodOutput::TYPE_POINT) {
            $order->set_address(self::getBillingAddress($user), 'billing');
        } else {
            $order->set_address($address, 'billing');
        }

		$wcShipping = new \WC_Order_Item_Shipping();
		$wcShipping->set_method_id($wcShippingMethod->id);
		$wcShipping->set_method_title($pacificShippingMethod->name);
		$wcShipping->set_instance_id($wcShippingMethod->instance_id);

		if(wc_tax_enabled()) {
            $wcShippingRates = self::getWcShippingRates($order, $address['country']);
			$shippingPrice = $pacificShippingMethod->price->amount / (($wcShippingRates + 100) / 100);
			$wcShipping->set_total($shippingPrice);
			$wcShipping->calculate_taxes();
		} else {
			$wcShipping->set_total($pacificShippingMethod->price->amount);
		}

        $wcShipping->save();
		$order->add_item($wcShipping);

		// Set payment gateway
		$wcPaymentGateways = WC()->payment_gateways->payment_gateways();
		$order->set_payment_method($wcPaymentGateways['pacific']);

		$order->calculate_taxes();
		$order->calculate_totals();

		$order->set_status('on-hold');
		$order->save();

        if ($pacificShippingMethod->type === ShippingMethodOutput::TYPE_POINT) {
            self::setOrderParcelLocker($order->get_id(), self::prepareAddress($user, $shippingAddressId, true), $wcShippingMethod, $wcShipping);
        }

		return $order;
	}

    /**
     * @param \WC_Order $order
     * @param string $country
     * @return float|int
     * @see WC_Abstract_Order::calculate_taxes
     */
    private static function getWcShippingRates(\WC_Order $order, string $country)
    {
        $shippingTaxClass = get_option( 'woocommerce_shipping_tax_class' );

        if ('inherit' === $shippingTaxClass) {
            $found_classes = array_intersect(
                array_merge([''], \WC_Tax::get_tax_class_slugs()),
                $order->get_items_tax_classes()
            );
            
            $shippingTaxClass = count($found_classes) ? current($found_classes) : false;
        }

        $rates = \WC_Tax::find_rates([
            'country' => $country,
            'tax_class' => $shippingTaxClass
        ]);

        $rates = array_filter($rates, function($r) {
            return $r["shipping"] === "yes";
        });

        $rates = array_map(function ($r) {
            return $r['rate'];
        }, $rates);

        return array_sum($rates);
    }

    /**
     * Add parcel locker to order in chosen shipment integration
     *
     * @param $orderId
     * @param $address
     * @param $shippingMethod
     */
    private static function setOrderParcelLocker($orderId, $address, $shippingMethod, $wcShipping = null)
    {
        $lockerId = $address['point_id'];
        $lockerAddress1 = $address['address_1'];
        $lockerAddress1 .= $address['address_2'] != '' ? ' / '. $address['address_2'] : '';
        $lockerAddress2 =  $address['postcode'].' '.$address['city'];

        try {
            switch ($shippingMethod->id) {
                case InpostPaczkomatyIntegration::getShippingMethod():
                    InpostPaczkomatyIntegration::orderAddLockerAddress($orderId, $lockerId, $lockerAddress1, $lockerAddress2);
                    break;
                case WPDeskPaczkomatyInpostIntegration::getShippingMethod():
                    WPDeskPaczkomatyInpostIntegration::orderAddLockerAddress($orderId, $lockerId, $shippingMethod, $wcShipping);
                    break;
                case PacificInpostShipmentMethod::PACIFIC_SHIPMENT_ID:
                default:
                    PacificInpostShipmentMethod::orderAddLockerAddress($orderId, $lockerId, $lockerAddress1, $lockerAddress2);
                    break;
            }
        } catch (\Exception $exception) {
            PacificInpostShipmentMethod::orderAddLockerAddress($orderId, $lockerId, $lockerAddress1, $lockerAddress2);
        }
    }

    /**
     * @param User $user
     * @return array|null
     * @throws \Exception
     */
    public static function getBillingAddress(User $user)
    {
        $gateway = App::get('pacificGateway');
        $addresses = $gateway->userContext()->getShipmentAddresses(
            CredentialsSession::getCredentials($gateway),
            $user->uuid,
            ShippingMethodOutput::TYPE_COURIER
        );

        foreach ($addresses as $address) {
            if ($address->defaultShipment === true) {
                return self::prepareAddressArray($user, $address);
            }
        }

		// If no default address, return first address
        foreach ($addresses as $address) {
            if ($address->type === 'COURIER') {
                return self::prepareAddressArray($user, $address);
            }
        }

        return null;
    }

    /**
     * @param User $user
     * @param $shipmentAddressId
     * @return array
     * @throws \Exception
     */
	private static function prepareAddress(User $user, $shipmentAddressId, $additionalFields = false)
	{
		$gateway = App::get('pacificGateway');
		$address = $gateway->userContext()->getShipmentAddress(
			CredentialsSession::getCredentials($gateway),
			$user->uuid,
			$shipmentAddressId
		);

		return self::prepareAddressArray($user, $address, $additionalFields);
	}

    /**
     * @param User $user
     * @param $address
     * @return array
     */
    private static function prepareAddressArray(User $user, $address, $additionalFields = false)
    {
        $data = [
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
            'email' => $user->email,
            'phone' => $user->mobile ? $user->mobile->prefix.$user->mobile->number : null,
            'address_1' => $address->address->streetName.' '.$address->address->streetNumber,
            'address_2' => $address->address->flatNumber,
            'city' => $address->address->city,
            'state' => '',
            'postcode' => $address->address->postal,
            'country' => (strlen($address->address->country) > 2)
                ? Languages::getAlpha2Code(strtolower($address->address->country))
                : strtolower($address->address->country)
        ];

		if ($additionalFields) {
			$data['point_id'] = $address->pointId ?? '';
		}

		return $data;
    }
}
