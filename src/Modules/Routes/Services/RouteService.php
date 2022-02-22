<?php
namespace LightWine\Modules\Routes\Services;

use LightWine\Core\Models\RouteModel;
use LightWine\Modules\Database\Services\MysqlConnectionService;

class RouteService
{
    private MysqlConnectionService $databaseConnection;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
    }

    /**
     * Get the parameters from the route
     * @param array $arr The array of parameters
     * @return array Array of parameters
     */
    private function GetParameters(array $arr): array {
        foreach ($arr as $key => $value) {
            if (is_int($key)) {
                unset($arr[$key]);
            }
        }

        return $arr;
    }

    /**
     * Add the parameters of the array to the GET
     * @param array $parameters array containing the parameters to add
     */
    private function AddParameters(array $parameters){
        foreach ($parameters as $Parameter => $Value){
            $_GET[$Parameter] = str_replace("/", "", $Value);
        }
    }

    /**
     * Get the route based on the current request url
     * @param string $url The url a route must be searched for
     * @return RouteModel Model containing all the details of the found route
     */
    public function GetRouteBasedOnUrl(string $url): RouteModel {
        $routeModel = new RouteModel;

        $domain = $_SERVER['HTTP_HOST'];

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("currentDomain", $domain);
        $this->databaseConnection->AddParameter("requestUrl", $url);
        $this->databaseConnection->GetDataset("
            SELECT
                `name`,
		        id,
		        template_id,
		        url,
                meta_title,
                meta_description,
                CONCAT('/', `match_pattern`, '/') AS `match_pattern`,
                allowed_methodes,
                type,
                published
            FROM `site_pages`
            WHERE ?requestUrl REGEXP match_pattern
	            AND (domain = ?currentDomain OR domain IS NULL);
            ORDER BY `order`
            LIMIT 1
        ");

        if($this->databaseConnection->rowCount > 0){
            if (preg_match($this->databaseConnection->DatasetFirstRow("match_pattern"), $url, $matches)) {
                $this->AddParameters($this->GetParameters($matches));

                $routeModel->Name = $this->databaseConnection->DatasetFirstRow("name");
                $routeModel->Variables = $this->GetParameters($matches);
                $routeModel->Datasource = $this->databaseConnection->DatasetFirstRow("template_id");
                $routeModel->Url = $this->databaseConnection->DatasetFirstRow("url");
                $routeModel->MetaTitle = $this->databaseConnection->DatasetFirstRow("meta_title");
                $routeModel->MetaDescription = $this->databaseConnection->DatasetFirstRow("meta_description");
                $routeModel->AllowedMethodes = explode(",", $this->databaseConnection->DatasetFirstRow("allowed_methodes"));
                $routeModel->Type = $this->databaseConnection->DatasetFirstRow("type");
                $routeModel->Published = $this->databaseConnection->DatasetFirstRow("published", "boolean");
            }
        }else{
            $routeModel->NotFound = true;
        }

        return $routeModel;
    }
}