<?php
namespace LightWine\Core\Services;

use LightWine\Core\Models\RequestModel;
use LightWine\Modules\Api\Services\ApiService;
use LightWine\Core\Interfaces\IServerService;
use LightWine\Modules\Logger\Services\LoggerService;
use LightWine\Core\HttpResponse;
use LightWine\Core\HttpRequest;
use LightWine\Core\Enums\RouteTypeEnum;
use LightWine\Modules\Routing\Services\RoutingService;

class ServerService implements IServerService
{
    private RequestModel $request;
    private PageService $pageService;
    private ApiService $apiService;
    private LoggerService $logger;
    private RoutingService $routingService;

    public function __construct(){
        $this->apiService = new ApiService();
        $this->pageService = new PageService();
        $this->logger = new LoggerService();
        $this->routingService = new RoutingService();
    }

    /**
     * Runs a webmethod based on a file on the server or template from the cms
     * @param string $module The name of the module to run
     * @return string The return content of the function
     */
    private function RunWebMethod(string $module): string {
        if(class_exists('LightWine\\Providers\\'.$module.'\\'.$module)){
            $class = 'LightWine\\Providers\\'.$module.'\\'.$module;
            $pageObject = new $class;

            if(method_exists($pageObject , "Render")){
                return call_user_func(array($pageObject, 'Render'));
            }else{
                return "";
            }
        }

        return "";
    }

    /**
     * Starts a new instance of the framework server
     * @return string The content to return to the webbrowser
     */
    public function Start(): string {
        $content = "";
        $route = $this->routingService->MatchRouteByUrl(HttpRequest::RequestUrlWithoutQuerystring());

        // Show error page if the requested route could not be found
        if($route->NotFound) HttpResponse::ShowError(404, "Not found", "The specified content could not be found");

        // Check the type of the current route
        switch($route->Type){
            case RouteTypeEnum::VIEW: $content = $this->pageService->Render($route)->Content; break;
            case RouteTypeEnum::API_HANDLER: $content = $this->apiService->Start(); break;
            case RouteTypeEnum::WEBMETHOD: $content = $this->RunWebMethod($route->Datasource); break;
            case RouteTypeEnum::REDIRECT: HttpResponse::Redirect($route->Url, [], 302); break;
        }

        $this->logger->LogSiteVisitor();

        return $content;
    }
}