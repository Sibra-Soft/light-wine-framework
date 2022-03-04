<?php
namespace LightWine\Modules\QueryBuilder\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\QueryBuilder\Enums\QueryOperatorsEnum;

class QueryBuilderService
{
    protected $queryConstructor = [];

    public bool $ignoreDuplicatesOnInsert = false;

    /**
     * Clears the constructor
     */
    public function Clear(){
        $this->queryConstructor = [
            "0_STATEMENT" => "SELECT",
            "1_DATA" => ["SELECT" => "*"],
            "2_FROM" => "",
            "3_WHERE" => [],
            "4_GROUP_BY" => "",
            "5_ORDER_BY" => "",
            "6_LIMIT" => 0
        ];
    }
       
    /**
     * This function will insert a new record in the specified table
     * @param string $table The target table
     * @param string $column The column you want to fill with the specified value
     * @param string $value The value you want to insert
     */
    public function Insert(string $table, string $column, $value){
        // Reset the data constructor if the last statement was not a select
        if($this->queryConstructor["0_STATEMENT"] !== "INSERT"){$this->queryConstructor["1_DATA"] = [];}

        $this->queryConstructor["0_STATEMENT"] = "INSERT";
        $this->queryConstructor["1_DATA"][$column] = $value;
        $this->queryConstructor["2_FROM"] = $table;
    }

    /**
     * This function can be used to delete a record from a specified table
     * @param string $table
     */
    public function Delete(string $table){
        // Reset the data constructor if the last statement was not a select
        if($this->queryConstructor["0_STATEMENT"] !== "DELETE"){$this->queryConstructor["1_DATA"] = [];}

        $this->queryConstructor["0_STATEMENT"] = "DELETE";
        $this->queryConstructor["2_FROM"] = $table;
    }

    /**
     * This function updates specified columns and values in a table
     * @param string $table The table you want to update
     * @param string $column The column of the table you want to update
     * @param string $value The value you want to update in the specified table and column
     */
    public function Update(string $table, string $column, $value){
        // Reset the data constructor if the last statement was not a select
        if($this->queryConstructor["0_STATEMENT"] !== "UPDATE"){$this->queryConstructor["1_DATA"] = [];}

        $this->queryConstructor["0_STATEMENT"] = "UPDATE";
        $this->queryConstructor["1_DATA"][$column] = $value;
        $this->queryConstructor["2_FROM"] = $table;
    }

    /**
     * This function selects data from specified columns in a specified table
     * @param string $table The table you want to select
     * @param string $columns The columns you want to return
     */
    public function Select(string $table, string $columns = "*"){
        $this->queryConstructor["0_STATEMENT"] = "SELECT";
        $this->queryConstructor["1_DATA"]["SELECT"] = $columns;
        $this->queryConstructor["2_FROM"] = $table;
    }

    /**
     * This function adds a where statement to the query
     * @param integer $extender
     * @param string $column
     * @param string $operator
     * @param string $value
     */
    public function Where(int $extender, string $column, string $operator, string $value){
        $extendWith = "";

        if($extender === 0){
            $extendWith = " AND";
        }elseif($extender === 1){
            $extendWith = " OR";
        }elseif($extender === 2){
            $extendWith = "";
        }

        $whereStatementCount = count((array)$this->queryConstructor["3_WHERE"]);
        $this->queryConstructor["3_WHERE"][$whereStatementCount.";".$extendWith] = [$column.";".$operator => $value];
    }

    /**
     * This function adds a order statement to the query
     * @param string $column
     * @param string $direction
     */
    public function Order(string $column, string $direction){
        $this->queryConstructor["5_ORDER_BY"] = [$direction => $column];
    }

    /**
     * This function adds a limit to the query
     * If you use the limit function you can't use the pagination function
     * @param integer $maxItems The amount of items to display, `0` means unlimited
     */
    public function Limit(int $maxItems = 0){
        $this->queryConstructor["6_LIMIT"] = $maxItems;
    }

    /**
     * This function adds pagination to the query
     * If you use the pagination function you can't use the limit function
     * @param integer $Amount
     * @param integer $Page
     */
    public function Pagination(int $amount, int $page){
        $pageEndAmount = ($page - 1) * $amount;

        $this->queryConstructor["6_LIMIT"] = $pageEndAmount.",".$amount;
    }

    /**
     * This function constructs the where clause based on the specified where statements
     * @return string
     */
    private function ConstructWhereClause(){
        $constructor = "";

        // Check if where statements have been added
        if(count((array)$this->queryConstructor["3_WHERE"]) > 0){
            $index = 0;
            foreach((array)$this->queryConstructor["3_WHERE"] as $where){
                $column = StringHelpers::SplitString(key($where), ";", 0);
                $value = $where[key($where)];
                $operator = StringHelpers::SplitString(key($where), ";", 1);

                if($index == 0){
                    $constructor .= "WHERE";
                }else{
                    $key = array_keys((array)$this->queryConstructor["3_WHERE"])[$index];
                    $extender = trim(StringHelpers::SplitString($key, ";", 1));

                    if($extender === "AND"){
                        $constructor .= " ";
                        $constructor .= "AND";
                    }else{
                        $constructor .= " ";
                        $constructor .= "OR";
                    }
                }

                $constructor .= " ";

                // Check for specific operators
                switch($operator){
                    case QueryOperatorsEnum::Like:
                        $constructor .= "`$column` LIKE '%$value%'";
                        break;

                    default:
                        $constructor .= "`$column` $operator '$value'";
                        break;
                }

                $index++;
            }
        }

        return $constructor;
    }

