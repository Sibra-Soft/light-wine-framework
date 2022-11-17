<?php
namespace LightWine\Modules\Api\Services;

use LightWine\Core\HttpResponse;
use LightWine\Modules\Api\Models\ApiRequestModel;
use LightWine\Modules\Sam\Services\SamService;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Api\Services\ApiQueryService;
use LightWine\Modules\Api\Enums\RequestMethodesEnum;

class ApiService
{
    private ApiRequestModel $RequestModel;
    private SamService $samService;
    private MysqlConnectionService $databaseService;
    private ApiQueryService $apiQueryService;

    public function __construct(){
        $this->samService = new SamService();
        $this->RequestModel = new ApiRequestModel;
        $this->databaseService = new MysqlConnectionService();
        $this->apiQueryService = new ApiQueryService($this->RequestModel);
    }

    /**
     * This is the startup function of the class
     */
    public function Start() {
        if(!$this->samService->CheckIfUserIsLoggedin()){
            $this->RestServerReturnJson("You are unauthorized to access the requested resource.", "UNAUTHORIZED", 401);
        }

        $this->GetHandlerAndParametersBasedOnRoute();
        $this->Authenticate();
        $this->FillParametersBasedOnRequestData();
        $this->CheckParameterDataTypes();

        $this->HandleCurrentRequest();
    }

    /**
     * This function can be used for generating a default output message
     * @param mixed $message The message you want to show, can be text or a array
     * @param string $categorie The category of the current message
     * @param int $code The error code of the current message
     */
    private function RestServerReturnJson($message, string $categorie, int $code){
        HttpResponse::SetReturnJson([
            "message" => $message,
            "categorie" => $categorie,
            "code" => $code
        ]);
    }

    /**
     * This function start the authentication process using the bearer token specified in the header
     */
    private function Authenticate(){
        $authorizationHeader = $this->RequestModel->Headers["Authorization"];
        $authorizationBearer = "Bearer ".$_SESSION["ClientToken"];

        if(empty($authorizationHeader)){
            $this->RestServerReturnJson("No Authorization header specified", "MISSING ARGUMENTS", 419);
        }

        if(!($authorizationHeader == $authorizationBearer)){
            $this->RestServerReturnJson("You are unauthorized to access the requested resource.", "UNAUTHORIZED", 401);
        }

        if(!in_array($this->RequestModel->RequestMethod, $this->RequestModel->AllowedMethodes)){
            $this->RestServerReturnJson("Current request methode is not allowed for the requested resource", "FORBIDDEN", 403);
        }
    }

