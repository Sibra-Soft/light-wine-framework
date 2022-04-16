<?php
namespace LightWine\Providers\ComponentServiceProvider;

use LightWine\Core\Services\ComponentsService;
use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\HttpResponse;

class ComponentServiceProvider
{
    private ComponentsService $componentService;

    public function __construct(){
        $this->componentService = new ComponentsService();
    }

    public function Render(){
        $componentName = RequestVariables::Get("name");
        $componentContent = $this->componentService->HandleRenderComponent($componentName);

        HttpResponse::SetContentType("text/html");
        HttpResponse::SetData($componentContent);

        exit();
    }
}