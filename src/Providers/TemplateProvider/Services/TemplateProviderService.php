<?php
namespace LightWine\Providers\TemplateProvider\Services;

use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\HttpResponse;

class TemplateProviderService
{
    private TemplatesService $templateService;
    private TemplatingService $templatingService;

    public function __construct(){
        $this->templateService = new TemplatesService();
        $this->templatingService = new TemplatingService();
    }

    public function HandleTemplateRequest(): string {
        $templateName = RequestVariables::Get("templatename");

        if(StringHelpers::IsNullOrWhiteSpace($templateName)) HttpResponse::ShowError(404, "Not found", "Template could not be found");

        $template = $this->templateService->GetTemplateByName($templateName);
        $template = $this->templatingService->RenderTemplateAndDoAllReplacements($template->Id);

        return $template->Content;
    }
}