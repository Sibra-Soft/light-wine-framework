<?php
namespace LightWine\Modules\Tracing\Services;

use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Enums\EnvironmentEnum;
use LightWine\Core\HttpResponse;

class TracingService
{
    private ConfigurationManagerService $settings;

    public function __construct(){
        $this->settings = new ConfigurationManagerService();
    }

    /**
     * Generates the tracing and adds the output to the response
     */
    public function GenerateTracing(){
        if((bool)$this->settings->GetAppSetting("Tracing") && $this->settings->GetAppSetting("Environment") == EnvironmentEnum::Development){
            ob_start();
            include(Helpers::MapPath("~/views/trace.tpl"));
            $traceOutput = ob_get_contents();
            ob_end_clean();

            HttpResponse::$MinifyHtml = false;
            HttpResponse::SetData($traceOutput);
        }
    }
}