<?php
namespace LightWine\Core\Interfaces;

use \LightWine\Core\Models\PageModel;
use \LightWine\Modules\Routing\Models\RouteModel;

interface IPageService
{
    public function Render(RouteModel $route): PageModel;
}
