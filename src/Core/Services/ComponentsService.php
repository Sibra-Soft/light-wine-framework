<?php
namespace LightWine\Core\Services;

use LightWine\Components\Account\Account;
use LightWine\Components\DeviceVerification\DeviceVerification;
use LightWine\Components\Dataview\Dataview;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Core\Interfaces\IComponentService;

class ComponentsService implements IComponentService
{
    private MysqlConnectionService $databaseConnection;
    private TemplatingService $templatingService;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
        $this->templatingService = new TemplatingService();
    }

    /**
     * Handels the rendering of a specified component
     * @param string $name The name of the component
     * @return string The content of the rendered component, based on the settings and templates
     */
    public function HandleRenderComponent(string $name): string {
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("controlName", $name);
        $this->databaseConnection->GetDataset("SELECT * FROM `site_dynamic_content` WHERE `name` = ?controlName LIMIT 1;");

        if($this->databaseConnection->rowCount > 0){
            $controlId = $this->databaseConnection->DatasetFirstRow("id");

            // Run the control code
            switch($this->databaseConnection->DatasetFirstRow("type")){
                case "account": $myControl = new Account($controlId); $controlContent = $myControl->Init(); break;
                case "device-verification": $myControl = new DeviceVerification(); $controlContent = $myControl->Init($controlId); break;
                case "dataview": $myControl = new Dataview($controlId); $controlContent = $myControl->Init(); break;
            }

            $template = $controlContent;
            $template = $this->templatingService->ReplaceExtensions($template);
            $template = $this->templatingService->RunCompilers($template);

            return $template;
        }else{
            HttpContextHelpers::ShowError(404, "Not found", "Component with name ". $name." could not be found");
            return "";
        }
    }
}