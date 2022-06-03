<?php
namespace LightWine\Modules\Database\Services;

use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Database\Interfaces\IMysqlConnectionService;
use LightWine\Core\Helpers\TraceHelpers;

use \PDO;
use \PDOException;
use \DateTime;
use \PDOStatement;
use \Exception;

class MysqlConnectionService implements IMysqlConnectionService {
    protected $dbConnection;
    protected $datasetFirstRow = [];

    public static $DatabaseConnection;

    public $rowCount = 0;
    public $rowsAffected = 0;
    public $rowInsertId = 0;

    private $query = "";
    private $mysqlQueryParameters = [];
    private $lastErrorCode = null;

    public DatabaseHelperService $helpers;
    public ConfigurationManagerService $config;

    public function __construct(){
        $this->helpers = new DatabaseHelperService($this);
        $this->config = new ConfigurationManagerService();

        // Get the settings for the connectionstring
        $server = $this->config->ConnectionStrings("DefaultConnectionString", "server");
        $database = $this->config->ConnectionStrings("DefaultConnectionString", "database");
        $username = $this->config->ConnectionStrings("DefaultConnectionString", "user");
        $password = $this->config->ConnectionStrings("DefaultConnectionString", "password");
        $conString = "mysql:host=".$server.";dbname=".$database;

        try {
            // Check if the database connection is already declared
            if(isset(self::$DatabaseConnection)){
                $this->dbConnection = self::$DatabaseConnection;
            }else{
                $this->dbConnection = new PDO($conString, $username, $password);
                $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                self::$DatabaseConnection = $this->dbConnection;
            }
        }
        catch(PDOException $e)
        {
            throw new Exception("Mysql connection error: ".$e->getMessage());
        }
    }

    /** {@inheritdoc} */
    public function DatasetFirstRow(string $column, string $typeOf = "string"){
        switch($typeOf){
            case "string": $return = (string)$this->datasetFirstRow[$column]; break;
            case "integer": $return = (integer)$this->datasetFirstRow[$column]; break;
            case "boolean": $return = (boolean)$this->datasetFirstRow[$column]; break;
            case "datetime": $return = new DateTime($this->datasetFirstRow[$column]); break;
        }

        return $return;
    }

    /** {@inheritdoc} */
    public function ExecuteQuery(string $query): PDOStatement {
        $queryToExecute = $this->doParameterReplacements($query);
        $this->query = $queryToExecute;

        try {
            $statement = $this->dbConnection->query($queryToExecute);

            $this->rowsAffected = $statement->rowCount();
            $this->rowInsertId = $this->dbConnection->lastInsertId();

            return $statement;
        }
        catch(PDOException $e) {
            throw new Exception('Error during Mysql query execution, error: '.print_r($e->errorInfo).' current query: '.$this->query);
        }
    }

    /** {@inheritdoc} */
    public function GetFieldset(string $tableOrQuery, string $type = "table"){
        $fieldset = array();
        $index = 0;

        if($type == "table"){
            $dataset = $this->getDataset("DESCRIBE ".$tableOrQuery);

            foreach ($dataset as $row) {
                list($type, $length) = explode("(", $row["Type"]."(");

                $fieldset[$row["Field"]] = array(
                    "name" => $row["Field"],
                    "type" => $type,
                    "length" => str_replace(")", "", $length)
                );

                $index++;
            }
        }else{
            $query = $this->DoParameterReplacements($tableOrQuery);
            $queryResult = $this->dbConnection->query($query);

            for ($i = 0; $i < $queryResult->columnCount(); $i++) {
                $col = $queryResult->getColumnMeta($i);
                $columns[] = $col['name'];
            }

            $fieldset = $columns;
        }

        return $fieldset;
    }

    /** {@inheritdoc} */
    public function ClearParameters(){
        $this->mysqlQueryParameters = array();
    }

    /** {@inheritdoc} */
    public function AddParameter(string $name, $value, $default = null){
        if($value == ""){
            $this->mysqlQueryParameters["?$name"] = $default;
        }else{
            $this->mysqlQueryParameters["?$name"] = $value;
        }
    }

    /**
     * Replace all query parameters
     * @param string $query
     * @return string
     */
    private function DoParameterReplacements(string $query){
        $tempQuery =  $query;

        foreach($this->mysqlQueryParameters as $name => $value){
            if(gettype($value) == "string"){
                $tempQuery = str_replace($name, "'".addslashes($value)."'", $tempQuery);
            }else{
                $tempQuery = str_replace($name, $value, $tempQuery);
            }
        }

        return $tempQuery;
    }

    /** {@inheritdoc} */
    public function GetDatasetAsJson(string $query): array {
        return $this->GetDataset($query);
    }

    /** {@inheritdoc} */
    public function GetDataset(string $query = null){
        $dataset = array();

        if($query == null){
            $query = $this->query;
        }

        $queryToExecute = $this->doParameterReplacements($query);

        try {
            $statement = $this->dbConnection->prepare($queryToExecute);

            if($statement->execute()){
                array_push($dataset, ["result" => true]);
            }else{
                array_push($dataset, ["result" => false]);
            }

            $timeStart = microtime(true);

            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $dataset = $statement->fetchAll();

            $timeEnd = microtime(true);
            $executionTime = ($timeEnd - $timeStart) * 1000;

            TraceHelpers::Write("Query executed in: ".$executionTime." seconds");
            TraceHelpers::Write("Query executed: <pre>".$queryToExecute."</pre>");
        }
        catch(PDOException $e)
        {
            if($e->getMessage() !== "SQLSTATE[HY000]: General error") throw new Exception($e->errorInfo[2]."#".$queryToExecute);
        }

        // Get the rowcount
        $this->rowCount = $statement->rowCount();

        // Only set the datasetfirstrow property if a row is returend
        if($this->rowCount > 0){
            $this->datasetFirstRow = $dataset[0];
        }

        return $dataset;
    }
}
?>