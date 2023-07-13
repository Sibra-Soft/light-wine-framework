<?php
namespace LightWine\Providers\ModuleServiceProvider;

use LightWine\Modules\SiteExtensions\Services\SiteExtensionsService;

class ModuleServiceProvider {
    private SiteExtensionsService $siteExtensionsService;

    public function __construct(){
        $this->siteExtensionsService = new SiteExtensionsService();
    }

    public function Render(){
        $extName = RequestVariables::Get("name");
        
        echo($this->siteExtensionsService->RunExtension($extName));
    }
}
?>