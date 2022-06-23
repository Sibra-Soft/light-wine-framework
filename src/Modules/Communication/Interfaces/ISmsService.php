<?php
namespace LightWine\Modules\Communication\Interfaces;

use LightWine\Modules\Communication\Models\SmsMessageModel;

interface ISmsService
{
    public function SendSms(SmsMessageModel $sms);
}
