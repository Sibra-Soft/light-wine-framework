<?php
namespace LightWine\Components\Dataview\Models;

class DataviewComponentModel
{
    public int $Mode = 0;
    public int $MaxItemsVisible = 50;

    public string $MainQueryTemplate = "";
    public string $CountQueryTemplate = "";
    public string $RepeatTemplate = "";
    public string $HeaderTemplate = "";
    public string $FooterTemplate = "";
    public string $NodataTemplate = "";
    public string $PaginationTemplate = "";

    public bool $EnablePagination = false;
    public bool $DetermineOddEvenRows = false;
}