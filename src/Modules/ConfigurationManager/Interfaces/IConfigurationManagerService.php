<?php
namespace LightWine\Modules\ConfigurationManager\Interfaces;

interface IConfigurationManagerService
{
    public function GetAppSetting(string $name, string $default = "");
    public function ConnectionStrings(string $connectionStringName, string $keyValue):string;
}
?>