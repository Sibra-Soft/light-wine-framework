<?php
namespace LightWine\Modules\Api\Services;

use LightWine\Core\HttpResponse;
use LightWine\Modules\Api\Models\ApiRequestModel;
use LightWine\Modules\Routing\Models\RouteModel;
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
     * Executes a api request based on the specified route details
     * @param RouteModel $route The details of the route
     * @return string The content to return
     */
    public function Execute(RouteModel $route): string {
        if(!$this->samService->CheckIfUserIsLoggedin()){
            $this->RestServerReturnJson("You are unauthorized to access the requested resource.", "UNAUTHORIZED", 401);
        }

        $this->RequestModel->Parameters = $route->Parameters;
        $this->RequestModel->DatasourceName = StringHelpers::SplitString($route->Action, ";", 1);
        $this->RequestModel->DatasourceType = StringHelpers::SplitString($route->Action, ";", 0);
        $this->RequestModel->RouteParameters = $route->RoutingParams;

        $this->Authenticate();
        $this->FillParametersBasedOnRequestData();
        $this->CheckParameterDataTypes();

        $this->HandleCurrentRequest();

        return "";
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
    }

    /**
     * This function adds the values of the parameters to the parameters array
     */
    private function FillParametersBasedOnRequestData(){
        $requestVariables = [];
        parse_str(file_get_contents("php://input"), $requestVariables);

        // Add the values of the parameters from the request
        foreach($requestVariables as $key => $value){
            $index = array_search($key, array_column($this->RequestModel->Parameters, 'name'));

            $this->RequestModel->Parameters[$index]["value"] = $value;
        }

        // Add the values of the parameters from the request uri
        foreach($this->RequestModel->RouteParameters as $key => $value){
            if(array_key_exists($key, $this->RequestModel->RouteParameters)){
                $value = (is_array($value)) ? $value[0]: $value;

                $index = array_search($key, array_column($this->RequestModel->Parameters, 'name'));
                $this->RequestModel->Parameters[$index]["value"] = $value;
            }
        }

        // Check the created parameters
        foreach($this->RequestModel->Parameters as $parameter){
            if(!array_key_exists("name", $parameter)){
                $this->RestServerReturnJson("Unnecessary parameter declaration", "UNNECESSARY_PARAMETER", 104);
            }

            if(StringHelpers::IsNullOrWhiteSpace($parameter["value"])){
                if($parameter["isRequired"]){
                    $this->RestServerReturnJson("Required parameter not set: ".$parameter["name"], "REQUIRED_PARAMETER", 103);
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
            switch($parameter["type"]){
                case "string":
                    if(!is_string($parameter["value"])){
                        $this->RestServerReturnJson("Parameter type mismatch for parameter: ".$parameter["name"], "TYPE MISMATCH", 101);
                    }
                    break;

                case "integer":
                    if(!is_numeric($parameter["value"])){
                        $this->RestServerReturnJson("Parameter type mismatch for parameter: ".$parameter["name"], "TYPE MISMATCH", 101);
                    }
                    break;

                case "date":
                    if(!StringHelpers::IsValidDate($parameter["value"])){
                        $this->RestServerReturnJson("Parameter type mismatch for parameter: ".$parameter["name"], "TYPE MISMATCH", 101);
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