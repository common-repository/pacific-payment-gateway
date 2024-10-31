<?php

namespace Pacific\GatewayWordpress\App\Api;

use Pacific\Core\Api\CheckoutContext;
use Pacific\Core\Dto\ShippingMethodOutput;
use Pacific\Core\Exception\HttpException;
use Pacific\Core\Exception\HttpExceptionInterface;
use Pacific\Core\Utils\CredentialsSession;
use Pacific\GatewayWordpress\App\Service\CheckoutService;
use Pacific\GatewayWordpress\App\Utils\Shop;
use Pacific\GatewayWordpress\Kernel\App;

class Checkout extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/checkout/calculate', 'POST', 'calculateCart'],
            ['/checkout/execute', 'POST', 'checkoutCart'],
            ['/checkout/(?P<checkoutId>\S+)', 'GET', 'getCheckoutData'],
        ];
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see CheckoutContext::calculateCartValue()
     */
    public function calculateCart(\WP_REST_Request $data)
    {
        try {
			$params = $data->get_json_params();
	        $attributes = $params['attributes'] ?? [];

	        $pacificUser = $this->pacificGateway->userContext()->getUserData(
		        CredentialsSession::getCredentials(App::get('pacificGateway'))
	        );
	        $address = CheckoutService::getBillingAddress($pacificUser);

			$product = Shop::getProduct(Shop::getWcProduct($params['productId'], $attributes), $address['country'] ?? 'pl');
			$product->quantity = $params['quantity'] ?? 1;

            $calcOrder = CheckoutService::prepareOrder(
	            [$product],
	            $params['shippingAddressId'],
	            $params['shippingMethodId'],
				$this->pacificGateway->configuration->getMerchantId()
            );
            $data = $this->pacificGateway->checkoutContext()->calculateCartValue(
				CredentialsSession::getCredentials($this->pacificGateway),
				$calcOrder
            );
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($data);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see CheckoutContext::checkout()
     */
    public function checkoutCart(\WP_REST_Request $data)
    {
        try {
            $params = $data->get_json_params();
	        $attributes = $params['attributes'] ?? [];

	        $pacificUser = $this->pacificGateway->userContext()->getUserData(
		        CredentialsSession::getCredentials(App::get('pacificGateway'))
	        );
	        $address = CheckoutService::getBillingAddress($pacificUser);

			// Prepare product
			$wcProduct = Shop::getWcProduct($params['productId'], $attributes);
			$pacificProduct = Shop::getProduct($wcProduct, $address['country'] ?? 'pl');
			$pacificProduct->quantity = 1;

			// Create WC Order
	        $wcOrder = CheckoutService::createWcOrder(
				$pacificUser,
				[$wcProduct],
				$params['shippingAddressId'],
				$params['shippingMethodId']
	        );

			// Prepare pacific order object
            $checkoutOrder = CheckoutService::prepareOrder(
	            [$pacificProduct],
                $params['shippingAddressId'],
                $params['shippingMethodId'],
                $this->pacificGateway->configuration->getMerchantId(),
	            $wcOrder->get_id() . '-WP-' .
                (
                    defined('CONTAINER_TAG')
                    ? CONTAINER_TAG . '-' . wp_generate_uuid4()
                    : wp_generate_uuid4()
                )
            );

            $checkoutId = $this->pacificGateway->checkoutContext()->checkout(CredentialsSession::getCredentials($this->pacificGateway), $checkoutOrder);
            $data = $this->pacificGateway->checkoutContext()->getCheckoutData(CredentialsSession::getCredentials($this->pacificGateway), $checkoutId);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        } catch (\Exception $exception) {
            return $this->error(new HttpException($exception->getCode(), $exception->getMessage()));
        }

        return $this->success($data, 201);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see CheckoutContext::getCheckoutData()
     */
    public function getCheckoutData(\WP_REST_Request $data)
    {
        try {
            $data = $this->pacificGateway->checkoutContext()->getCheckoutData(CredentialsSession::getCredentials($this->pacificGateway), $data->get_param('checkoutId'));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($data);
    }

}
