<?php
namespace LightWine\Core\Interfaces;

use \LightWine\Core\Models\ResponseModel;

interface IServerService
{
    public function Start(): ResponseModel;
    public function ShowFileBrowser(string $path);
    public function ShowSimplePage(string $content);
}
