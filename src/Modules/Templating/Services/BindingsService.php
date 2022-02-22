<?php
namespace LightWine\Modules\Templating\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Modules\Templating\Models\BindingReturnModel;
use LightWine\Modules\Templating\Interfaces\IBindingService;

class BindingsService implements IBindingService
{
    private MysqlConnectionService $databaseConnection;
    private TemplatingService $templatingService;

    public function __construct(TemplatingService $templatingService){
        $this->databaseConnection = new MysqlConnectionService();
        $this->templatingService = $templatingService;
    }

    public function GetBindingBasedOnTemplateId(int $templateId): BindingReturnModel {
        $returnModel = new BindingReturnModel;

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("templateId", $templateId);
        $dataset = $this->databaseConnection->GetDataset("
            SELECT
                binding.result_can_be_empty AS `can_be_empty`,
	            binding.`name` AS `binding_name`,
	            binding.source_template_id AS `binding_template_id`,
                binding.result_as_json AS `result_as_json`
            FROM `site_bindings` AS binding
            WHERE binding.destination_template_id = ?templateId
	    ");

        $returnModel->BindingTemplateId = $templateId;
        $returnModel->BindingCount = $this->databaseConnection->rowCount;

        // Only add databinding data if bindings are found
        if($this->databaseConnection->rowCount > 0){
            foreach($dataset as $row){
                $bindingName = $row["binding_name"];
                $bindingQuery = $row["binding_template_id"];

                $queryTemplate = $this->templatingService->RenderTemplateAndDoAllReplacements($bindingQuery)->Content;

                if(StringHelpers::IsNullOrWhiteSpace($queryTemplate) && $row["can_be_empty"] == 1){
                    $returnModel->BindingResult[$bindingName."_rowcount"] = 0;
                    $returnModel->BindingResult[$bindingName] = json_decode(json_encode([[]]));
                }else{
                    $bindingDataset = mb_convert_encoding($this->databaseConnection->GetDataset($queryTemplate), 'UTF-8', 'UTF-8');

                    // Add the result and rowcount
                    if($row["result_as_json"] == 1){
                        $returnModel->BindingResult[$bindingName."_json"] = json_encode($bindingDataset);
                    }else{
                        $returnModel->BindingResult[$bindingName."_rowcount"] = $this->databaseConnection->rowCount;
                        $returnModel->BindingResult[$bindingName] = json_decode(json_encode($bindingDataset));
                    }
                }
            }

            $returnModel->BindingName = $bindingName;
        }

        return $returnModel;
    }
}