<?php
namespace LightWine\Modules\Database\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Files\Services\UploadService;
use LightWine\Modules\QueryBuilder\Services\QueryBuilderService;
use LightWine\Modules\QueryBuilder\Enums\QueryExtenderEnum;
use LightWine\Modules\QueryBuilder\Enums\QueryOperatorsEnum;
use LightWine\Modules\Database\Interfaces\IDatabaseHelperService;

class DatabaseHelperService implements IDatabaseHelperService {
    private MysqlConnectionService $databaseConnection;
    private QueryBuilderService $queryBuilder;

    public function __construct(MysqlConnectionService $connection){
        $this->databaseConnection = $connection;
        $this->queryBuilder = new QueryBuilderService();
    }

    /** {@inheritdoc} */
    public function SyncSet(array $mutationArray, string $targetTable, array $options){
        $this->queryBuilder->Clear();

        $queries = "";

        foreach ($mutationArray as $key) {
            if(StringHelpers::IsNullOrWhiteSpace($key["id"])){
                // Must be added
                unset($key["id"]);

                $key += $options;
                $key += ["user_id" => $_SESSION["UserId"]];

                foreach($key as $column => $value){
                    $this->queryBuilder->Insert($targetTable, $column, $value);
                }

                $queries .= $this->queryBuilder->Render().";";
            }else{
                // Must be added
                $id = $key["id"];
                unset($key["id"]);

                $key += $options;
                $key += ["user_id" => $_SESSION["UserId"]];

                foreach($key as $column => $value){
                    $this->queryBuilder->Update($targetTable, $column, $value);
                    $this->queryBuilder->Where(QueryExtenderEnum::Nothing, "id", QueryOperatorsEnum::EqualTo, $id);
                }

                $queries .= $this->queryBuilder->Render().";";
            }
        }

        if(count($mutationArray) > 0){
            $this->databaseConnection->ExecuteQuery($queries);
        }
    }

    /** {@inheritdoc} */
    public function Lookup(string $expr, string $table, string $criteria){
        // Build the criteria
        $criteria = (StringHelpers::IsNullOrWhiteSpace($criteria) ? "1=1": $criteria);

        // Build the lookup query
        $this->databaseConnection->GetDataset("SELECT $expr FROM $table WHERE $criteria LIMIT 1;");

        // Return the selected value
        return $this->databaseConnection->DatasetFirstRow($expr);
    }

    /** {@inheritdoc} */
    public function DeleteMultipleRecords(string $table, string $ids){
        $query = "DELETE FROM `$table` WHERE id IN ($ids)";

        $this->databaseConnection->ExecuteQuery($query);
    }

    /** {@inheritdoc} */
    public function DeleteRecord(string $table, int $id){
        $this->queryBuilder->Clear();
        $this->queryBuilder->Delete($table);
        $this->queryBuilder->Where(QueryExtenderEnum::Nothing, "id", QueryOperatorsEnum::EqualTo, $id);

        $this->databaseConnection->ExecuteQuery($this->queryBuilder->Render());
    }

    /** {@inheritdoc} */
    public function UpdateOrInsertRecordBasedOnParameters(string $table, int $id = null, bool $ignoreDuplicates = false):int {
        $this->queryBuilder->ignoreDuplicatesOnInsert = $ignoreDuplicates;
        $this->queryBuilder->Clear();

        // Generate the query based on the specified parameters
        $parameters = $this->databaseConnection->mysqlQueryParameters;

        unset($parameters["?currentDatabase"]); // Remove unnecessary parameters

        // Check if it's a add or a update query
        if(StringHelpers::IsNullOrWhiteSpace($id)){
            foreach($parameters as $key => $value){
                $this->queryBuilder->Insert($table, str_replace("?", "", $key), $value);
            }
        }else{
            foreach($parameters as $key => $value){
                $this->queryBuilder->Update($table, str_replace("?", "", $key), $value);
            }
            $this->queryBuilder->Where(QueryExtenderEnum::Nothing, "id", QueryOperatorsEnum::EqualTo, $id);

        }

        // Render and execute the query
        $query = $this->queryBuilder->Render();
        $this->databaseConnection->ExecuteQuery($query);

        return $this->databaseConnection->rowInsertId;
    }
}
?>