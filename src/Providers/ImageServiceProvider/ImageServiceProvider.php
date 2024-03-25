<?php
namespace LightWine\Providers\ImageServiceProvider;

use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\HttpResponse;
use LightWine\Modules\Files\Services\ImageFileService;

class ImageServiceProvider
{
    private ImageFileService $imageFileService;
    
    public function __construct(){
        $this->imageFileService = new ImageFileService();
    }

    public function Render(){
        $filename = RequestVariables::Get("filename");
        $image = $this->imageFileService->GetImage($filename);

        HttpResponse::$MinifyHtml = false;

        HttpResponse::SetHeader("pragma", "public");
        HttpResponse::SetHeader("Cache-Control", "max-age=86400");
        HttpResponse::SetHeader("Expires", gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

        HttpResponse::SetContentType($image->File->Mime);
        HttpResponse::SetData($image->File->Blob);
        exit();
    }
}