    /**
     * This function constructs the select statements
     * @return string
     */
    private function ConstructSelectQuery(){
        $constructor = "";

        // Construct the query
        $constructor .= "SELECT ".$this->queryConstructor["1_DATA"]["SELECT"];
        $constructor .= " ";
        $constructor .= "FROM ".$this->queryConstructor["2_FROM"];
        $constructor .= " ";

        $constructor .= $this->ConstructWhereClause();

        // Add GroupBy, Order, Limit, etc.
        $constructor .= " ";

        // Check if we must add a GroupBy
        if(!StringHelpers::IsNullOrWhiteSpace($this->queryConstructor["4_GROUP_BY"])){
            $constructor .= "GROUP BY ".$this->queryConstructor["4_GROUP_BY"];
            $constructor .= " ";
        }

        // Check if we must add a OrderBy
        if(!StringHelpers::IsNullOrWhiteSpace($this->queryConstructor["5_ORDER_BY"])){
            $orderByDirection = key((array)$this->queryConstructor["5_ORDER_BY"]);
            $orderByColumn = $this->queryConstructor["5_ORDER_BY"][$orderByDirection];

            $constructor .= "ORDER BY ".$orderByColumn." ".$orderByDirection;
            $constructor .= " ";
        }

        if($this->queryConstructor["6_LIMIT"] !== 0){
            $constructor .= "LIMIT ".$this->queryConstructor["6_LIMIT"];
        }

        return $constructor;
    }

    /**
     * This function constructs the insert statement
     * @return string
     */
    private function ConstructInsertQuery(){
        $constructor = "";

        // Construct the query
        if($this->ignoreDuplicatesOnInsert){
            $constructor .= "INSERT IGNORE INTO ".$this->queryConstructor["2_FROM"];
        }else{
            $constructor .= "INSERT INTO ".$this->queryConstructor["2_FROM"];
        }

        $constructor .= " ";

        $columns = "(";
        $values = "(";
        foreach($this->queryConstructor["1_DATA"] as $column => $value){
            $columns .= "`$column`,";

            // Check if the value is a string, otherwise just add the value to the query
            if(gettype($value) == "string"){
                $values .= "'".addslashes($value)."',";
            }else{
                $values .= $value.",";
            }
        }

        $constructor .= rtrim($columns, ',').")";
        $constructor .= " VALUES ";
        $constructor .= rtrim($values, ',').")";

        return $constructor;
    }

    /**
     * This function constructs the update statement
     * @return string
     */
    private function ConstructUpdateQuery(){
        $constructor = "";

        // Construct the query
        $constructor .= "UPDATE ".$this->queryConstructor["2_FROM"];
        $constructor .= " ";

        $updates = "SET ";
        foreach($this->queryConstructor["1_DATA"] as $column => $value){
            if(gettype($value) == "string"){
                $values .= $updates .= "`$column` = '".addslashes($value)."',";
            }else{
                $updates .= "`$column` = $value,";
            }
        }

        $constructor .= rtrim($updates, ",");
        $constructor .= " ";
        $constructor .= $this->ConstructWhereClause();

        return $constructor;
    }

    /**
     * This function constructs the delete query
     * @return string
     */
    private function ConstructDeleteQuery(){
        $constructor = "";

        $constructor .= "DELETE FROM ".$this->queryConstructor["2_FROM"]." ";
        $constructor .= $this->ConstructWhereClause();

        if(count((array)$this->queryConstructor["3_WHERE"]) == 0){
            echo("You must specify a where statement when using the delete function");
            exit();
        }

        return $constructor;
    }

    /**
     * This function will render the query and all it's specified statements
     * @return string
     */
    public function Render(){
        $constructor = "";

        // Check if a table is specified
        if(StringHelpers::IsNullOrWhiteSpace($this->queryConstructor["2_FROM"])){
            echo("You must specify a table, use the `Table` function to add a table to the constructor");
            exit();
        }

        // Check the statement and run the correct constructor
        switch($this->queryConstructor["0_STATEMENT"]){
            case "SELECT": $constructor = $this->ConstructSelectQuery(); break;
            case "INSERT": $constructor = $this->ConstructInsertQuery(); break;
            case "UPDATE": $constructor = $this->ConstructUpdateQuery(); break;
            case "DELETE": $constructor = $this->ConstructDeleteQuery(); break;
        }

        return $constructor;
    }
}
?>