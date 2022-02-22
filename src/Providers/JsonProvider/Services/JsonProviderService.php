<?php
namespace LightWine\Providers\JsonProvider\Services;

use LightWine\Core\Models\PageModel;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Modules\Templating\Services\TemplatingService;

class JsonProviderService {
    private MysqlConnectionService $databaseConnection;
    private TemplatesService $templateService;
    private TemplatingService $templatingService;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
        $this->templateService = new TemplatesService();
        $this->templatingService = new TemplatingService();
    }

    public function HandleJsonRequest(PageModel $page){
        if(HttpContextHelpers::RequestVariable("csrf_token") !== $_SESSION["CsrfToken"]){
            HttpContextHelpers::ShowError(403, "You do not have permission to access the requested content", "Access denied");
        }

        $template = $this->templateService->GetTemplateByName(HttpContextHelpers::RequestVariable("templatename"), "sql");

        $this->templatingService->AddTemplatingVariablesToStore();
        $queryTemplate = $this->templatingService->ReplaceVariablesFromStore($template->Content);

        $json = $this->databaseConnection->GetDatasetAsJson($queryTemplate);

        $jsonResponse = [
            "rowsAffected" =>  $this->databaseConnection->rowsAffected,
            "rowCount" => $this->databaseConnection->rowCount,
            "dataset" => $json
        ];

        $page->Headers["Content-Type"] = "application/json; charset=UTF-8";
        return json_encode($jsonResponse);
    }
}
?>