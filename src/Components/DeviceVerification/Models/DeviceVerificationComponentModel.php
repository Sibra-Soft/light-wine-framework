<?php
namespace LightWine\Components\DeviceVerification\Models;
use LightWine\Components\DeviceVerification\Enums\ComponentModes;

class DeviceVerificationComponentModel
{
    public int $Mode = ComponentModes::Check;

    public string $CheckTemplate = "";
    public string $VerifyTemplate = "";
    public string $MailTemplate = "";
    public string $SmsTemplate = "";
    public string $ConfirmTemplate = "";
    public string $RedirectUrlIfVerified = "";
    public string $ErrorTemplate = "";

    public bool $UseLinkToVerify = true;
    public bool $UsePincodeToVerify = false;
    public bool $SendMailToVerify = true;
    public bool $SendSmsToVerify = false;
}