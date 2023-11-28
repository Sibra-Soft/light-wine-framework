<?php
namespace LightWine\Modules\Routing\Models;

use LightWine\Core\Helpers\StringHelpers;

class ViewRouteModel
{
    public function __construct(RouteModel $route){
        $this->MetaTitle = $route->MetaTitle;
        $this->MetaDescription = $route->MetaDescription;
        $this->TemplateId = (int)StringHelpers::SplitString($route->Action, ";", 1);
    }

    public string $MetaTitle;
    public string $MetaDescription;

    public int $TemplateId;
}
?>