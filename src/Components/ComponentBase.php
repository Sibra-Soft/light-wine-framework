<?php
namespace LightWine\Components;

use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Interfaces\IComponentBase;

class ComponentBase implements IComponentBase
{
    private $componentModel = [];
    private MysqlConnectionService $databaseConnection;

    public function __construct(int $controlId){
        $this->databaseConnection = new MysqlConnectionService();

        $char = '"';
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("controlId", $controlId);
        $this->databaseConnection->GetDataset("SELECT CONCAT('{".$char."settings".$char.":', settings, ', ".$char."templates".$char.":', settings_templates, '}') AS component_model FROM `site_dynamic_content` WHERE `id` = ?controlId LIMIT 1;");

        if($this->databaseConnection->rowCount > 0){
            $this->componentModel = json_decode(Helpers::RepairJson($this->databaseConnection->DatasetFirstRow("component_model")), true);
        }
    }

    /**
     * Get a specific setting for this component
     * @param string $setting The name of the setting
     * @return string The value of the specified setting
     */
    public function GetSettings(string $setting): string {
        return $this->componentModel["settings"][$setting];
    }

    /**
     * Get a specified template of the current component
     * @param string $name The name of the template
     * @return string The content of the specified template
     */
    public function GetControlTemplate(string $name): string {
        return $this->componentModel["templates"][$name];
    }
}