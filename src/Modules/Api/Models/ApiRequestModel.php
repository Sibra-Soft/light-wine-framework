<?php
namespace LightWine\Modules\Api\Models;

use LightWine\Core\Helpers\StringHelpers;

class ApiRequestModel {
    public function __construct(){
        if(isset($_SESSION["UserId"])){
            $this->UserId = $_SESSION["UserId"];
        }
               
        if(!StringHelpers::IsNullOrWhiteSpace(json_decode(file_get_contents('php://input')))){
            $this->RequestBody = json_decode(file_get_contents('php://input'));
        }

        $this->RequestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        $this->ApiFullRequestPath = $_SERVER["REQUEST_URI"];
        $this->ApiPathSegments = explode('/', $_SERVER["REQUEST_URI"]);
        $this->ApiRequestPath = $this->ApiFullRequestPath;
        $this->Headers = getallheaders();
    }
    public array $ApiPathSegments = [];
    public array $Headers = [];
    public array $Parameters = [];
    public array $AllowedMethodes = [];
    public array $RouteParameters = [];

    public string $RequestMethod = "";
    public string $RequestBody = "";
    public string $ApiFullRequestPath = "";
    public string $ApiRequestPath = "";
    public string $DatasourceType = "";
    public string $DatasourceName = "";

    public int $UserId = 0;
    public int $PathIndex = 0;
}