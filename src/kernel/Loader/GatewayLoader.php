<?php

namespace Pacific\GatewayWordpress\Kernel\Loader;

use Pacific\Core\Dto\OAuth\AccessToken;
use Pacific\Core\Gateway;
use Pacific\Core\Utils\Configuration;
use Pacific\GatewayWordpress\Kernel\App;

class GatewayLoader
{
    /** @var Gateway */
    private $gateway;

    /** @var array $options */
    private $options;

    /** @var AccessToken $accessToken */
    private $accessToken;

    /** @var string */
    private $accessTokenKey;

    public function __construct()
    {
        App::bind('databaseSettings', get_option(App::get('DATABASE_OPTIONS_KEY'), []));
        App::bind('credentialsValid', get_option(App::get('DATABASE_OPTIONS_KEY') . '_credentials_valid', false));

        $this->options = App::get('databaseSettings');
        $this->accessTokenKey = App::get('DATABASE_OPTIONS_KEY') . '_access_token';

        $this->gateway = new Gateway($this->getConfiguration(), $this->getAccessToken());
        $this->gateway->setMerchantCredentialsCallback([$this, 'setAccessToken']);
    }

    public function getGateway(): Gateway
    {
        return $this->gateway;
    }

	public function setAccessToken(AccessToken $accessToken)
	{
        update_option($this->accessTokenKey, $accessToken);
        $this->accessToken = $accessToken;
	}

	public function getAccessToken(): ?AccessToken
	{
        if ($this->accessToken == null) {
            $this->accessToken = get_option($this->accessTokenKey, null) ?: null;
        }

        return $this->accessToken;
	}

    public function getConfiguration(): Configuration
    {
        $configuration = [
            'merchant_id' => null,
            'client_id' => null,
            'client_secret' => null,
            'secret_key' => null,
            'environment' => null,
            'language' => 'pl_PL'
        ];

        $configuration = array_merge($configuration, $this->options ?: []);

        // @todo fetch language from WP
        return new Configuration($configuration);
    }
}