    /**
     * This function searches for the handler in the database based on the current request uri
     * If the handler is found the attached parameters are added to the request object parameters array
     */
    private function GetHandlerAndParametersBasedOnRoute(){
        $routeFound = false;
        $routeId = 0;
        $routeParameters = [];

        // Check if the route exists
        $this->databaseService->AddParameter("method", $this->RequestModel->RequestMethod);
        $this->databaseService->AddParameter("request_url", $this->RequestModel->ApiRequestPath);
        $this->databaseService->GetDataset("
            SELECT
                `id`,
                CONCAT(`match_pattern`) AS `match_pattern`
            FROM `site_rest_api`
            WHERE allowed_methodes = ?method
                AND ?request_url REGEXP match_pattern
            ORDER BY `order`
            LIMIT 1
        ");

        if($this->databaseService->rowCount > 0){
            preg_match_all($this->databaseService->DatasetFirstRow("match_pattern"), $this->RequestModel->ApiRequestPath, $matches);

            $routeParameters = (is_null($matches) ? [] : $matches);
            $routeFound = true;
            $routeId = $this->databaseService->DatasetFirstRow("id");
        }

        if($routeFound){
            // Get the parameters based on the route
            $this->databaseService->ClearParameters();
            $this->databaseService->AddParameter("apiId", $routeId);
            $dataset = $this->databaseService->GetDataset("
                SELECT
                    parameter,
                    data_type,
                    IFNULL(is_key, 0) AS is_key,
                    IFNULL(is_required, 0) AS is_required ,
                    IFNULL(default_value, '') AS default_value,
                    api.datasource,
                    api.allowed_methodes
                FROM `site_rest_api_parameters` AS parameters
                INNER JOIN site_rest_api AS api ON api.id = parameters.api_id
                WHERE parameters.api_id = ?apiId
            ");

            $parameters = [];
            foreach($dataset as $row){
                $parameters[$row["parameter"]]["Name"] = $row["parameter"];
                $parameters[$row["parameter"]]["DataType"] = $row["data_type"];
                $parameters[$row["parameter"]]["IsPrimaryKey"] = (bool)$row["is_key"];
                $parameters[$row["parameter"]]["IsRequired"] = (bool)$row["is_required"];
                $parameters[$row["parameter"]]["DefaultValue"] = $row["default_value"];
                $parameters[$row["parameter"]]["Value"] = "";
            }

            $this->RequestModel->AllowedMethodes = explode(",", strtolower($this->databaseService->DatasetFirstRow("allowed_methodes")));
            $this->RequestModel->DatasourceName = StringHelpers::SplitString($this->databaseService->DatasetFirstRow("datasource"), ";", 1);
            $this->RequestModel->DatasourceType = StringHelpers::SplitString($this->databaseService->DatasetFirstRow("datasource"), ";", 0);
            $this->RequestModel->Parameters = $parameters;
            $this->RequestModel->RouteParameters = $routeParameters;
        }else{
            $this->RestServerReturnJson("Not found", "API PATH NOT FOUND", 404);
        }
    }

    /**
     * This function adds the values of the parameters to the parameters array
     */
    private function FillParametersBasedOnRequestData(){
        $requestVariables = [];
        parse_str(file_get_contents("php://input"), $requestVariables);

        // Add the values of the parameters from the request
        foreach($requestVariables as $key => $value){
            $this->RequestModel->Parameters[$key]["Value"] = $value;
        }

        // Add the values of the parameters from the request uri
        foreach($this->RequestModel->RouteParameters as $key => $value){
            if(array_key_exists($key, $this->RequestModel->Parameters)){
                $value = (is_array($value)) ? $value[0]: $value;
                $this->RequestModel->Parameters[$key]["Value"] = $value;
            }
        }

        // Check the created parameters
        foreach($this->RequestModel->Parameters as $parameter){
            if(!array_key_exists("Name", $parameter)){
                $this->RestServerReturnJson("Unnecessary parameter declaration", "UNNECESSARY_PARAMETER", 104);
            }

            if(StringHelpers::IsNullOrWhiteSpace($parameter["Value"])){
                if($parameter["IsRequired"]){
                    $this->RestServerReturnJson("Required parameter not set: ".$parameter["Name"], "REQUIRED_PARAMETER", 103);
                }else{
                    unset($this->RequestModel->Parameters[$parameter["Name"]]);
                }
            }
        }
    }

    /**
     * This function checks the values against the parameter datatype
     */
    private function CheckParameterDataTypes(){
        foreach($this->RequestModel->Parameters as $parameter){
            switch($parameter["DataType"]){
                case "string":
                    if(!is_string($parameter["Value"])){
                        $this->RestServerReturnJson("Parameter type mismatch for parameter: ".$parameter["Name"], "TYPE MISMATCH", 101);
                    }
                    break;

                case "integer":
                    if(!is_numeric($parameter["Value"])){
                        $this->RestServerReturnJson("Parameter type mismatch for parameter: ".$parameter["Name"], "TYPE MISMATCH", 101);
                    }
                    break;

                case "date":
                    if(!StringHelpers::IsValidDate($parameter["Value"])){
                        $this->RestServerReturnJson("Parameter type mismatch for parameter: ".$parameter["Name"], "TYPE MISMATCH", 101);
                    }
                    break;
            }
        }
    }

    /**
     * After all the data is collected, the request is run and all the queries are executed
     */
    private function HandleCurrentRequest(){
        if($this->RequestModel->DatasourceType === "query"){
            $this->apiQueryService->HandleQueryRequest();
        }else{
            switch (strtoupper($this->RequestModel->RequestMethod)) {
                case RequestMethodesEnum::GET: $this->apiQueryService->HandleSelectRequest(); break;
                case RequestMethodesEnum::POST: $this->apiQueryService->HandleInsertRequest(); break;
                case RequestMethodesEnum::DELETE: $this->apiQueryService->HandleDeleteRequest(); break;
                case RequestMethodesEnum::PUT: $this->apiQueryService->HandleUpdateRequest(); break;

                default: return array("count" => 0, "data" => [], "error" => "unknown request type");
            }
        }
    }
}
?>