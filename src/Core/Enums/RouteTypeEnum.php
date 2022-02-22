<?php
namespace LightWine\Core\Enums;

abstract class RouteTypeEnum
{
    const TemplateLink = "template-link";
    const Channel = "channel";
    const PageLink = "page-link";
    const Webmethod = "webmethod";
    const ApiHandler = "api-handler";
}