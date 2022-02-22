<?php
namespace LightWine\Core\Interfaces;

use \LightWine\Core\Models\PageModel;

interface IPageService
{
    public function Render(): PageModel;
}
