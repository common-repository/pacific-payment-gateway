<?php

namespace Pacific\GatewayWordpress\App\Api;

use Pacific\Core\Api\ShopContext;
use Pacific\Core\Exception\HttpException;
use Pacific\Core\Exception\HttpExceptionInterface;
use Pacific\Core\Exception\InvalidOrderStatusChangeException;
use Pacific\Core\Exception\InvalidPaidOrderStatusChangeException;
use Pacific\Core\Exception\InvalidRequestSignatureException;
use Pacific\Core\Exception\SignatureHeaderMissingException;
use Pacific\Core\Exception\ValidationFailedErrorException;
use Pacific\GatewayWordpress\App\Service\CheckoutService;
use Pacific\GatewayWordpress\App\Utils\Shop as ShopUtils;

class Shop extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/e-commerce/merchant/shipping', 'GET', 'getMerchantShippingMethods'],
            ['/e-commerce/product/(?P<productId>\d+)', 'GET', 'getProduct'],
            ['/e-commerce/webhook', 'POST', 'webhook']
        ];
    }

    /**
     * @return string[]|\WP_REST_Response
     * @throws HttpExceptionInterface
     * @see ShopContext::getMerchantShippingMethods()
     */
    public function getMerchantShippingMethods()
    {
        try {
            $shippingMethods = $this->pacificGateway->shopContext()->getMerchantShippingMethods();
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($shippingMethods);
    }

	/**
	 * Return product details by product id
	 *
	 * @param \WP_REST_Request $data
	 * @return string[]|\WP_REST_Response
	 * @throws HttpExceptionInterface
	 * @see ShopUtils::getProduct()
	 */
	public function getProduct(\WP_REST_Request $data)
	{
		try {
			$attributes = array_filter($data->get_params(), function($key) {
				return strpos($key, 'attribute_') === 0;
			}, ARRAY_FILTER_USE_KEY) ?? [];

			$product = ShopUtils::getProduct(ShopUtils::getWcProduct($data->get_param('productId'), $attributes));
			CheckoutService::reCalculateProduct($product);
		} catch (HttpExceptionInterface $httpException) {
			return $this->error($httpException);
		}

		return $this->success($product);
	}

    /**
     * @param \WP_REST_Request $data
     * @return string[]|\WP_REST_Response
     */
    public function webhook(\WP_REST_Request $request)
    {
        try {
            $this->logWebhookRequest($request);
            $jsonParams = $request->get_json_params();

            if (!$request->get_header('x_signature')) {
                $this->logWebhookError("Header: 'X-Signature' doesn't exists.");

                return $this->error(
                    new SignatureHeaderMissingException("Missing signature header.")
                );
            }

            $signatureChecker = $this->pacificGateway->getSignatureChecker();
            $correctlySignature = $signatureChecker->createSignature($jsonParams);
            $isValidSignatureRequest = $signatureChecker->compareSignatures(
                $request->get_header('x_signature'),
                $correctlySignature
            );

            if (!$isValidSignatureRequest) {
                $this->logWebhookError("The request signature is invalid.");

                return $this->error(
                    new InvalidRequestSignatureException("The request signature is invalid.")
                );
            }

            // Checks whether the required parameters exist
            $validationsError = [];

            if (!isset($jsonParams['status'])) {
                $this->logWebhookError("Param: 'status' doesn't exists.");

                $validationsError[] = [
                    "field" => "status",
                    "message" => "must not be null"
                ];
            }

            if (!isset($jsonParams['extOrderId'])) {
                $this->logWebhookError("Param: 'extOrderId' doesn't exists.");

                $validationsError[] = [
                    "field" => "extOrderId",
                    "message" => "must not be null"
                ];
            }

            if (!empty($validationsError)) {
                return $this->error(
                    (new ValidationFailedErrorException("Request parameters constraints violation"))
                        ->setErrors($validationsError)
                );
            }

            // Checks whether the status and order are valid
            $status = ShopUtils::getWcStatusByPacificStatus($jsonParams['status']);
            if ($status === false) {
                $this->logWebhookError("Unknown status: '{$jsonParams['status']}'");

                $validationsError[] = [
                    "field" => "status",
                    "message" => "unknown status: '{$jsonParams['status']}', must be 'SUCCESSFUL' or 'FAIL'"
                ];
            }

            $orderId = (int) substr(
                $jsonParams['extOrderId'],
                0,
                strpos($jsonParams['extOrderId'], '-')
            );

            $order = wc_get_order($orderId);
            if ($order === false || $order->get_payment_method() !== 'pacific') {
                $this->logWebhookError("Unknown order by ID: '{$jsonParams['extOrderId']}'");

                $validationsError[] = [
                    "field" => "extOrderId",
                    "message" => "order with the number '$orderId' does not exist"
                ];
            }

            if (!empty($validationsError)) {
                return $this->error(
                    (new ValidationFailedErrorException("Request parameters constraints violation"))
                        ->setErrors($validationsError)
                );
            }

            $orderStatus = $order->get_status();
            if ($orderStatus === 'on-hold' || $orderStatus === 'failed') {
                $order->set_status($status);
                $order->save();
            } else if ($orderStatus === 'processing' || $orderStatus === 'completed' || $orderStatus === 'refunded') {
                return $this->error(new InvalidPaidOrderStatusChangeException("Invalid status change for a paid order."));
            } else {
                return $this->error(new InvalidOrderStatusChangeException("Invalid status change for an order."));
            }

            return $this->success();

        } catch (\Exception $e) {
            $this->logger->error(
                print_r([
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode(),
                    'trace' => $e->getTrace()
                ], true),
                ['source' => 'pacific-webhook']
            );

            return $this->error(new HttpException(500, "Unknown error."));
        }
    }

    /**
     * @param string $message
     * @return void
     */
    private function logWebhookError(string $message)
    {
        $this->logger->error(
            $message,
            ['source' => 'pacific-webhook']
        );
    }

    /**
     * @param \WP_REST_Request $request
     * @return void
     */
    private function logWebhookRequest(\WP_REST_Request $request)
    {
        $this->logger->info(
            print_r([
                'json_params' => $request->get_json_params(),
                'headers' => $request->get_headers()
            ], true),
            ['source' => 'pacific-webhook']
        );
    }
}
