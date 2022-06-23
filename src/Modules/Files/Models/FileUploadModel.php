<?php
namespace LightWine\Modules\Files\Models;

use \DateTime;

class FileUploadModel
{
    public DateTime $DateCreated;

    public string $MimeType;
    public string $Filename;
    public string $File;
    public string $Url;
    public string $ObjectType = "image";
    public string $Extension;

    public int $ParentFolder;
    public int $ItemId;
    public int $FileSize;
    public int $Id;
    public int $UserId;
    public int $ImageWidth;
    public int $ImageHeight;
}