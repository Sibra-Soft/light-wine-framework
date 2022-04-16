<?php
namespace LightWine\Providers\ResourceServiceProvider;

use LightWine\Core\HttpResponse;
use LightWine\Modules\Resources\Enums\ResourceTypeEnum;
use LightWine\Modules\Resources\Services\ResourceService;
use LightWine\Core\Helpers\RequestVariables;

class ResourceServiceProvider
{
    private ResourceService $imageFileService;

    public function __construct(){
        $this->resourceService = new ResourceService();
    }

    public function Render(){
        $filename = RequestVariables::Get("filename");
        $type = RequestVariables::Get("type");
        $single = (bool)RequestVariables::Get("single");

        $resourceContent = $this->resourceService->GetResourcesBasedOnFilename($filename, $type, $single);

        HttpResponse::SetHeader("Pragma", "public");
        HttpResponse::SetHeader("Cache-Control", "max-age=86400");
        HttpResponse::SetHeader("Expires", gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

        if($type == ResourceTypeEnum::CSS) HttpResponse::SetContentType("text/css");
        if($type == ResourceTypeEnum::JS) HttpResponse::SetContentType("application/javascript");

        HttpResponse::SetData($resourceContent);
        exit();
    }
}