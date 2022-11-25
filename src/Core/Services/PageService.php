<?php
namespace LightWine\Core\Services;

use LightWine\Core\Models\PageModel;
use LightWine\Modules\Routing\Models\RouteModel;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Modules\Resources\Services\ResourceService;
use LightWine\Modules\Sam\Services\SamService;
use LightWine\Core\Interfaces\IPageService;
use LightWine\Core\HttpResponse;

class PageService implements IPageService
{
    private TemplatesService $templateService;
    private TemplatingService $templatingService;
    private ResourceService $resourceService;
    private SamService $samService;

    public function __construct(){
        $this->templateService = new TemplatesService();
        $this->templatingService = new TemplatingService();
        $this->resourceService = new ResourceService();
        $this->samService = new SamService();
    }

    /** This function generates the Javascript page class */
    private function GeneratePageJsClass(){
        $content = "";

        $content .= '<script type="text/javascript" >';
        $content .= "window.page = {};";
        $content .= "window.page.csrf_token = '".$_SESSION["CsrfToken"]."';";
        $content .= "window.page.client_token = '".$_SESSION["ClientToken"]."';";
        $content .= "window.page.expiration_time = new Date('".$_SESSION["SessionEndTime"]."');";
        $content .= "window.page.start_time = new Date('".$_SESSION["SessionStartTime"]."');";
        $content .= '</script>';

        return $content;
    }

    /**
     * Render the requested page and fill the pagemodel
     * @return PageModel Model containing all the details of the requested page
     */
    public function Render(RouteModel $route): PageModel {
        $pageModel = new PageModel;

        $start = microtime(true); // Start recording the render time
        $masterpage = $this->templateService->GetTemplateByName("masterpage");
        $template = $this->templateService->GetTemplateById($route->Datasource);

        // Check if a user must be loggedin
        if($template->Policies->USERS_MUST_LOGIN and !$this->samService->CheckIfUserIsLoggedin())  HttpResponse::Redirect("/", [], 301);
        if($template->Policies->ENABLE_BASIC_AUTHENTICATION) $this->samService->BasicAuthentication();

        // Get the template of the page
        $pageTemplate = $this->templatingService->RenderTemplateAndDoAllReplacements($template);

        // Add the static masterpage variables
        $result = $masterpage->Content;
        $result = str_replace('{{$pageJavascript}}', $this->GeneratePageJsClass(), $result);
        $result = str_replace('{{$pageTitle}}', $route->MetaTitle, $result);
        $result = str_replace('{{$pageDescription}}', $route->MetaDescription, $result);
        $result = str_replace('{{$pageContent}}', $pageTemplate->Content, $result);
        $result = str_replace('{{$pageStylesheets}}', $this->resourceService->GenerateResourceURL("styling", $pageTemplate), $result);
        $result = str_replace('{{$pageScripts}}', $this->resourceService->GenerateResourceURL("scripts", $pageTemplate), $result);

        $pageModel->Content = $result;
        $pageModel->SizeInBytes = strlen($result);
        $pageModel->RenderTimeInMs = round(microtime(true) - $start * 1000); // Stop recording the render time

        return $pageModel;
    }
}