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
        $image = $this->imageFileService->GetImageByName($filename);

        HttpResponse::$MinifyHtml = false;

        if(!$image->Found){
            HttpResponse::ShowError(404, "The requested file could not be found", "File not found");
        }

        if(!$image->Permission){
            HttpResponse::ShowError(403, "You don't have permission to access the requested content", "Forbidden");
        }

        HttpResponse::SetHeader("pragma", "public");
        HttpResponse::SetHeader("Cache-Control", "max-age=86400");
        HttpResponse::SetHeader("Expires", gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

        HttpResponse::SetContentType($image->ContentType);
        HttpResponse::SetData($image->FileData);
        exit();
    }
}