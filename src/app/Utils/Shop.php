<?php

namespace Pacific\GatewayWordpress\App\Utils;

use Pacific\Core\Dto\Shop\Attribute;
use Pacific\Core\Dto\Shop\Price;
use Pacific\Core\Dto\Shop\Product;
use Pacific\Core\Service\SerializerService;
use Pacific\GatewayWordpress\Kernel\App;
use Symfony\Component\Intl\Languages;

final class Shop {

	/**
	 * @param \WC_Product|\WC_Product_Variation $wcProduct
	 * @return Product
	 */
	static public function getProduct($wcProduct, $country = 'pl'): Product {
		$attributes = self::getAttributesArray($wcProduct);

		$pacificPrice = new Price();
		$pacificPrice->amount = $wcProduct->get_price();
		$pacificPrice->currency = strtoupper(get_woocommerce_currency());
		$pacificPrice->setTaxEnabled(wc_tax_enabled());
		$pacificPrice->setHasTax(wc_prices_include_tax());

		if(wc_tax_enabled()) {
			$taxArray = \WC_Tax::find_rates(['country' => $country]);
			if($taxArray) {
				$tax = reset($taxArray);
				$pacificPrice->setTaxRate($tax['rate']);
			}
		}

		$pacificProduct = new Product();
		$pacificProduct->sku = $wcProduct->get_sku() ?: (string) $wcProduct->get_id();
		$pacificProduct->price = $pacificPrice;
		$pacificProduct->name = $wcProduct->get_name();
		$pacificProduct->externalProductId = $wcProduct->get_id();
		$pacificProduct->image = wp_get_attachment_url($wcProduct->get_image_id()) ?? null;

		if($attributes) {
			$pacificProduct->setAllAttributes($attributes);
			$pacificProduct->attributes = SerializerService::getSerializer()->denormalize($attributes, Attribute::class.'[]','array');
		}

		if($wcProduct->get_type() === 'variation') {
			$pacificProduct->productId = $wcProduct->get_parent_id();
		} else {
			$pacificProduct->productId = $wcProduct->get_id();
		}

		return $pacificProduct;
	}

	/**
	 * @param $id
	 * @param $wcAttributes
	 *
	 * @return false|\WC_Product|\WC_Product_Variation|null
	 */
	public static function getWcProduct($id, $wcAttributes)
	{
		$wcProduct = wc_get_product($id);
		if($wcAttributes) {
			$wcVariant = (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
				$wcProduct,
				$wcAttributes
			);
			if($wcVariant > 0) {
				$wcProduct = new \WC_Product_Variation($wcVariant);
				$wcProduct->set_attributes($wcAttributes);
			}
		}
		return $wcProduct;
	}

	/**
	 * @param $variation @param \WC_Product_Variation|\WC_Product|array $variation
	 * @return array
	 */
	public static function getAttributesArray($variation) : array
	{
		// Prepare
		if (is_a($variation, 'WC_Product_Variation')) {
			$variation_attributes = $variation->get_attributes();
			$product              = $variation;
			$variation_name       = $variation->get_name();
		} else {
			$product        = false;
			$variation_name = '';
			// Remove attribute_ prefix from names.
			$variation_attributes = array();
			if ( is_array( $variation ) ) {
				foreach ( $variation as $key => $value ) {
					$variation_attributes[ str_replace( 'attribute_', '', $key ) ] = $value;
				}
			}
		}

		$data = [];
		if (is_array($variation_attributes)) {
			foreach ( $variation_attributes as $name => $value ) {
				$rawValue = $value;
				// If this is a term slug, get the term's nice name.
				if ( taxonomy_exists( $name ) ) {
					$term = get_term_by( 'slug', $value, $name );
					if ( ! is_wp_error( $term ) && ! empty( $term->name ) ) {
						$value = $term->name;
					}
				}

				$data[] = [
					'key' => $name,
					'name' => wc_attribute_label($name, $product),
					'value' => rawurldecode( $value ),
					'rawValue' => $rawValue,
					'partOfName' => wc_is_attribute_in_product_name($value, $variation_name)
				];
			}
		}

		return $data;
	}

	/**
	 * @param string|int $shippingId
	 * @param string $addressCode
	 *
	 * @return \WC_Shipping_Method|null
	 */
	public static function getWcShippingMethod($shippingId, $addressCode)
	{
		$code = (strlen($addressCode) > 2)
			? strtoupper(Languages::getAlpha2Code(strtolower($addressCode)))
			: strtoupper($addressCode);

		$zone = self::findZone($code);
		if(!$zone) {
			return null;
		}

		return self::findShippingMethod($zone, $shippingId);
	}

	/**
	 * @param string $code
	 * @return null|array|\WC_Shipping_Zone
	 */
	private static function findZone($code)
	{
		$zones = \WC_Shipping_Zones::get_zones();
		foreach ($zones as $zone) {
			foreach ($zone['zone_locations'] as $location) {
				if ($location->code === $code) {
					return $zone;
				}
			}
		}
		return null;
	}

	/**
	 * @param array $zone
	 * @param string $shippingId
	 * @return null|\WC_Shipping_Method
	 */
	private static function findShippingMethod($zone, string $shippingId)
	{
		foreach ($zone['shipping_methods'] as $method) {
			if ($method->id === $shippingId) {
				return $method;
			}
		}
		return null;
	}

	/**
	 * @param string $status
	 * @return false|string
	 */
    public static function getWcStatusByPacificStatus(string $status)
    {
        switch ($status) {
//            case "pending":
//                return "pending";
            case "SUCCESSFUL":
                return "processing";
//            case "on-hold":
//                return "on-hold";
//            case "completed":
//                return "completed";
//            case "cancelled":
//                return "cancelled";
//            case "refunded":
//                return "refunded";
            case "FAILED":
                return "failed";
            default:
                return false;
        }
    }

    /**
     * @param string $key
     * @return null|array
     */
    public static function getMerchantTerm(string $key)
    {
        $settings = App::get('databaseSettings');
        if (array_key_exists($key, $settings)) {
            $post = get_post($settings[$key]);

            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'url' => get_permalink($post)
            ];
        }

        return null;
    }
}
