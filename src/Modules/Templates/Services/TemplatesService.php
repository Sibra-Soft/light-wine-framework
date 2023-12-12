<?php
namespace LightWine\Modules\Templates\Services;

use LightWine\Modules\Cache\Services\CacheService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Database\Enums\DataTypesEnum;
use LightWine\Modules\Templates\Models\TemplateModel;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Templates\Interfaces\ITemplatesService;

class TemplatesService implements ITemplatesService
{
    private MysqlConnectionService $databaseConnection;
    private ConfigurationManagerService $settings;
    public TemplateModel $returnModel;
    public CacheService $cacheService;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
        $this->settings = new ConfigurationManagerService();
        $this->cacheService = new CacheService();
        $this->returnModel = new TemplateModel;
    }

    /**
     * This function populates the returnmodel with various fields from the database, based on the specified templateId
     * @param MysqlConnectionService $dbConnection
     */
    private function PopulateModelFromDatabase(MysqlConnectionService $dbConnection){
        // Add the current environment as variable (options: `dev`, `test`, `live`)
        $currentEnvironment = $this->settings->GetAppSetting("Environment");

        $dbConnection->ClearParameters();
        $dbConnection->AddParameter("templateId", $this->returnModel->Id);
        $dbConnection->GetDataset("
            SELECT
	            template.*,
	            IFNULL(NULLIF(content_minified, ''), content) AS `content`,
                content AS `content_not_minified`,
                policies
            FROM `site_templates` AS `template`
            INNER JOIN site_template_versioning AS versions ON versions.template_id = template.id
	            AND versions.version = template.template_version_$currentEnvironment
            WHERE template.id = ?templateId
            LIMIT 1;
        ");

        // Check if a template is found
        if($dbConnection->rowCount > 0){
            $this->returnModel->DateCreated = $dbConnection->DatasetFirstRow("date_added", DataTypesEnum::DateTime);
            $this->returnModel->DateModified = $dbConnection->DatasetFirstRow("date_modified", DataTypesEnum::DateTime);
            $this->returnModel->Name = $dbConnection->DatasetFirstRow("name", DataTypesEnum::String);

            $this->returnModel->Content = $dbConnection->DatasetFirstRow("content", DataTypesEnum::String);
            $this->returnModel->ContentNotMinified = $dbConnection->DatasetFirstRow("content_not_minified", DataTypesEnum::String);

            $this->returnModel->Settings->CachingHours = $dbConnection->DatasetFirstRow("caching_hours", DataTypesEnum::Integer);
            $this->returnModel->Settings->StylingResources = $dbConnection->DatasetFirstRow("stylesheets", DataTypesEnum::String);
            $this->returnModel->Settings->ScriptResources = $dbConnection->DatasetFirstRow("scripts", DataTypesEnum::String);

            $policies = explode(",", $dbConnection->DatasetFirstRow("policies", DataTypesEnum::String));
            $this->GetTemplatePolicies($policies);
        }else{
            $this->returnModel->Found = false;
        }
    }

    /**
     * This function gets the active policies and sets them in the template return model
     * @param array $policyList The list of active policies
     */
    private function GetTemplatePolicies(array $policyList){
        foreach($policyList as $policy){
            switch($policy){
                case 1001: $this->returnModel->Policies->NO_MINIFICATION = true; break;
                case 1002: $this->returnModel->Policies->ALLOW_TO_RUN_THIS_MODULE_IN_BROWSER = true; break;
                case 1003: $this->returnModel->Policies->LOAD_TEMPLATE_ON_EVERY_PAGE = true; break;
                case 1004: $this->returnModel->Policies->HANDLE_SESSION_VARIABLES = true; break;
                case 1005: $this->returnModel->Policies->HANDLE_GET_VARIABLES = true; break;
                case 1006: $this->returnModel->Policies->HANDLE_POST_VARIABLES = true; break;
                case 1007: $this->returnModel->Policies->HANDLE_DATABASE_BINDINGS = true; break;
                case 1008: $this->returnModel->Policies->USERS_MUST_LOGIN = true; break;
                case 1009: $this->returnModel->Policies->ALLOW_EXPORT = true; break;
                case 1010: $this->returnModel->Policies->CAN_RUN_AS_SERVICE_WORKER = true; break;
                case 1011: $this->returnModel->Policies->BYPASS_MASTERPAGE = true; break;
                case 1012: $this->returnModel->Policies->ENABLE_BASIC_AUTHENTICATION = true; break;
                case 1013: $this->returnModel->Policies->NEVER_REMOVE_OLD_VERSIONS = true; break;
                case 1014: $this->returnModel->Policies->CACHE_TEMPLATE = true; break;
            }
        }
    }

    /**
     * Gets a template based on the specified name and type
     * @param string $templateName The name of the template you want to request
     * @param string $templateType The type of the template you want to request
     * @param string $folderName The foldername where the template is located
     * @return TemplateModel Model containing all the details of the requested template
     */
    public function GetTemplateByName(string $templateName, string $templateType = "html", string $folderName = "*"): TemplateModel {
        $this->returnModel = new TemplateModel();

        // First get the template id from the specified function parameters
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("templateName", $templateName);
        $this->databaseConnection->AddParameter("templateType", $templateType);
        $this->databaseConnection->AddParameter("folder", $folderName);
        $this->databaseConnection->GetDataset("
        SELECT
	        template.id,
	        LOWER(folder.`name`) AS folder
        FROM site_templates AS template
        LEFT JOIN site_templates AS folder ON folder.id = template.parent_id
        WHERE template.`name` = ?templateName
	        AND (template.type = ?templateType OR template.type = 'modal')
            AND IF(?folder = '*', 1=1, LOWER(folder.`name`) = ?folder)
        LIMIT 1;
        ");

        if($this->databaseConnection->rowCount > 0){
            $this->returnModel->Id = $this->databaseConnection->DatasetFirstRow("id");
            $this->returnModel->UnqiueId = sha1($this->returnModel->Id);

            $this->PopulateModelFromDatabase($this->databaseConnection);
        }else{
            $this->returnModel->Found = false;
        }
        return $this->returnModel;
    }

    /**
     * Gets a template based on a specified id
     * @param int $templateId The id of the template you want to request
     * @return TemplateModel Model containing all the details of the requested template
     */
    public function GetTemplateById(int $templateId): TemplateModel {
        $this->returnModel = new TemplateModel();
        $this->returnModel->UnqiueId = sha1($this->returnModel->Id);
        $this->returnModel->Id = $templateId;

        $this->PopulateModelFromDatabase($this->databaseConnection);

        return $this->returnModel;
    }
}