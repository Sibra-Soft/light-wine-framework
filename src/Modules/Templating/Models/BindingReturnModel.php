<?php
namespace LightWine\Modules\Templating\Models;

class BindingReturnModel
{
    public array $BindingResult;

    public string $BindingName = "";

    public int $BindingQueryId = 0;
    public int $BindingTemplateId = 0;
    public int $BindingCount = 0;
}