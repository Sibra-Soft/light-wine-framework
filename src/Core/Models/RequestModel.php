<?php
namespace LightWine\Core\Models;
use DateTime;

class RequestModel
{
    public array $Querystring;
    public array $Form;
    public array $Headers;

    public string $RequestUrl;

    public DateTime $RequestTime;
    
    public RouteModel $Route;
}