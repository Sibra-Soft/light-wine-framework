<?php
namespace LightWine\Components\Dataview;

use LightWine\Components\ComponentBase;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Components\Dataview\Models\DataviewComponentModel;
use LightWine\Core\Helpers\RequestVariables;

class Dataview {
    private int $PageCount = 1;

    private ComponentBase $control;
    private TemplatingService $templatingService;
    private TemplatesService $templatesService;
    private MysqlConnectionService $databaseConnection;
    private DataviewComponentModel $component;

    public function __construct($id){
        $this->control = new ComponentBase();
        $this->templatingService = new TemplatingService();
        $this->templatesService = new TemplatesService();
        $this->databaseConnection = new MysqlConnectionService();
        $this->component = $this->control->GetSettings(new DataviewComponentModel, $id);
    }

    private function RenderFiltersInQuery(TemplatingService $templating){
        $filters = json_decode(stripslashes(RequestVariables::Get("filters")), true);

        foreach($filters as $filter){
            $fieldName = $filter["field"];

            $templating->AddReplaceVariable("filter_".$fieldName, true);

            switch($filter["function"]){
                case "equal": $filterPart = "= '".$filter["value"]."'"; break;
                case "contains": $filterPart = "LIKE '%".$filter["value"]."%'"; break;
            }

            $templating->AddReplaceVariable("filter_".$fieldName."_value", $filterPart);
        }
    }

    /**
     * Add limit to the current component query
     * @param string $query The query to modify
     * @param int $page The current pagenumber
     * @return string The new query with the added limit
     */
    private function GenerateQueryLimit(string $query, int $page = 1): string {
        $limit = $this->component->MaxItemsVisible;

        $offset = ($page-1) * $limit;
        $limitPart = "LIMIT $offset,$limit;";

        if(StringHelpers::Contains($query, "LIMIT")){
            $limitPos = strpos($query, "LIMIT");
            return str_replace(substr($query, $limitPos), $limitPart, $query);
        }else{
            return str_replace('@pagination', $limitPart, $query);
        }
    }

    /**
     * This function generates the pagination query etc.
     * @param MysqlConnectionService $dbConnection A open database connection
     * @param string $query The current control query
     */
    private function GetPageCountFromQuery(MysqlConnectionService $dbConnection) {
        $limit = $this->component->MaxItemsVisible;
        $this->templatingService->AddTemplatingVariablesToStore();

        $queryTemplate = $this->templatesService->GetTemplateByName($this->component->CountQueryTemplate, "sql");
        $queryTemplate = $this->templatingService->ReplaceVariablesFromStore($queryTemplate->Content);
        $queryTemplate = $this->templatingService->RunCompilers($queryTemplate);

        // Execute the count query
        $dbConnection->GetDataset($queryTemplate);
        $numberOfRecords = $dbConnection->DatasetFirstRow("count", "integer");

        $this->PageCount = ceil($numberOfRecords / $limit);
    }

    /**
     * This function renders the control into plain HTML code
     * @return string
     */
    private function RenderControl(): string {
        $this->templatingService->AddTemplatingVariablesToStore();

        $queryTemplate = $this->templatesService->GetTemplateById($this->component->MainQueryTemplate)->Content;

        $this->RenderFiltersInQuery($this->templatingService);

        $queryTemplate = $this->templatingService->ReplaceVariablesFromStore($queryTemplate);
        $queryTemplate = $this->templatingService->RunCompilers($queryTemplate);

        // This gets the current page count
        $queryTemplate = $this->GenerateQueryLimit($queryTemplate, 1);
        if($this->component->EnablePagination) $this->GetPageCountFromQuery($this->databaseConnection);

        // Render the query if a page number is specified
        $pageNr = (int)RequestVariables::Get("page", 1);
        if(RequestVariables::Get("page")){
            $queryTemplate = $this->GenerateQueryLimit($queryTemplate, $pageNr);
        }

        $dataset = $this->databaseConnection->GetDataset($queryTemplate);

        // Build the output
        $template = "";

        if($this->databaseConnection->rowCount > 0){
            $template .= str_replace("{{row_count}}", $this->databaseConnection->rowCount, $this->component->HeaderTemplate);

            $index = 0;
            foreach($dataset as $row){
                $controlTemplate = $this->component->RepeatTemplate;
                foreach($row as $key => $value){ $this->templatingService->AddReplaceVariable($key, $value); }
                $controlTemplate = $this->templatingService->RunCompilers($controlTemplate);

                $template .= $this->templatingService->ReplaceVariablesFromStore($controlTemplate);

                if($this->component->DetermineOddEvenRows){
                    $index++;

                    if($index % 2 == 0){
                        $template = str_replace("{{odd_or_even}}", "even", $template);
                    }else{
                        $template = str_replace("{{odd_or_even}}", "odd", $template);
                    }
                }
            }
            $template .= $this->component->FooterTemplate;

            // Generate the pagination part of the template
            $template .= $this->component->PaginationTemplate;
            $template = str_replace("{{page_count}}", $this->PageCount, $template);
        }else{
            $template .= $this->component->NodataTemplate;
        }

        return $template;
    }

    /**
     * Component init function
     * @return string The content of the component
     */
    public function Init(){
        return $this->RenderControl();
    }
}