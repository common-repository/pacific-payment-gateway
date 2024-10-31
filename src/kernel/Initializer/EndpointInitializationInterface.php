<?php

namespace Pacific\GatewayWordpress\Kernel\Initializer;

/**
 * Endpoints implementing this method in the "api" directory will be automatically run with the "boot" method
 */
interface EndpointInitializationInterface
{
    public function routes();
}