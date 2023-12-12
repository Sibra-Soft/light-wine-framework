<?php
namespace LightWine\Components\ShoppingBasket\Models;

use LightWine\Components\ShoppingBasket\Enums\ComponentModes;

class ShoppingBasketComponentModel {
    public int $Mode = ComponentModes::View;

    public string $MainTemplate = "";
    public string $BasketQuery = "";
}
?>