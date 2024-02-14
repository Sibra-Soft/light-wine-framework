<?php
namespace LightWine\Core\Enums;

abstract class RouteTypeEnum
{
    const VIEW = "view";
    const WEBVIEW = "web";
    const API = "api";
    const REDIRECT = "redirect";
    const CONTROLLER = "controller";
}