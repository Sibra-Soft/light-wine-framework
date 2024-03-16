<?php
namespace LightWine\Modules\ModuleRunner\Services;

use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\HttpResponse;
use LightWine\Modules\Templates\Services\TemplatesService;

class ModuleRunnerService {
    private TemplatesService $templateService;

    public function __construct(){
        $this->templateService = new TemplatesService();
    }

    public function RunCmsModule(string $moduleName): string {
        $returnContent = "";

        $template = $this->templateService->GetTemplateByName($moduleName, "module");

        if(!$template->Found){
            HttpResponse::ShowError(404, "The specified module or method could not be found", "Method not found");
        }

        $template = $template->Content;

        preg_match_all('/@import\((\'.*?\')\)/', $template, $matches);
        foreach($matches[0] as $templateVariable){
            $valName = StringHelpers::StringBetween($templateVariable, "@", "(");
            $valValue = str_replace("'", "", StringHelpers::StringBetween($templateVariable, "(", ")"));

            if($variableValue === "ReturnContent"){
                $returnPageContent = true;
                $valValue = str_replace('/', '\\', $valValue);
                $template = str_replace($templateVariable, "", $template);
            }else{
                $valValue = str_replace('/', '\\', $valValue);
                $template = str_replace($templateVariable, "use ".$valValue.";", $template);
            }
        }

        $className = "Page".Helpers::RandomInteger(0, 100);
        $template = str_replace("class Page", "class ".$className, $template);

        eval($template);

        $pageObject = new $className;
        if(!method_exists($pageObject , "Init")){
            Throw new \Exception("The specified module does not contain a Page class with Init function");
        }else{
            $returnContent = call_user_func(array($pageObject, 'Init'));
        }

        return $returnContent;
    }
}