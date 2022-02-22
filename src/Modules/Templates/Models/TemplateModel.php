<?php
namespace LightWine\Modules\Templates\Models;

// Settings Model
class TemplateSettingsModel {
    public string $Name = "";
    public string $StylingResources = "";
    public string $ScriptResources = "";

    public int $CachingHours = 0;
}

// Main Template Model
class TemplateModel {
    public function __construct(){
        $this->Settings = new TemplateSettingsModel;
        $this->Policies = new TemplatePolicyModel;
    }

    public int $Id = 0;

    public string $UnqiueId = "";
    public string $Name = "";
    public string $Content = "";
    public string $ContentNotMinified = "";

    public TemplatePolicyModel $Policies;

    public bool $IsFromCache = false;
    public bool $Found = true;

    public \DateTime $DateCached;
    public \DateTime $DateCreated;
    public \DateTime $DateModified;
    public TemplateSettingsModel $Settings;
}