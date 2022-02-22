<?php
namespace LightWine\Providers\PartialProvider\Services;

use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Core\Helpers\StringHelpers;

use Rct567\DomQuery\DomQuery;

class PartialProviderService {
    private TemplatingService $templatingService;

    public function __construct(){
        $this->templatingService = new TemplatingService();
    }

    public function HandlePartialRequest(): string {
        $templateId = HttpContextHelpers::RequestVariable("template");
        $partialName = HttpContextHelpers::RequestVariable("name");

        // Get and render the template
        $templateContent = $this->templatingService->RenderTemplateAndDoAllReplacements($templateId);

        // Get the specified partial content
        $domQuery = new DomQuery($templateContent->Content);
        $partialContent = $domQuery->find("div[data-type='template'][data-name='$partialName']")->getInnerHtml();

        if(StringHelpers::IsNullOrWhiteSpace($partialContent)){
            HttpContextHelpers::ShowError(400, "The specified partial could not be found in the specified template", "Partial not found");
        }else{
            return $partialContent;
        }

        return "";
    }
}
?>