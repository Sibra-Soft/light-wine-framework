<?php
namespace LightWine\Providers\PartialServiceProvider;

use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\HttpResponse;

use Rct567\DomQuery\DomQuery;

class PartialServiceProvider
{
    private TemplatingService $templatingService;

    public function __construct(){
        $this->templatingService = new TemplatingService();
    }

    public function Render(){
        $templateId = RequestVariables::Get("template");
        $partialName = RequestVariables::Get("name");

        // Show error if not all parameters are set
        if(StringHelpers::IsNullOrWhiteSpace($templateId) || StringHelpers::IsNullOrWhiteSpace($partialName)){
            HttpResponse::ShowError(400, "The specified partial could not be found in the specified template", "Partial not found");
        }

        // Get and render the template
        $templateContent = $this->templatingService->RenderTemplateAndDoAllReplacements($templateId);

        // Get the specified partial content
        $domQuery = new DomQuery($templateContent->Content);
        $partialContent = $domQuery->find("div[data-type='template'][data-name='$partialName']")->getInnerHtml();

        if(StringHelpers::IsNullOrWhiteSpace($partialContent)){
            HttpResponse::ShowError(400, "The specified partial could not be found in the specified template", "Partial not found");
        }else{
            HttpResponse::SetContentType("tet/html");
            HttpResponse::SetData($partialContent);
            exit();
        }
    }
}