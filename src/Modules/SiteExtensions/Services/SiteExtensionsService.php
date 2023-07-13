<?php
namespace LightWine\Modules\SiteExtensions\Services;

use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\HttpResponse;
use LightWine\Modules\Templates\Services\TemplatesService;

class SiteExtensionsService
{
    private TemplatesService $templateService;

    public function __construct(){
        $this->templateService = new TemplatesService();
    }

    /**
     * Run site extensions from the cms
     * @param string $name The name of the extension you want to run
     */
    public function RunExtension(string $name){
        $template = $this->templateService->GetTemplateByName($name, "module");

        (bool)$returnPageContent = false;

        if(!$template->Found){
            HttpResponse::ShowError(404, "The specified module or method could not be found", "Method not found");
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
            call_user_func(array($pageObject, 'Init'));
        }else{
            HttpResponse::ShowError(404, "The specified module does not contain a Page class with Init function", "Method error");
        }
    }
}