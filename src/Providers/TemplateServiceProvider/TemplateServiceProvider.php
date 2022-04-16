<?php
namespace LightWine\Providers\TemplateServiceProvider;

use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\HttpResponse;

class TemplateServiceProvider
{
    private TemplatesService $templateService;
    private TemplatingService $templatingService;

    public function __construct(){
        $this->templateService = new TemplatesService();
        $this->templatingService = new TemplatingService();
    }

    public function Render(){
        $templateName = RequestVariables::Get("templatename");

        if(StringHelpers::IsNullOrWhiteSpace($templateName)) HttpResponse::ShowError(404, "Not found", "Template could not be found");

        $template = $this->templateService->GetTemplateByName($templateName);

        if($template->Id == 0){
            HttpResponse::ShowError(404, "Not found", "Template could not be found");
        }else{
            $template = $this->templatingService->RenderTemplateAndDoAllReplacements($template->Id);

            HttpResponse::SetContentType("text/html");
            HttpResponse::SetData($template->Content);
            exit();
        }
    }
}