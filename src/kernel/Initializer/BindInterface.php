<?php

namespace Pacific\GatewayWordpress\Kernel\Initializer;

/**
 * Classes implementing this interface will be available in "App" registers
 */
interface BindInterface
{
    /**
     * The method sets the name of the key in "App" registries
     * @return string
     */
    public function bindName();
}