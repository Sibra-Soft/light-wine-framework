<?php
namespace LightWine\Core\Interfaces;

interface IComponentBase
{
    public function GetSettings(string $setting): string;
    public function GetControlTemplate(string $name): string;
}
