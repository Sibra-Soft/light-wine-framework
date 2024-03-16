<?php
namespace LightWine\Providers\ModuleServiceProvider;

use LightWine\Modules\ModuleRunner\Services\ModuleRunnerService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Core\Helpers\RequestVariables;

class ModuleServiceProvider {
    private TemplatesService $templateService;
    private ModuleRunnerService $moduleRunner;

    public function __construct(){
        $this->moduleRunner = new ModuleRunnerService();
        $this->templateService = new TemplatesService();
    }

    public function Render(){
        $module = RequestVariables::Get("name");
        
        return $this->moduleRunner->RunCmsModule($module);
    }
}
?>