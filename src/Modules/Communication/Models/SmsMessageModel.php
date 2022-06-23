<?php
namespace LightWine\Modules\Communication\Models;

class SmsMessageModel
{
    public string $FromName = "";
    public string $PhoneNumber = 0;
    public string $Subject = "";
    public string $Message = "";

    public bool $UseMessageQueue = true;

    public array $Attachments = [];
    public array $Variables = [];
}