<?php
namespace LightWine\Modules\Files\Models;

class ImageFileReturnModel
{
    public bool $Found = false;
    public bool $Permission = true;
    public bool $SaveToCache = false;

    public string $ContentType = "";
    public string $FileData = "";
    public string $Filename = "";
    public string $CacheKey = "";
    public string $CacheFile = "";

    public int $FileSize = 0;
}