<?php
namespace LightWine\Core;

use LightWine\Core\Helpers\Helpers;

class HttpResponse
{
    public static function SetCookie(string $name, string $value){

    }

    /**
     * Set the content type of the response
     * @param string $contentType The content type mime string
     */
    public static function SetContentType(string $contentType){
        header("Content-Type: ".$contentType);
    }

    /**
     * Set data to send back as response
     * @param mixed $data The data to send as response
     */
    public static function SetData(mixed $data){
        echo($data);
    }

    /**
     * Add a extra header
     * @param string $name The name of the header entry to add
     * @param mixed $value The value of the header entry to add
     * @param bool $replace Must the value be replaced if it already exists
     */
    public static function SetHeader(string $name, mixed $value, bool $replace = true){
        header($name.":".$value);
    }

    /**
     * Send a file on the server as request
     * @param string $file The full path of the file to be send
     */
    public static function SetFile(string $file){

    }

    /**
     * Redirect to a specified location
     * @param string $url The url the user me be redirected to
     * @param array $querystring Querystring parameters that must be added when redirecting
     * @param int $status The code that must be used when redirecting
     */
    public static function Redirect(string $url, array $querystring, int $status = 302){

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
}