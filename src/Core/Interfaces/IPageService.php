<?php
namespace LightWine\Core\Interfaces;

use \LightWine\Core\Models\PageModel;
use LightWine\Modules\Routing\Models\ViewRouteModel;

interface IPageService
{
    public function Render(ViewRouteModel $route): PageModel;
}
