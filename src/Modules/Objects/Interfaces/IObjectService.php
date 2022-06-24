<?php
namespace LightWine\Modules\Objects\Interfaces;

interface IObjectService
{
    public function FindObjectBasedOnDomainName(string $key, string $default = ""): string;
}
