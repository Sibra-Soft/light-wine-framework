<?php
namespace LightWine\Components\ShoppingBasket\Integration;

use LightWine\Components\ShoppingBasket\Enums\ComponentModes;

class ShoppingBasketComponentBluePrint
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

    public array $MainTemplate = [
        "Caption" => "Main template",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "",
        "Field" => "codemirror"
    ];

    public array $BasketQuery = [
        "Caption" => "Basket query",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "",
        "Field" => "codemirror"
    ];
}