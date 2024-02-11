<?php
namespace LightWine\Modules\Database\Services;

class SqlLiteConnectionService {
    protected $dbConnection;
    protected $datasetFirstRow = [];

    public $queryParameters = [];
    public DatabaseHelperService $helpers;

    public function Open(string $file){

    }

    public function ExecuteQuery(string $query){

    }

    public function GetDataset(){
        
    }

    public function ClearParameters(){
        $this->queryParameters = array();
    }

    public function AddParameter(string $name, $value, $default = null){
        if($value == ""){
            $this->queryParameters["?$name"] = $default;
        }else{
            $this->queryParameters["?$name"] = $value;
        }
    }
}
?>