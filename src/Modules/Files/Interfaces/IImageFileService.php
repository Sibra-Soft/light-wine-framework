<?php
namespace LightWine\Modules\Files\Interfaces;

use LightWine\Modules\Files\Models\ImageFileReturnModel;

interface IImageFileService
{
    /**
     * Gets a image from the database or cache using the specified filename
     * @param string $filename The filename of the image you want to get
     * @return ImageFileReturnModel Model containing all the details of the image
     */
    public function GetImageByName(string $filename): ImageFileReturnModel;
}
