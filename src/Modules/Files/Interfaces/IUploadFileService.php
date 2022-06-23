<?php
namespace LightWine\Modules\Files\Interfaces;

interface IUploadFileService
{
    public function UploadFileBasedOnUrl(string $url);
    public function UploadFileFromWebform();
}
