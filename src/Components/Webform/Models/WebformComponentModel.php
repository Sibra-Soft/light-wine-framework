<?php
namespace LightWine\Components\Webform\Models;

class WebformComponentModel
{
    public int $FormId = 0;
    public int $MailTemplate = 0;

    public bool $SendMail = false;
    public bool $SaveToDatabase = true;
}