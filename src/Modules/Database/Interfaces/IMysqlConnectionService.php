<?php
namespace LightWine\Modules\Database\Interfaces;

use \PDOStatement;

interface IMysqlConnectionService
{
    public function DatasetFirstRow(string $column, string $typeOf = "string");
    public function ExecuteQuery(string $query): PDOStatement;
    public function GetFieldset(string $tableOrQuery, string $type = "table");
    public function ClearParameters();
    public function AddParameter(string $name, $value, $default = null);
    public function GetDatasetAsJson(string $query): array;
    public function GetDataset(string $query = null);
}
