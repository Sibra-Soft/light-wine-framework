<?php
namespace LightWine\Components\ShoppingBasket;

use LightWine\Components\ComponentBase;
use LightWine\Components\ShoppingBasket\Models\ShoppingBasketComponentModel;
use LightWine\Core\Helpers\TraceHelpers;
use LightWine\Modules\Templating\Services\StringTemplaterService;
use LightWine\Modules\Templating\Services\TemplatingService;

use \ShippingCostModel;

class ShoppingBasket
{
    private ComponentBase $component;
    private ShoppingBasketComponentModel $settings;
    private StringTemplaterService $templating;
    private TemplatingService $templatingService;

    public function __construct(int $id){
        $this->component = new ComponentBase();
        $this->templating = new StringTemplaterService();
        $this->settings = $this->component->GetSettings(new ShoppingBasketComponentModel, $id);
        $this->templatingService = new TemplatingService();
    }

    public function Init(){
        return $this->RenderComponent();
    }

    /**
     * Add a new line to the basket
     * @param string $description The description of the line that must be added
     * @param float $price The price of the line that must be added
     * @param int $Quantity The quantity of the line that must be added
     */
    public function AddLineToBasket(string $description, float $price, int $Quantity){

    }

    /**
     * Updates the quantity of a existing line
     * @param int $lineId The id of the line that must be updated
     * @param int $newQuantity The new quantity of the specified id
     */
    public function UpdateLineQuantity(int $lineId, int $newQuantity){

    }

    public function GetVatAmount(){

    }

    /**
     * Caculates the total price of the shopping basket based on the specified vat amount
     * @param bool $includeVat If set the vat will be added to the totalamount
     * @param int $vatAmount The amount of vat that must be added
     */
    public function CalculateTotalPrice(bool $includeVat, int $vatAmount = 21): float {
        return 0;
    }

    /**
     * Get the amount of products added to the basket
     * @return int The amount of products added
     */
    public function GetBasketProductCount(): int {
        return 0;
    }

    /**
     * Calculates the shipping cost based on a specified model
     * @param ShippingCostModel $model The model containing all the details of the shipment
     * @return float The amount of shipping cost
     */
    public function CalculateShippingCost(ShippingCostModel $model): float {
        return 0;
    }

    public function CaculateDiscountBasedOnCoupons(){

    }

    public function RenderComponent(){
        $this->templating->ClearVariables();

        TraceHelpers::Write("Start render shopping basket component");

        $this->templating->AssignVariable("products_count", $this->GetBasketProductCount());
        $this->templating->AssignVariable("total_incl_vat", $this->CalculateTotalPrice(true));
        $this->templating->AssignVariable("total_excl_vat", $this->CalculateTotalPrice(false));
        //$this->templating->AssignVariable("total_shipping_cost", $this->CalculateShippingCost());
        $this->templating->AssignVariable("total_discount", $this->CaculateDiscountBasedOnCoupons());
        $this->templating->AssignVariable("total_vat", $this->GetVatAmount());

        $template = $this->templating->DoReplacements($this->settings->MainTemplate);
        $template = $this->templatingService->ReplaceExtensions($template);

        TraceHelpers::Write("End render shopping basket component");

        return $template;
    }
}
?>