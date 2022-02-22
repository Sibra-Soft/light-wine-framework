<?php
namespace LightWine\Components\Basket;

class Basket {
    private $controlId = 0;
    private \models\BasketModel $basket;

    private function RenderControl(){
        $control = new \ControlRoot($this->controlId);
        $this->basket = new \models\BasketModel;

        $this->basket;
    }

    private function GetBasketTotalPriceInclVat(){

    }

    private function GetBasketTotalPriceExclVat(){

    }

    public function GetBasketVatPrice(){

    }

    public function Init($id){
        $this->controlId = $id;

        $this->RenderControl();
    }
}
?>