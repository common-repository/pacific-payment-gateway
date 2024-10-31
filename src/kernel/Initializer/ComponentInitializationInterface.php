<?php

namespace Pacific\GatewayWordpress\Kernel\Initializer;

/**
 * Classes implementing this method in the "component" directory will be automatically run with the "boot" method
 */
interface ComponentInitializationInterface
{
    public function boot();
}