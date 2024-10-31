<?php

namespace Pacific\GatewayWordpress\App\Api;

use Pacific\Core\Api\RegulationContext;
use Pacific\Core\Dto\Regulation\MerchantRegulation;
use Pacific\Core\Exception\HttpExceptionInterface;
use Pacific\Core\Exception\ResourceNotFoundErrorException;
use Pacific\GatewayWordpress\App\Utils\Shop;

class Regulation extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/regulations/pacific/(?P<type>\S+)', 'GET', 'getPacificTerm'],
            ['/regulations/merchant/(?P<type>\S+)', 'GET', 'getMerchantTerm'],
        ];
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see RegulationContext::getTerm()
     */
    public function getPacificTerm(\WP_REST_Request $data)
    {
        try {
            $result = $this->pacificGateway->regulationContext()->getTerm($data->get_param('type'));
            if ($data->get_header('Content-Type') != 'application/json') {
                $this->decodePdfBase64ToFile($result->file, $result->termsType);
            }
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($result);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see MerchantRegulation::TYPE_PRIVACY_POLICY_PAGE, MerchantRegulation::TYPE_TERMS_AND_CONDITIONS_PAGE
     */
    public function getMerchantTerm(\WP_REST_Request $data)
    {
        try {
            $result = Shop::getMerchantTerm($data->get_param('type'));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        if ($result === null) {
            return $this->error(new ResourceNotFoundErrorException(), 404);
        }

        $dto = $this->serializer->denormalize(
            $result,
            MerchantRegulation::class,
            'array'
        );

        return $this->success($dto);
    }

    /**
     * Stream pdf file to browser.
     * Can't be escaped because it's a pdf file. It's loaded from trusted external api.
     *
     * @param $pdfContent
     * @param $name
     */
    protected function decodePdfBase64ToFile($pdfContent, $name) {
        $pdfToStream = base64_decode($pdfContent);

        header("Content-type: application/octet-stream");
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=$name.pdf");
        echo $pdfToStream;
    }

}
