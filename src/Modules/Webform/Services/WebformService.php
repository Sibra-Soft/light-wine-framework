<?php
namespace LightWine\Modules\Webform\Services;

use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Objects\Services\ObjectService;

class WebformService
{
    private MysqlConnectionService $databaseConnection;
    private ObjectService $objectService;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
        $this->objectService = new ObjectService();
    }

    /**
     * Gets a webform based on the specified id
     * @param int $formId The id of the webform to render
     */
    public function GetWebformById(int $formId): string {
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("formId", $formId);

        $dataset = $this->databaseConnection->GetDataset("
            SELECT
	            form_fields.`name`,
	            form_fields.`value`,
	            form_fields.field_type,
	            form_fields.placeholder,
	            form_fields.row_column,
	            form_fields.validation_type,
	            form_fields.label_caption
            FROM `site_forms` AS forms
            INNER JOIN site_form_fields AS form_fields ON form_fields.form_id = forms.id
            WHERE forms.id = ?formId
        ");

        foreach($dataset as $row){
            
        }
    }

    public function GetWebformByName(string $formName): string {
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("formName", $formName);

    }
}