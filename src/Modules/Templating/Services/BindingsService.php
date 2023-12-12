<?php
namespace LightWine\Modules\Templating\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Modules\Templating\Models\BindingReturnModel;
use LightWine\Modules\Templating\Interfaces\IBindingService;

class BindingsService implements IBindingService
{
    private MysqlConnectionService $databaseConnection;
    private TemplatingService $templatingService;
    private ConfigurationManagerService $settings;

    public function __construct(TemplatingService $templatingService){
        $this->databaseConnection = new MysqlConnectionService();
        $this->templatingService = $templatingService;
        $this->settings = new ConfigurationManagerService();
    }

    public function GetBindingBasedOnTemplateId(int $templateId): BindingReturnModel {
        $returnModel = new BindingReturnModel;

        // Add the current environment as variable (options: `dev`, `test`, `live`)
        $currentEnvironment = $this->settings->GetAppSetting("Environment");

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("templateId", $templateId);
        $dataset = $this->databaseConnection->GetDataset("
            SELECT
	            bindings.`name`,
	            JSON_EXTRACT(version.content, '$.template') AS binding_template_id,
	            JSON_UNQUOTE(JSON_EXTRACT(version.content, '$.type')) AS type,
	            JSON_EXTRACT(version.content, '$.result_can_be_empty') AS result_can_be_empty,
	            JSON_EXTRACT(version.content, '$.result_as_json') AS result_as_json
            FROM site_templates AS bindings
            INNER JOIN site_template_versioning AS version ON version.template_id = bindings.id AND version.version = bindings.template_version_$currentEnvironment
            WHERE bindings.parent_id = ?templateId
	            AND bindings.type = 'binding'
	    ");

        $returnModel->BindingTemplateId = $templateId;
        $returnModel->BindingCount = $this->databaseConnection->rowCount;

        // Only add databinding data if bindings are found
        if($this->databaseConnection->rowCount > 0){
            foreach($dataset as $row){
                $bindingName = $row["name"];
                $bindingQuery = $row["binding_template_id"];

                $queryTemplate = $this->templatingService->RenderTemplateAndDoAllReplacements($bindingQuery)->Content;

                if(StringHelpers::IsNullOrWhiteSpace($queryTemplate) && $row["result_can_be_empty"] == 1){
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