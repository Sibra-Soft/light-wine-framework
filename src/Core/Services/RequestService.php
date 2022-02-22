<?php
namespace LightWine\Core\Services;

use LightWine\Core\Models\RequestModel;
use LightWine\Modules\Routes\Services\RouteService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Modules\ServiceProvider\Services\ServiceProviderService;
use LightWine\Core\Interfaces\IRequestService;

use \DateInterval;

class RequestService implements IRequestService
{
    private RouteService $routeService;
    private ServiceProviderService $serviceProviderService;

    public function __construct(){
        $this->routeService = new RouteService();
        $this->serviceProviderService = new ServiceProviderService();
    }

    /**
     * Generates various tokens, that can be used in the framework
     */
    private function GenerateTokens(){
        if(array_key_exists("CsrfToken", $_SESSION) == false) $_SESSION["CsrfToken"]  = uniqid(time());
        if(array_key_exists("ClientToken", $_SESSION) == false) $_SESSION["ClientToken"] = hash("sha1", time());
        if(array_key_exists("SessionStartTime", $_SESSION) == false) $_SESSION["SessionStartTime"] = Helpers::Now()->format("h:m:s");
        if(array_key_exists("SessionEndTime", $_SESSION) == false) $_SESSION["SessionEndTime"] = Helpers::Now()->add(new DateInterval('PT20M'))->format("h:m:s");
    }

    /**
     * Searches for the route based on the current request url
     * @return RequestModel Model containing all details of the current request
     */
    public function GetRouteBasedOnRequestUrl(): RequestModel {
        $requestModel = new RequestModel;

        $this->GenerateTokens();

        $requestUrlWithoutQuerystring = strtok($_SERVER["REQUEST_URI"], '?'); // Get the url without querystring

        $requestModel->Form = $_POST;
        $requestModel->Querystring = $_GET;
        $requestModel->Headers = getallheaders();
        $requestModel->RequestTime = Helpers::Now();
        $requestModel->RequestUrl = $requestUrlWithoutQuerystring;
        $requestModel->Route = $this->routeService->GetRouteBasedOnUrl($requestUrlWithoutQuerystring);

        return $requestModel;
    }
}