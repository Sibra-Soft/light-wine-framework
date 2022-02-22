<?php
namespace LightWine\Modules\Resources\Models;

class ImageResourceReturnModel
{
    public string $Filename = "";
    public string $Link = "";
    public string $AltTag = "";

    public DateTime $ImageCreationDate;
    public DateTime $ImageCacheDate;

    public int $Height = 0;
    public int $Width = 0;
}