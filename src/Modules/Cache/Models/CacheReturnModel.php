<?php
namespace LightWine\Modules\Cache\Models;

use \DateTime;

class CacheReturnModel
{
    public DateTime $DateCreated;
    public DateTime $DateCached;

    public string $UniqueId;
    public string $CacheFile;

    public bool $Cached;
}