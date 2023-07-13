<?php
namespace LightWine\Modules\Files\Interfaces;

interface IUploadFileService
{
    /**
     * Upload a file based on the specified url
     * @param string $url The url of the file you want to download
     */
    public function UploadFileBasedOnUrl(string $url);

    /**
     * Upload a file that is specified in a webform
     */
    public function UploadFileFromWebform();
}
