<?php
namespace LightWine\Modules\QueryBuilder\Interfaces;

interface IQueryBuilderService
{
    public function Clear();
    public function Insert(string $table, string $column, $value);
    public function Delete(string $table);
    public function Update(string $table, string $column, $value);
    public function Select(string $table, string $columns = "*");
    public function Where(int $extender, string $column, string $operator, string $value);
    public function Order(string $column, string $direction);
    public function Limit(int $maxItems = 0);
    public function Pagination(int $amount, int $page);
    public function Render();
}
