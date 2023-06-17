<?php
namespace LightWine\Providers\RobotsTextServiceProvider;

use LightWine\Core\HttpResponse;

class RobotsTextServiceProvider
{
    public function Render(){
        $defaultRobotsTextContent = "User-agent: *\nAllow: /";

        HttpResponse::SetContentType("text/plain");
        HttpResponse::SetData($defaultRobotsTextContent);
        exit();
    }
}