<?php
namespace LightWine\Modules\Api\Services;

use LightWine\Core\HttpResponse;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\QueryBuilder\Services\QueryBuilderService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Modules\QueryBuilder\Enums\QueryExtenderEnum;
use LightWine\Modules\QueryBuilder\Enums\QueryOperatorsEnum;
use LightWine\Modules\Api\Models\ApiRequestModel;

class ApiQueryService {
    private MysqlConnectionService $databaseConnection;
    private QueryBuilderService $queryBuilderService;
    private TemplatesService $templatesService;
    private TemplatingService $templatingService;
    private ApiRequestModel $RequestModel;

    public function __construct(ApiRequestModel $requestModel){
        $this->databaseConnection = new MysqlConnectionService();
        $this->queryBuilderService = new QueryBuilderService();
        $this->templatesService = new TemplatesService();
        $this->templatingService = new TemplatingService();

        $this->RequestModel = $requestModel;
    }

    /**
     * This function handels api requests that are linked to a query template
     */
    public function HandleQueryRequest(){
        $template = $this->templatesService->GetTemplateByName($this->RequestModel->DatasourceName, "sql")->Content;

        // Add the request parameters to the query replacement store
        foreach($this->RequestModel->Parameters as $parameter){
            $this->templatingService->AddReplaceVariable($parameter["name"], $parameter["value"]);
        }
        $this->templatingService->AddReplaceVariable("user_id", $this->RequestModel->UserId);

        $query = $this->templatingService->ReplaceVariablesFromStore($template);
        $this->databaseConnection->executeQuery($query);

        HttpResponse::SetReturnJson([
            "message" => [
                "affectedRows" => $this->databaseConnection->rowsAffected,
                "parameters" => $this->RequestModel->Parameters
            ],
            "categorie" => "UPDATE",
            "code" => 200
        ]);
    }

    /**
     * This function selects data from a specified table based on the API request
     */
    public function HandleSelectRequest(){
        $dataset = array();

        $keySelector = explode(":", $this->RequestModel->KeyColumn);
        $keyValue = $keySelector[1];
        $keyName = $keySelector[0];

        $columns = (empty($this->requestModel->Options->IncludeColumns)) ? "*" : $this->requestModel->Options->IncludeColumns;

        $this->queryBuilderService->Select($this->RequestModel->DatabaseObject, $columns);
        (empty($this->RequestModel->KeyColumn)) ? "" : $this->queryBuilderService->where(QueryExtenderEnum::AndExtender, $keyName, QueryOperatorsEnum::EqualTo, $keyValue);
        $this->queryBuilderService->where(QueryExtenderEnum::AndExtender, "user_id", QueryOperatorsEnum::EqualTo, $this->requestModel->UserId);

        $dataset = $this->databaseConnection->getDataset($this->queryBuilderService->render());

        HttpResponse::SetReturnJson([
            "message" => [
                "count" => $this->databaseConnection->rowCount,
                "data" => $dataset
            ],
            "categorie" => "SELECT",
            "code" => 200
        ]);
    }

    /**
     * This function updates data in a table based on the API request
     */
    public function HandleUpdateRequest(){
        foreach($this->RequestModel->Parameters as $parameter){
            $this->queryBuilderService->Update($this->RequestModel->DatasourceName, $parameter["name"], $parameter["value"]);
        }

        $this->queryBuilderService->Where(QueryExtenderEnum::Nothing, "user_id", QueryOperatorsEnum::EqualTo, $this->RequestModel->UserId);
        foreach($this->RequestModel->Parameters as $parameter){
            if($parameter["isKey"]){
                $this->queryBuilderService->Where(QueryExtenderEnum::AndExtender, $parameter["name"], QueryOperatorsEnum::EqualTo, $parameter["value"]);
            }
        }
        
        $this->databaseConnection->executeQuery($this->queryBuilderService->Render());

        HttpResponse::SetReturnJson([
            "message" => [
                "affectedRows" => $this->databaseConnection->rowsAffected,
                "parameters" => $this->RequestModel->Parameters
            ],
            "categorie" => "UPDATE",
            "code" => 200
        ]);
    }

    /**
     * This function inserts data into a specified table based on the API request
     */
    public function HandleInsertRequest(){
        $this->databaseConnection->ClearParameters();

        $this->queryBuilderService->Insert($this->RequestModel->DatasourceName, "user_id", $this->RequestModel->UserId);
        foreach($this->RequestModel->Parameters as $parameter){
            $this->queryBuilderService->Insert($this->RequestModel->DatasourceName, $parameter["name"], $parameter["value"]);
        }

        $this->databaseConnection->ExecuteQuery($this->queryBuilderService->Render());
        $lastInsertId = $this->databaseConnection->rowInsertId;

        HttpResponse::SetReturnJson([
            "message" => array(
                "insertKey" => $lastInsertId,
                "parameters" => $this->RequestModel->Parameters,
            ),
            "categorie" => "INSERT",
            "code" => 200
        ]);
    }

    /**
     * This function deletes data from a specified table based on the API request
     */
    public function HandleDeleteRequest(){
        $this->queryBuilderService->Delete($this->RequestModel->DatasourceName);
        $this->queryBuilderService->Where(QueryExtenderEnum::Nothing, "user_id", QueryOperatorsEnum::EqualTo, $this->RequestModel->UserId);

        foreach($this->RequestModel->Parameters as $parameter){
            if($parameter["isKey"]){
                $this->queryBuilderService->Where(QueryExtenderEnum::AndExtender, $parameter["name"], QueryOperatorsEnum::EqualTo, $parameter["value"]);
            }
        }

        $this->databaseConnection->executeQuery($this->queryBuilderService->Render());

        HttpResponse::SetReturnJson([
            "message" => array(
                "affectedRows" => $this->databaseConnection->rowsAffected,
                "parameters" => $this->RequestModel->Parameters,
            ),
            "categorie" => "DELETE",
            "code" => 200
        ]);
    }
}
?>