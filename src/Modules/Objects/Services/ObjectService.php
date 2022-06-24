<?php
namespace LightWine\Modules\Objects\Services;

use LightWine\Modules\Objects\Interfaces\IObjectService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\HttpRequest;
use LightWine\Modules\Cache\Services\CacheService;

class ObjectService implements IObjectService
{
    private MysqlConnectionService $databaseConnection;
    private CacheService $cacheService;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
        $this->cacheService = new CacheService();
    }

    /**
     * Gets a object based on the specified key and domain
     * @param string $key The name of the object
     * @param string $default The default value of the object
     * @return string The value of the object
     */
    public function FindObjectBasedOnDomainName(string $key, string $default = ""): string {
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("domain", HttpRequest::Domain());
        $this->databaseConnection->AddParameter("key", $key);
        $this->databaseConnection->GetDataset("
            SELECT
                id,
                object_key,
                object_value
            FROM site_objects
            WHERE type = 'item'
                AND object_key = ?key
                AND domain IS NULL OR domain = ?domain
            LIMIT 1;
        ");

        if($this->databaseConnection->rowCount > 0){
            return $this->databaseConnection->DatasetFirstRow("object_value");
        }else{
            return $default;
        }
    }
}