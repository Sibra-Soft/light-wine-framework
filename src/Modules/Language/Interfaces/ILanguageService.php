<?php
namespace LightWine\Modules\Language\Interfaces;

interface ILanguageService
{
    public function WriteOrRefreshCacheTranslations();
    public function GetTranslation(string $anchor): string;
}
