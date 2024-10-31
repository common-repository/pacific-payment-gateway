<?php

namespace Pacific\GatewayWordpress\Kernel\Loader;

use Pacific\GatewayWordpress\Kernel\App;

class ScriptsLoader
{
    private $version;
    private $scripts;
    private $styles;
    private $hookInitializer;

    public function __construct()
    {
        $this->hookInitializer = App::get('hookInitializer');

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->version = time();
        } else {
            $this->version = App::get('PACIFIC_PLUGIN_VERSION');
        }
    }

    public function setStyles($styles)
    {
        $this->styles = $styles;

        return $this;
    }

    public function setScripts($scripts)
    {
        $this->scripts = $scripts;

        return $this;
    }

    public function enqueueStyles() {
        if (is_null($this->styles)) {
            return;
        }

        foreach ($this->styles as $style) {
            wp_enqueue_style(
                $style->name,
                App::get('PACIFIC_PLUGIN_URL') . $style->path,
                property_exists($style, 'deps') ? $style->version : [],
                property_exists($style, 'version') ? $style->version : $this->version
            );
        }
    }

    public function enqueueScripts() {
        if (is_null($this->scripts)) {
            return;
        }

        foreach ($this->scripts as $script) {
            wp_enqueue_script(
                $script->name,
                App::get('PACIFIC_PLUGIN_URL') . $script->path,
                property_exists($script, 'deps') ? $script->version : [],
                property_exists($script, 'version') ? $script->version : $this->version,
                property_exists($script, 'in_footer') ? $script->in_footer : true
            );
        }

    }

    public function load()
    {
        $this->hookInitializer->addAction('wp_enqueue_scripts', $this, 'enqueueScripts');
        $this->hookInitializer->addAction('wp_enqueue_scripts', $this, 'enqueueStyles');
        $this->hookInitializer->addAction('wp_enqueue_scripts', $this, 'enqueueInPostStyles');
        $this->hookInitializer->addAction('wp_enqueue_scripts', $this, 'enqueuePayuScript');
        $this->hookInitializer->addAction('wp_enqueue_scripts', $this, 'enqueueInPostScript');
    }

    public function enqueuePayuScript() {
	    if(!is_product()) {
		    return;
	    }

        wp_enqueue_script(
            'pacific-payu',
            App::get('pacificGateway')->configuration->getPayuScript()
        );
    }

    public function enqueueInPostScript() {
		if(!is_product()) {
			return;
		}
        wp_enqueue_script(
            'pacific-inpost',
            App::get('pacificGateway')->configuration->getInPostScript()
        );
    }

    public function enqueueInPostStyles() {
		if(!is_product()) {
			return;
		}
	    wp_enqueue_style(
		    'pacific-inpost',
		    App::get('pacificGateway')->configuration->getInPostStyle()
	    );
    }
}
