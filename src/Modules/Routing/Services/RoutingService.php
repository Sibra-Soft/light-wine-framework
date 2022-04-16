<?php
namespace LightWine\Modules\Routing\Services;

use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Routing\Models\RouteModel;
use LightWine\Core\Route;
use LightWine\Modules\RegexBuilder\Services\RegexBuilderService;
use LightWine\Modules\Cache\Services\CacheService;
use LightWine\Core\HttpRequest;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Routing\Interfaces\IRoutingService;
use LightWine\Core\Helpers\TraceHelpers;

class RoutingService implements IRoutingService
{
    private MysqlConnectionService $databaseConnection;
    private CacheService $cacheService;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
        $this->cacheService = new CacheService();
    }

    /**
     * Gets the parameters from the current routing pattern
     * @param array $arr The parameter matches
     * @return array The parameters from the current route
     */
    private function GetRoutingParameters(array $arr): array {
        foreach ($arr as $key => $value) {
            if (is_int($key)) {
                unset($arr[$key]);
            }
        }

        return $arr;
    }

    /**
     * Adds the current parameters to the request
     * @param array $parameters Array of parameters
     */
    private function AddRoutingParametersToRequest(array $parameters){
        foreach ($parameters as $Parameter => $Value){
            $_GET[$Parameter] = str_replace("/", "", $Value);
        }
    }

    /**
     * Adds the current routes to the cache
     */
    private function AddRoutingToCache(){
        $this->cacheService->AddArrayToCache("routing", Route::$Routes);
    }

    /**
     * Gets the routes added by the cms system
     */
    private function GetRoutesFromCms(){
        $domain = HttpRequest::Domain();

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("currentDomain", $domain);

        $dataset = $this->databaseConnection->GetDataset("
            SELECT
                `name`,
                id,
                template_id,
                url,
                meta_title,
                meta_description,
                method,
                type
            FROM `site_routes`
            WHERE (domain = ?currentDomain  OR NULLIF(domain, '') IS NULL)
                AND published = 1
            ORDER BY `order`
        ");

        foreach($dataset as $row){
            if($row["type"] == "template-link") Route::View($row["url"], $row["template_id"], ["title" => $row["meta_title"], "description" => $row["meta_description"]]);
            if($row["type"] == "redirect") Route::Redirect($row["url"], "http://www.google.nl");
        }
    }

    /**
     * This functions generates the matching pattern for urls
     * @param string $url The url to convert to a matching pattern
     * @return string The matching pattern
     */
    private function GenerateMatchingPattern(string $url): string {
        $parts = explode("/", $url);
        $pattern = RegexBuilderService::Expression()->startOfString("");
        $counter = 0;

        foreach($parts as $part){
            if(preg_match("/(?<=\{).+?(?=\})/", $part, $matches)){
                foreach($matches as $match){
                    $part = str_replace("{".$match."}", RegexBuilderService::Group($match)->raw(".*?"), $part);
                }
            }

            if($counter == count($parts)-1){
                $pattern->raw($part)->endOfString();
            }else{
                $pattern->raw($part)->raw("/");
            }

            $counter++;
        }

        $pattern = str_replace("/", "\/", $pattern);

        return $pattern;
    }

    /**
     * Main function for getting the url from the current request
     * @param string $url The url of the current request
     * @return RouteModel The routemodel created from the current request
     */
    public function MatchRouteByUrl(string $url): RouteModel {
        $returnModel = new RouteModel;
        $cachedRoutes = $this->cacheService->GetArrayFromCacheBasedOnName("routing");

        // Check if the routes are cached
        if(count($cachedRoutes) > 0){
            Route::$Routes = $cachedRoutes;

            TraceHelpers::Write("Loaded routing from the cache");
        }else{
            $this->GetRoutesFromCms();
            $this->AddRoutingToCache();

            TraceHelpers::Write("Generated new routing tree, and added the information to the cache");
        }

        // Loop trough routes to match the current url
        foreach(Route::$Routes as $route){
            $pattern = $this->GenerateMatchingPattern($route["url"]);

            if(preg_match($pattern, $url, $matches)){
                $this->AddRoutingParametersToRequest($this->GetRoutingParameters($matches));

                $metaTitle = $route["options"]["title"];
                $metaDescription = $route["options"]["description"];

                $returnModel->MatchPattern = $pattern;
                $returnModel->NotFound = false;
                $returnModel->MetaTitle = "";
                $returnModel->MetaDescription = "";
                $returnModel->Type = $route["type"];
                $returnModel->Datasource = $route["source"];
                $returnModel->MetaTitle = (StringHelpers::IsNullOrWhiteSpace($metaTitle) ? "" : $metaTitle);
                $returnModel->MetaDescription = (StringHelpers::IsNullOrWhiteSpace($metaDescription) ? "" : $metaDescription);

                break;
            }else{
                $returnModel->NotFound = true;
            }
        }

        return $returnModel;
    }
}