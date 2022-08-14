<?php
namespace LightWine\Modules\Database\Interfaces;

use \PDOStatement;

interface IMysqlConnectionService
{
    /**
     * This function gets the specified column row value
     * @param string $column The columnd containing the data you want to select
     * @param string $typeOf The type you want to return (string, integer, datatime, boolean)
     * @return mixed The selected data
     */
    public function DatasetFirstRow(string $column, string $typeOf = "string");

    /**
     * Execute a specified query
     * @param string $query The query you want to execute
     * @return PDOStatement
     */
    public function ExecuteQuery(string $query): PDOStatement;

    /**
     * Get the fieldset of a specified query
     * @param string $tableOrQuery The tablename or the query to execute
     * @param string $type The type (table or query)
     * @return array[] Array containing all the fields of the table or query
     */
    public function GetFieldset(string $tableOrQuery, string $type = "table");

    /**
     * Clear all specified query parameters
     */
    public function ClearParameters();

    /**
     * Add query parameter to the query
     * @param string $name The name of the parameter
     * @param string $value The value of the parameter
     * @param string $default (optional) The default value if the value is empty
     */
    public function AddParameter(string $name, $value, $default = null);

    /**
     * Gets a dataset and returns json
     * @param string $query The query to get the dataset off
     * @return string The json of the dataset
     */
    public function GetDatasetAsJson(string $query): array;

    /**
     * Get a dataset from the specified query
     * @param string $query The query you want to execute
     * @return array[] Array containg all the rows of the executed query
     */
    public function GetDataset(string $query = null);

    /**
     * Executes a query based on the specified *.sql file
     * @param string $file The .sql file containing the query you want to execute
     * @return array The returned dataset of the executed query
     */
    public function ExecuteQueryBasedOnFile(string $file): array;
}
