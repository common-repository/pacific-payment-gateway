<?php

namespace Pacific\GatewayWordpress\App\Api;

use Pacific\Core\Api\UserContext;
use Pacific\Core\Dto\ShippingMethod;
use Pacific\Core\Exception\HttpExceptionInterface;
use Pacific\Core\Utils\CredentialsSession;

class User extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/users/sign-in', 'POST', 'signIn'],
            ['/users', 'GET', 'getUserData'],
            ['/users/shipment-addresses', 'GET', 'getShipmentAddresses'],
            ['/users/shipment-addresses', 'POST', 'addShipmentAddress'],
            ['/users/shipment-addresses/(?P<addressUuid>\S+)', 'DELETE', 'deleteShipmentAddress'],
        ];
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see UserContext::signIn()
     */
    public function signIn(\WP_REST_Request $data)
    {
		$data = $data->get_json_params();

        try {
            $clientCredentials  = $this->pacificGateway->userContext()->signIn($data['email'], $data['pinCode']);
			CredentialsSession::saveCredentials($clientCredentials);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success();
    }

	/**
	 * @return \WP_REST_Response User data
	 * @throws HttpExceptionInterface
	 * @see UserContext::getUserData()
	 */
	public function getUserData()
	{
		try {
			$credentials = CredentialsSession::getCredentials($this->pacificGateway);
			$data = $this->pacificGateway
				->userContext()
				->getUserData($credentials);
		} catch (HttpExceptionInterface $httpException) {
			return $this->error($httpException);
		}

		return $this->success($data);
	}

	/**
	 * Get shipment addresses
	 * Optional param: ?type=courier or ?type=locker
	 *
	 * @param \WP_REST_Request $data
	 * @return \WP_REST_Response Shipping methods
	 * @throws HttpExceptionInterface
	 * @see UserContext::getShipmentAddresses()
	 */
	public function getShipmentAddresses(\WP_REST_Request $data)
	{
		try {
			$credentials = CredentialsSession::getCredentials($this->pacificGateway);

			/** @var \Pacific\Core\Dto\User $userData */
			$userData = $this->pacificGateway
				->userContext()
				->getUserData($credentials);

			$data = $this->pacificGateway
				->userContext()
				->getShipmentAddresses($credentials, $userData->uuid,  $data->get_params()['type'] ?? null);

		} catch (HttpExceptionInterface $httpException) {
			return $this->error($httpException);
		}

		return $this->success($data);
	}

	/**
	 * Add shipment address
	 *
	 * @param \WP_REST_Request $data
	 * @return \WP_REST_Response
	 * @throws HttpExceptionInterface
	 * @see UserContext::addShipmentAddress()
	 */
	public function addShipmentAddress(\WP_REST_Request $data)
	{
		/** @var ShippingMethod $shippingMethod */
		$shippingMethod = $this->serializer->denormalize(
			$data->get_json_params(), ShippingMethod::class, 'array'
		);

		try {
			$credentials = CredentialsSession::getCredentials($this->pacificGateway);
			$this->pacificGateway
				->userContext()
				->addShipmentAddress($credentials, $shippingMethod);

		} catch (HttpExceptionInterface $httpException) {
			return $this->error($httpException);
		}

		return $this->success(null, 201);
	}

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see UserContext::deleteShipmentAddress()
     */
    public function deleteShipmentAddress(\WP_REST_Request $data)
    {
        try {
            $credentials = CredentialsSession::getCredentials($this->pacificGateway);

            /** @var \Pacific\Core\Dto\User $userData */
            $userData = $this->pacificGateway
                ->userContext()
                ->getUserData($credentials);

            $this->pacificGateway
                ->userContext()
                ->deleteShipmentAddress($credentials, $userData->uuid, $data->get_param('addressUuid'));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success(null, 204);
    }

}
