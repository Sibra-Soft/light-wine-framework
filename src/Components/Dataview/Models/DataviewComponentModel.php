<?php
namespace LightWine\Components\Dataview\Models;

use LightWine\Components\Dataview\Enums\ComponentModes;

class DataviewComponentModel
{
    public int $Mode = ComponentModes::Dataview;
    public int $MaxItemsVisible = 50;
    public int $MainQueryTemplate = 0;
    public int $CountQueryTemplate = 0;

    public string $RepeatTemplate = "";
    public string $HeaderTemplate = "";
    public string $FooterTemplate = "";
    public string $NodataTemplate = "";
    public string $PaginationTemplate = "";

    public bool $EnablePagination = false;
    public bool $DetermineOddEvenRows = false;
}