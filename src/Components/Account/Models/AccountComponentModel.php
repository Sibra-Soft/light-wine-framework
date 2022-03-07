<?php
namespace LightWine\Components\Account\Models;

class AccountComponentModel
{
    public int $Mode = 3;
    public int $PasswordEncryption = 1;

    public bool $SendMailAfterCreatingAccount = false;
    public bool $MustEnterOldPassword = false;
    public bool $PasswordsMustMatch = false;
    public bool $RedirectIfLoggedIn = false;
    public bool $RedirectAfterLogin = false;

    public string $UsernameRequestFieldname = "login-username";
    public string $PasswordRequestFieldname = "login-password";
    public string $BlowfishSecret = "";
    public string $RedirectUrl = "";
    public string $MainTemplate = "";
    public string $ErrorTemplate = "";
    public string $SuccessTemplate = "";
    public string $MailTemplate = "";
}