<?php
namespace LightWine\Modules\Routing\Services;

use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Routing\Models\RouteModel;
use LightWine\Core\Route;
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
    private function RegisterRoutesAndParametersFromCms(){
        $domain = HttpRequest::Domain();
        $options = [];

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("currentDomain", $domain);

        // Add the routes
        $dataset = $this->databaseConnection->GetDataset("
            SELECT * FROM (
	            SELECT
		            routes.id,
		            IFNULL(routes.`name`, '') AS `name`,
		            routes.action,
		            routes.url,
		            routes.meta_title,
		            routes.meta_description,
		            routes.method,
		            routes.middleware,
		            routes.redirect_url,
		            routes.redirect_type,
		            routes.`order`
	            FROM `site_routes` AS routes
	            WHERE (domain = ?currentDomain  OR NULLIF(domain, '') IS NULL)
                    AND published = 1
            ) AS x
            ORDER BY x.`order`
        ");

        foreach($dataset as $row){
            if(!StringHelpers::IsNullOrWhiteSpace($row["meta_title"]) or !StringHelpers::IsNullOrWhiteSpace($row["meta_description"])){
                $options = ["title" => $row["meta_title"], "description" => $row["meta_description"]];
            }

            switch(strtoupper($row["method"])){
                case "GET": Route::Get($row["name"], $row["url"], $row["action"], $row["middleware"], $options); break;
                case "POST": Route::Post($row["name"], $row["url"], $row["action"], $row["middleware"], $options); break;
                case "PUT": Route::Put($row["name"], $row["url"], $row["action"], $row["middleware"], $options); break;
                case "DELETE": Route::Delete($row["name"], $row["url"], $row["action"], $row["middleware"], $options); break;
                case "PATCH": Route::Patch($row["name"], $row["url"], $row["action"], $row["middleware"], $options); break;
            }
        }

        // Add the parameters
        $dataset = $this->databaseConnection->GetDataset("
            SELECT
                CONCAT(LOWER(routes.method), '@', routes.`name`) AS route,

	            parameters.`name` AS name,
	            parameters.data_type AS type,
	            parameters.is_key,
	            parameters.is_required
            FROM `site_routes` AS routes
            INNER JOIN site_route_parameters AS parameters ON parameters.route_id = routes.id
            WHERE (domain = ''  OR NULLIF(domain, '') IS NULL)
			    AND published = 1
        ");

        foreach($dataset as $row){
            Route::RegisterRouteParameter($row["route"], $row["name"], $row["type"], $row["is_key"], $row["is_required"]);
        }
    }



    /**
     * Main function for getting the url from the current request
     * @param string $url The url of the current request
     * @return RouteModel The routemodel created from the current request
     */
    public function MatchRouteByUrl(string $url): RouteModel {
        $returnModel = new RouteModel;
        $cachedRoutes = $this->cacheService->GetArrayFromCacheBasedOnName("routing");
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);

        // Check if the routes are cached
        if(count($cachedRoutes) > 0){
            Route::$Routes = $cachedRoutes;

            TraceHelpers::Write("Loaded routing from the cache");
        }else{
            $this->RegisterRoutesAndParametersFromCms();
            $this->AddRoutingToCache();

            TraceHelpers::Write("Generated new routing tree, and added the information to the cache");
        }

        // Loop trough routes to match the current url
        foreach(Route::$Routes[$requestMethod] as $route){
            if(preg_match($route["regex_pattern"], $url, $matches)){
                $this->AddRoutingParametersToRequest($this->GetRoutingParameters($matches));

                $metaTitle = $route["options"]["title"];
                $metaDescription = $route["options"]["description"];

                $returnModel->NotFound = false;
                $returnModel->Action = $route["action"];
                $returnModel->Middleware = $route["middleware"];
                $returnModel->MatchPattern = $route["regex_pattern"];
                $returnModel->Name = $route["name"];
                $returnModel->Url = $url;
                $returnModel->MetaTitle = (StringHelpers::IsNullOrWhiteSpace($metaTitle) ? "" : $metaTitle);
                $returnModel->MetaDescription = (StringHelpers::IsNullOrWhiteSpace($metaDescription) ? "" : $metaDescription);
                $returnModel->Parameters = $route["parameters"];
                $returnModel->RoutingParams = $this->GetRoutingParameters($matches);

                break;
            }else{
                $returnModel->NotFound = true;
            }
        }

        return $returnModel;
    }
}