<?php
namespace LightWine\Core\Enums;

abstract class RouteTypeEnum
{
    const VIEW = "web";
    const API = "api";
    const REDIRECT = "redirect";
    const CONTROLLER = "controller";
}