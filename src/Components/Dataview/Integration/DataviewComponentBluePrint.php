<?php
namespace LightWine\Components\Dataview\Integration;

use LightWine\Components\Dataview\Enums\ComponentModes;

class DataviewComponentBluePrint
{
    public array $Mode = [
        "Caption" => "Component Mode",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "Select the component mode you want to use",
        "Field" => "dropdown",
        "FieldValues" => ComponentModes::class
    ];

    public array $RepeatTemplate = [
        "Caption" => "Repeat template",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "Enter the template you want to repeat for every row of the query",
        "Field" => "codemirror"
    ];

    public array $HeaderTemplate = [
        "Caption" => "Header template",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "",
        "Field" => "codemirror"
    ];

    public array $FooterTemplate = [
        "Caption" => "Footer template",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "",
        "Field" => "codemirror"
    ];

    public array $NodataTemplate = [
        "Caption" => "No data template",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "",
        "Field" => "codemirror"
    ];

    public array $MainQueryTemplate = [
        "Caption" => "Main query template",
        "Tab" => "General",
        "Mode" => "3",
        "Group" => "General",
        "Description" => "Select the query you want to use for this dataview component",
        "Field" => "dropdown",
        "FieldValues" => "templates~sql"
    ];
}