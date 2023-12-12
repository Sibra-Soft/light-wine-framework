<?php
namespace LightWine\Core\Services;

use LightWine\Components\Account\Account;
use LightWine\Components\DeviceVerification\DeviceVerification;
use LightWine\Components\Dataview\Dataview;
use LightWine\Components\Webform\Webform;

use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Templating\Services\TemplatingService;

use LightWine\Core\Interfaces\IComponentService;
use LightWine\Core\HttpResponse;


class ComponentsService implements IComponentService
{
    private MysqlConnectionService $databaseConnection;
    private TemplatingService $templatingService;
    private ConfigurationManagerService $settings;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
        $this->templatingService = new TemplatingService();
        $this->settings = new ConfigurationManagerService();
    }

    /**
     * Handels the rendering of a specified component
     * @param string $name The name of the component
     * @return string The content of the rendered component, based on the settings and templates
     */
    public function HandleRenderComponent(string $name): string {
        // Add the current environment as variable (options: `dev`, `test`, `live`)
        $currentEnvironment = $this->settings->GetAppSetting("Environment");

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("controlName", $name);
        $this->databaseConnection->GetDataset("
            SELECT
	            JSON_UNQUOTE(JSON_EXTRACT(version.content, '$.Type')) AS type,
	            component.id
            FROM `site_templates` AS component
            INNER JOIN site_template_versioning AS version ON version.version = component.template_version_$currentEnvironment AND version.template_id = component.id
            WHERE component.`name` = ?controlName
	            AND component.type = 'component'
            LIMIT 1;
        ");

        if($this->databaseConnection->rowCount > 0){
            $controlId = $this->databaseConnection->DatasetFirstRow("id");

            // Run the control code
            switch($this->databaseConnection->DatasetFirstRow("type")){
                case "account": $myControl = new Account($controlId); $controlContent = $myControl->Init(); break;
                case "device-verification": $myControl = new DeviceVerification($controlId); $controlContent = $myControl->Init(); break;
                case "dataview": $myControl = new Dataview($controlId); $controlContent = $myControl->Init(); break;
                case "webform": $myControl = new Webform($controlId); $controlContent = $myControl->Init(); break;
            }

            $template = $controlContent;
            $template = $this->templatingService->ReplaceExtensions($template);
            $template = $this->templatingService->RunCompilers($template);

            return $template;
        }else{
            Throw new \Exception("Component with name ". $name." could not be found");
        }
    }
}