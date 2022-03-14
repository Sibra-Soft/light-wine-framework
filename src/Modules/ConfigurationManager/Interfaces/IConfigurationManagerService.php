<?php
namespace LightWine\Modules\ConfigurationManager\Interfaces;

interface IConfigurationManagerService
{
    public function GetAppSetting(string $name, string $default = ""):string;
    public function ConnectionStrings(string $connectionStringName, string $keyValue):string;
}
?>