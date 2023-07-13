<?php
namespace LightWine\Modules\Webform\Services;

use LightWine\Modules\Database\Services\MysqlConnectionService;

class WebformService
{
    private MysqlConnectionService $databaseConnection;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
    }

    public function GetWebformById(int $id): string {
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("id", $id);
        $this->databaseConnection->GetDataset("SELECT * FROM `site_forms` WHERE id = ?id LIMIT 1;");

        return $this->databaseConnection->DatasetFirstRow("html");
    }

    public function GetWebformByName(string $name): string {

    }
}