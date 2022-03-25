<?php
namespace LightWine\Modules\ServiceProvider\Services;

use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Modules\Resources\Services\ResourceService;
use LightWine\Core\Models\PageModel;
use LightWine\Providers\ImageProvider\Services\ImageProviderService;
use LightWine\Providers\TemplateProvider\Services\TemplateProviderService;
use LightWine\Core\Services\ComponentsService;
use LightWine\Providers\JsonProvider\Services\JsonProviderService;
use LightWine\Providers\ModuleProvider\Services\ModuleProviderService;
use LightWine\Modules\Api\Services\ApiService;
use LightWine\Providers\PartialProvider\Services\PartialProviderService;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Scheduler\Services\SchedulerService;
use LightWine\Providers\Imdb\Services\ImdbApiProviderService;
use LightWine\Core\Helpers\RequestVariables;

class ServiceProviderService
{
    private ImageProviderService $imageProviderService;
    private TemplateProviderService $templateProviderService;
    private ComponentsService $componentService;
    private JsonProviderService $jsonProviderService;
    private ModuleProviderService $moduleProviderService;
    private PartialProviderService $partialProviderService;
    private ApiService $apiService;
    private SchedulerService $scheduler;
    private ImdbApiProviderService $imdbProviderService;

    public function __construct(){
        $this->imageProviderService = new ImageProviderService();
        $this->templateProviderService = new TemplateProviderService();
        $this->componentService = new ComponentsService();
        $this->jsonProviderService = new JsonProviderService();
        $this->moduleProviderService = new ModuleProviderService();
        $this->partialProviderService = new PartialProviderService();
        $this->apiService = new ApiService();
        $this->scheduler = new SchedulerService();
        $this->imdbProviderService = new ImdbApiProviderService();
    }

    public function CheckForServiceRequest($requestUri): PageModel {
        $pageModel = new PageModel;

        switch ($requestUri) {
            case "/resources.dll": $pageModel->Content = $this->GetResources(RequestVariables::Get("filename"), RequestVariables::Get("type"), (bool)RequestVariables::Get("single")); break;
            case "/images.dll": $pageModel->Content = $this->GetImage($pageModel); break;
            case "/template.dll": $pageModel->Content = $this->GetTemplate(); break;
            case "/component.dll": $pageModel->Content = $this->GetComponent(); break;
            case "/logoff.dll": HttpContextHelpers::Logoff(); break;
            case "/json.dll": $pageModel->Content = $this->GetJson($pageModel); break;
            case "/module.dll": $pageModel->Content = $this->GetModule(); break;
            case "/api.dll": $pageModel = $this->GetApiCall(); break;
            case "/partial.dll": $pageModel->Content = $this->GetPartial(); break;
            case "/scheduler.dll": $pageModel->Content = $this->scheduler->CheckForScheduledEvents(); break;
            case "/imdb.dll": $pageModel->Content = $this->GetFromImdb(); break;
            case "/app-config.json": HttpContextHelpers::Logoff(); break;

            default: $pageModel->Content = "";
        }

        return $pageModel;
    }

    /**
     * This function gets a resource file for example: Javascript or CSS
     * @param string $filename
     * @param string $type
     * @param bool $single
     */
    private function GetResources(string $filename, string $type, bool $single): string {
        $resources = new ResourceService();
        return $resources->GetResourcesBasedOnFilename($filename, $type, $single);
    }

    private function GetImage(PageModel $page): string {
        return $this->imageProviderService->HandleImageRequest($page);
    }

    private function GetTemplate(): string {
        return $this->templateProviderService->HandleTemplateRequest();
    }

    private function GetComponent(): string {
        return $this->componentService->HandleRenderComponent(RequestVariables::Get("name"));
    }

    private function GetJson(PageModel $page): string {
        return $this->jsonProviderService->HandleJsonRequest($page);
    }

    private function GetModule(): string {
        $moduleResponse = $this->moduleProviderService->RunModule(RequestVariables::Get("name"));

        if(StringHelpers::IsNullOrWhiteSpace($moduleResponse)){
            exit();
        }else{
            return $moduleResponse;
        }
    }

    private function GetApiCall(): PageModel {
        return $this->apiService->Start();
    }

    private function GetPartial(): string {
        return $this->partialProviderService->HandlePartialRequest();
    }

    private function GetFromImdb(): string {
        $searchMovieValue = RequestVariables::Get("search-movie");
        $searchSerieValue = RequestVariables::Get("search-serie");
        $titleId = RequestVariables::Get("title");
        $seasonNr = RequestVariables::Get("season");

        header('Content-Type: application/json; charset=utf-8');
        if(!StringHelpers::IsNullOrWhiteSpace($searchMovieValue)){
            return json_encode($this->imdbProviderService->SearchMovie($searchMovieValue));
        }else{
            if(!StringHelpers::IsNullOrWhiteSpace($searchSerieValue)){
                return json_encode($this->imdbProviderService->SearchSerie($searchSerieValue));
            }else{
                if(!StringHelpers::IsNullOrWhiteSpace($seasonNr)){
                    return json_encode($this->imdbProviderService->GetSerieSeasonEpisodes($titleId, $seasonNr));
                }else{
                    return json_encode($this->imdbProviderService->GetTitleBasedOnImdbId($titleId));
                }
            }
        }
    }
}