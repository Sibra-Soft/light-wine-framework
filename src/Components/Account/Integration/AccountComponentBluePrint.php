<?php
namespace LightWine\Components\Account\Integration;

use LightWine\Components\Account\Enums\ComponentModes;
use LightWine\Components\Account\Enums\EncryptionTypes;

class AccountComponentBluePrint
{
    public array $Mode = [
        "Caption" => "Component Mode",
        "Mode" => "*",
        "Tab" => "General",
        "Group" => "General",
        "Description" => "Select the component mode you want to use",
        "Field" => "dropdown",
        "FieldValues" => ComponentModes::class
    ];

    public array $PasswordEncryption = [
        "Caption" => "Password encryption",
        "Tab" => "General",
        "Mode" => "*",
        "Group" => "Security",
        "Description" => "Select the type of encryption you want to use",
        "Field" => "dropdown",
        "FieldValues" => EncryptionTypes::class
    ];

    public array $SendMailAfterCreatingAccount = [
        "Caption" => "Send mail after creating account",
        "Tab" => "General",
        "Mode" => "0",
        "Group" => "General",
        "Description" => "If set a mail will be send after the user created a account"
    ];

    public array $MustEnterOldPassword = [
        "Caption" => "Must enter old password",
        "Tab" => "General",
        "Mode" => "4,5",
        "Group" => "General"
    ];

    public array $RedirectIfLoggedIn = [
        "Caption" => "Redirect the user to a specified url when loggedin",
        "Tab" => "General",
        "Mode" => "3",
        "Group" => "General",
        "Description" => "If set the user will be redirected to the specified url when loggedin"
    ];
}