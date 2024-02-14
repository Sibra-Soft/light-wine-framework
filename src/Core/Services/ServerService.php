<?php
namespace LightWine\Core\Services;

use LightWine\Core\Interfaces\IServerService;
use LightWine\Core\HttpResponse;
use LightWine\Core\HttpRequest;
use LightWine\Core\Enums\RouteTypeEnum;
use LightWine\Modules\Logger\Services\LoggerService;
use LightWine\Modules\Api\Services\ApiService;
use LightWine\Modules\Routing\Models\RouteModel;
use LightWine\Modules\Routing\Models\ViewRouteModel;
use LightWine\Modules\Routing\Services\RoutingService;

class ServerService implements IServerService
{
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
    private function ExecuteController(RouteModel $route): string {
        $module = str_replace("@", "", $route->Action);

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
        switch($route->Middleware){
            case RouteTypeEnum::WEBVIEW: $content = $this->pageService->Render(new ViewRouteModel($route))->Content; break;
            case RouteTypeEnum::API: $content = $this->apiService->Execute($route); break;
            case RouteTypeEnum::CONTROLLER: $content = $this->ExecuteController($route); break;
            case RouteTypeEnum::REDIRECT: HttpResponse::Redirect($route->Url, []); break;

            default: Throw new \Exception('Specified middleware not found: '.$route->Middleware, 1050);
        }

        $this->logger->LogSiteVisitor();

        return $content;
    }
}