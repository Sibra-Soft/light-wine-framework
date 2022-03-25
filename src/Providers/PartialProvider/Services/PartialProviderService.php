<?php
namespace LightWine\Providers\PartialProvider\Services;

use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\HttpResponse;
use LightWine\Core\Helpers\RequestVariables;

use Rct567\DomQuery\DomQuery;

class PartialProviderService {
    private TemplatingService $templatingService;

    public function __construct(){
        $this->templatingService = new TemplatingService();
    }

    public function HandlePartialRequest(): string {
        $templateId = RequestVariables::Get("template");
        $partialName = RequestVariables::Get("name");

        // Get and render the template
        $templateContent = $this->templatingService->RenderTemplateAndDoAllReplacements($templateId);

        // Get the specified partial content
        $domQuery = new DomQuery($templateContent->Content);
        $partialContent = $domQuery->find("div[data-type='template'][data-name='$partialName']")->getInnerHtml();

        if(StringHelpers::IsNullOrWhiteSpace($partialContent)){
            HttpResponse::ShowError(400, "The specified partial could not be found in the specified template", "Partial not found");
        }else{
            return $partialContent;
        }

        return "";
    }
}
?>