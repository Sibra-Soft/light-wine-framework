<?php
namespace LightWine\Core\Interfaces;

interface IComponentService
{
    public function HandleRenderComponent(string $name): string;
}
