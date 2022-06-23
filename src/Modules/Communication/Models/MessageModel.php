<?php
namespace LightWine\Modules\Communication\Models;

use \DateTime;

class MessageModel
{
    public int $UserId = 0;
    public int $TemplateId = 0;

    public string $ReceiverName = "";
    public string $Receiver = "";
    public string $Subject = "";
    public string $Body = "";
    public string $Type = "email";

    public DateTime $DateScheduled;

    public array $Attachements = [];
    public array $Variables = [];
}