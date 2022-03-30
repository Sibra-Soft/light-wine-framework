<?php
namespace LightWine\Core\Helpers;

class HttpContextHelpers
{
    /**
     * Logoff the current user
     */
    public static function Logoff(){
        unset($_SESSION["Checksum"]);
        header("location: /");
    }
}