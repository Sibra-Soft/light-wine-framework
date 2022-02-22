<?php
namespace LightWine\Providers\ModuleProvider\Services;

use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\Helpers\Helpers;

class ModuleProviderService {
    private TemplatesService $templateService;

    public function __construct(){
        $this->templateService = new TemplatesService();
    }

    public function RunModule(string $module){
        $template = $this->templateService->GetTemplateByName($module, "module");
        (bool)$returnPageContent = false;

        if(!$template->Found){
            HttpContextHelpers::ShowError(404, "The specified module or method could not be found", "Method not found");
        }

        $template = $template->Content;
        preg_match_all('/@import\((\'.*?\')\)/', $template, $matches);
        foreach($matches[0] as $variable){
            $variableName = StringHelpers::StringBetween($variable, "@", "(");
            $variableValue = str_replace("'", "", StringHelpers::StringBetween($variable, "(", ")"));

            if($variableValue === "ReturnContent"){
                $returnPageContent = true;
                $variableValue = str_replace('/', '\\', $variableValue);
                $template = str_replace($variable, "", $template);
            }else{
                $variableValue = str_replace('/', '\\', $variableValue);
                $template = str_replace($variable, "use ".$variableValue.";", $template);
            }
        }

        $className = "Page".Helpers::RandomInteger(0, 100);
        $template = str_replace("class Page", "class ".$className, $template);

        eval($template);

        $pageObject = new $className;
        if(method_exists ($pageObject , "Init")){
            return call_user_func(array($pageObject, 'Init'));
        }else{
            HttpContextHelpers::ShowError(404, "The specified module does not contain a Page class with Init function", "Method error");
        }
    }
}
?>