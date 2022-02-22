<?php
namespace LightWine\Modules\Cache\Models;

use \DateTime;

class CacheServiceReturnModel
{
    public DateTime $DateCreated;
    public DateTime $DateCached;

    public string $UniqueId;

    public bool $IsFromCache = false;

    public $Data;
}