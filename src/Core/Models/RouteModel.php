<?php
namespace LightWine\Core\Models;

class RouteModel
{
    public bool $Published;
    public bool $NotFound = false;

    public string $MatchPattern;
    public string $Name;
    public string $Datasource;
    public string $Url;
    public string $MetaTitle;
    public string $MetaDescription;

    public string $Type;

    public array $AllowedMethodes;
    public array $Variables;
}