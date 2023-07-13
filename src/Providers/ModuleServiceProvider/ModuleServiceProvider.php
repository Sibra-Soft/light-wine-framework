<?php
namespace LightWine\Providers\ModuleServiceProvider;

use LightWine\Core\Helpers\RequestVariables;
use LightWine\Modules\SiteModule\Services\SiteModuleService;

class ModuleServiceProvider {
    private SiteModuleService $siteExtensionsService;

    public function __construct(){
        $this->siteExtensionsService = new SiteModuleService();
    }

    public function Render(){
        $extName = RequestVariables::Get("name");
        
        echo($this->siteExtensionsService->RunModule($extName));
    }
}
?>