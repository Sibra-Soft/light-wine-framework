<?php
namespace LightWine\Modules\Communication\Models;

class MailMessageModel
{
    public string $FromName = "";
    public string $FromAddress = "";
    public string $ToName = "";
    public string $EmailAddress = "";
    public string $Subject = "";
    public string $Body = "";

    public bool $UseHtml = false;
}