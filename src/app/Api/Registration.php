<?php

namespace Pacific\GatewayWordpress\App\Api;

use Pacific\Core\Api\RegisterContext;
use Pacific\Core\Dto\MobileNumber;
use Pacific\Core\Dto\Register\RegisterUser;
use Pacific\Core\Exception\HttpExceptionInterface;

class Registration extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/users/generate-sms-verification-code', 'POST', 'generateSmsVerificationCode'],
            ['/users/check-pin-code', 'POST', 'checkPinCode'],
            ['/users/individual-sign-up', 'POST', 'individualSignUp'],
            ['/users/check-email-exist/(?P<email>\S+)', 'GET', 'emailExist'],
        ];
    }

    /**
     * @param \WP_REST_Request $data
     * @return string[]|\WP_REST_Response
     * @throws HttpExceptionInterface
     * @see RegisterContext::generateSmsVerificationCode()
     */
    public function generateSmsVerificationCode(\WP_REST_Request $data)
    {
        $mobileNumberDto = $this->serializer->denormalize(
            $data->get_json_params()['mobile'], MobileNumber::class, 'array'
        );

        try {
            $this->pacificGateway->RegisterContext()->generateSmsVerificationCode($mobileNumberDto);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success(null, 201);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see RegisterContext::checkPinCode()
     *
     */
    public function checkPinCode(\WP_REST_Request $data)
    {
        try {
            $this->pacificGateway->RegisterContext()->checkPinCode($data->get_json_params()['pinCode']);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success();
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see RegisterContext::signUp()
     */
    public function individualSignUp(\WP_REST_Request $data)
    {
        $registerUserDto = $this->serializer->denormalize(
            $data->get_json_params(), RegisterUser::class, 'array'
        );

        try {
            $this->pacificGateway->RegisterContext()->signUp($registerUserDto);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success(null, 201);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see RegisterContext::emailExist()
     */
    public function emailExist(\WP_REST_Request $data)
    {

        try {
            $data = $this->pacificGateway->RegisterContext()->emailExist($data->get_param('email'));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($data);
    }

}
