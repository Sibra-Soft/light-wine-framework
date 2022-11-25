<?php
namespace LightWine\Components\Account\Enums;

abstract class ComponentModes
{
    const Create = 0;
    const Delete = 1;
    const ConfirmAccount = 2;
    const Login = 3;
    const ForgotPassword = 4;
    const ResetPassword = 5;
    const Logoff = 6;
}

