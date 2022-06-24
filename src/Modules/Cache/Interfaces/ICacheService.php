<?php
namespace LightWine\Modules\Cache\Interfaces;

interface ICacheService
{
    public function CheckCache(string $name);
    public function AddArrayToCache(string $name, array $array);
    public function GetArrayFromCacheBasedOnName(string $name);
    public function ClearCache();
    public function ClearAllCache();
}
