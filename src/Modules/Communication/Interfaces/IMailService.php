<?php
namespace LightWine\Modules\Communication\Interfaces;

use LightWine\Modules\Communication\Models\MailMessageModel;

interface IMailService
{
    public function SendMail(MailMessageModel $mail);
}
