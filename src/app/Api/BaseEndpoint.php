<?php

namespace Pacific\GatewayWordpress\App\Api;

use Pacific\Core\Exception\HttpExceptionInterface;
use Pacific\Core\Gateway;
use Pacific\Core\Service\Response;
use Pacific\Core\Service\SerializerService;
use Pacific\GatewayWordpress\Kernel\App;
use Pacific\GatewayWordpress\Kernel\Initializer\EndpointInitializationInterface;

abstract class BaseEndpoint implements EndpointInitializationInterface
{
	/** @var Gateway */
    protected $pacificGateway;

	/** @var \Symfony\Component\Serializer\Serializer */
	protected $serializer;

    /** @var \WC_Logger */
    protected $logger;

    public function __construct()
    {
        $this->pacificGateway = App::get('pacificGateway');
		$this->serializer = SerializerService::getSerializer();
        $this->logger = wc_get_logger();
    }

    /**
     * Registers endpoints
     *
     *     return [
     *       [string $endpoint, string $httpMethod, string $callback (method in current class), [array $apiArgs], [string apiVersion]]
     *       ...
     *       N
     *     ];
     *
     *  example:
     *     return [
     *       ['/foo/(?P<id>\d+)', 'GET', 'bar']
     *     ];
     */
    abstract public function routes();

    /**
     * Returns WP_REST_RESPONSE object based on the contents of a given exception.
     *
     * @param HttpExceptionInterface $httpException
     * @param $code
     * @return \WP_REST_Response
     */
    public function error(HttpExceptionInterface $httpException, $code = null)
    {
        $responseBody = Response::prepareSimpleErrorResponseBody($httpException);

        if (is_null($code)) {
            $code = $httpException->getHttpCode();
        }

        return new \WP_REST_Response($responseBody, $code);
    }

    /**
     * Returns WP_REST_RESPONSE object with given data and http status code.
     *
     * @param array|object|null $data
     * @param $code
     * @return \WP_REST_Response
     */
    public function success($data = null, $code = 200)
    {
        $responseBody = Response::prepareSimpleSuccessResponseBody($data);
        return new \WP_REST_Response($responseBody, $code);
    }
}
