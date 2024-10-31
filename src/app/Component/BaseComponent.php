<?php

namespace Pacific\GatewayWordpress\App\Component;

use Pacific\GatewayWordpress\Kernel\App;
use Pacific\GatewayWordpress\Kernel\Initializer\ComponentInitializationInterface;

abstract class BaseComponent implements ComponentInitializationInterface
{
    protected $hookInitializer;

    public function __construct()
    {
        $this->hookInitializer = App::get('hookInitializer');
    }

    abstract public function boot();
}