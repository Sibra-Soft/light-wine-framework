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

    /**
     * Shows a error page using the specified variables
     * @param int $errorNumber The error number
     * @param string $errorHeader The header titel of the error
     * @param string $errorDescription The description of the error
     */
    public static function ShowError(int $errorNumber, string $errorHeader, string $errorDescription){
        header("HTTP/1.1 $errorNumber $errorHeader");

        $view = Helpers::GetFileContent("~/src/Views/Error.tpl");
        $view = str_replace("{{errorNumber}}", $errorNumber, $view);
        $view = str_replace("{{errorHeader}}", $errorHeader, $view);
        $view = str_replace("{{errorDescription}}", $errorDescription, $view);

        echo($view);
        die();
    }

    /**
     * Get a request variable from get or post
     * @param string $name
     * @param string $default
     * @return string
     */
    public static function RequestVariable($name, $default = ""){
        $returnValue = "";

        if(!isset($_POST[$name]) and !isset($_GET[$name])){
            $returnValue = $default;
        }else{
            if(isset($_POST[$name])){
                $returnValue = $_POST[$name];
            }else{
                $returnValue = $_GET[$name];
            }
        }

        return StringHelpers::Escape($returnValue);
    }
}