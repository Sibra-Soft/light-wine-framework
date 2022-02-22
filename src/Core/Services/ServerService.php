<?php
namespace LightWine\Core\Services;

use LightWine\Core\Models\RequestModel;
use LightWine\Core\Models\ResponseModel;
use LightWine\Core\Enums\RouteTypeEnum;
use LightWine\Modules\ServiceProvider\Services\ServiceProviderService;
use LightWine\Modules\Api\Services\ApiService;
use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\Interfaces\IServerService;
use LightWine\Modules\Logger\Services\LoggerService;

class ServerService implements IServerService
{
    private RequestModel $request;
    private PageService $pageService;
    private ServiceProviderService $serviceProviderService;
    private ApiService $apiService;
    private LoggerService $logger;

    public function __construct(RequestModel $requestModel){
        $this->apiService = new ApiService();
        $this->serviceProviderService = new ServiceProviderService();
        $this->pageService = new PageService($requestModel->Route);
        $this->request = $requestModel;
        $this->logger = new LoggerService();
    }

    /**
     * Starts a new instance of the framework server
     * @return ResponseModel Model containing all the details of the current response
     */
    public function Start(): ResponseModel {
        $responseModel = new ResponseModel;

        if($this->request->Route->NotFound){
            $responseModel->Page = $this->serviceProviderService->CheckForServiceRequest($this->request->RequestUrl);

            if(StringHelpers::IsNullOrWhiteSpace($responseModel->Page->Content)){
                HttpContextHelpers::ShowError(404, "Not found", "The specified content could not be found");
            }
        }else{
            switch($this->request->Route->Type){
                case RouteTypeEnum::Channel: $responseModel->Page = $this->serviceProviderService->CheckForServiceRequest($this->request->Route->Url); break;
                case RouteTypeEnum::TemplateLink: $responseModel->Page = $this->pageService->Render(); break;
                case RouteTypeEnum::ApiHandler: $responseModel->Page->Content = $this->apiService->Start(); break;
            }

            // Add the headers to the server response
            foreach($responseModel->Page->Headers as $key => $value){
                header($key.":".$value);
            }
        }

        $this->logger->LogSiteVisitor();

        return $responseModel;
    }

    public function ShowFileBrowser(string $path){

    }

    public function ShowSimplePage(string $content){

    }
}