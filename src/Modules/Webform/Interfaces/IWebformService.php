<?php
namespace LightWine\Modules\Webform\Interfaces;

interface IWebformService
{
    public function GetWebformById(int $id): string;
    public function GetWebformByName(string $name): string;
}
