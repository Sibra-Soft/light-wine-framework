<?php
namespace LightWine\Core\Enums;

abstract class RouteTypeEnum
{
    const VIEW = "view";
    const CHANNEL = "channel";
    const WEBMETHOD = "webmethod";
    const API_HANDLER = "api-handler";
    const REDIRECT = "redirect";
}