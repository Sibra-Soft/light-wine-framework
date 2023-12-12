<?php
namespace LightWine\Modules\Routing\Models;

class RouteModel
{
    public bool $NotFound = false;

    public string $Name;
    public string $MatchPattern;
    public string $Action;
    public string $Url;
    public string $MetaTitle;
    public string $MetaDescription;
    public string $Method;
    public string $Middleware;

    public array $RoutingParams = [];
    public array $Parameters = [];
}