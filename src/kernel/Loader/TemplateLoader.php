<?php

namespace Pacific\GatewayWordpress\Kernel\Loader;

use Pacific\GatewayWordpress\Kernel\App;

class TemplateLoader
{
    private $templatePath;

    public function __construct()
    {
        $this->templatePath = App::get("PACIFIC_TEMPLATES_DIR");
    }

    public function getTemplateContent($file, $data = [])
    {
        $file = $file . '.php';
        if (!file_exists($this->templatePath . "/" . $file)) {
            throw new \Exception("File: " . $this->templatePath . "/" . $file . " doesn't exists.");
        }

        // create variables allowed in template
        $pacificPluginUrl = App::get('PACIFIC_PLUGIN_URL');
        $pacificPluginVersion = App::get('PACIFIC_PLUGIN_VERSION');
        extract($data);
        unset($data);

        ob_start();
        require $this->templatePath . "/" . $file;

        return ob_get_clean();
    }

    public function includeHtmlFile($file)
    {
        $lang = "pl"; // @todo: add handler for checking the current language

        $file = App::get('PACIFIC_FRONTEND_ASSETS_DIR') . '/' . $file . '_' . $lang . '.html';
        if (!file_exists($file)) {
            throw new \Exception("File: " . $file. " doesn't exists. Did you built a frontend dist of the plugin?");
        }

        $pacificPluginUrl = App::get('PACIFIC_PLUGIN_URL');

        $contents = file_get_contents($file);
        $contents = str_replace('__PACIFIC_PLUGIN_URL__', $pacificPluginUrl, $contents);

        echo $contents;
    }

}
