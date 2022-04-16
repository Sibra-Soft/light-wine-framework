<?php
namespace LightWine\Modules\Logger\Interfaces;

interface ILoggerService
{
    public function LogSiteVisitor();
    public function LogDebug(string $message, string $category = "general", $id = 0);
    public function LogError(string $message, string $category = "general", $id = 0);
    public function LogWarning(string $message, string $category = "general", $id = 0);
    public function LogInformation(string $message, string $category = "general", $id = 0);
}
