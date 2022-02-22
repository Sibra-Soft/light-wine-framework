<?php
namespace LightWine\Core\Interfaces;

use LightWine\Core\Models\RequestModel;

interface IRequestService
{
    public function GetRouteBasedOnRequestUrl(): RequestModel;
}
