<?php
namespace LightWine\Components\Webform\Models;

class WebformComponentModel
{
    public int $UseFormId = 0;

    public string $FormHtml = "";

    public bool $SendMail = false;
    public bool $SaveToDatabase = false;
}