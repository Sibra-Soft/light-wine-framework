<?php
namespace LightWine\Core;

use \DateTime;

class HttpRequest
{
    /**
     * Gets the current domain
     * @return string The current domain
     */
    public static function Domain(): string {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Add multiple cookies to the current request
     * @param array $cookies A array of cookies to add
     */
    public static function AddCookies(array $cookies){

    }

    /**
     * Get the current request time
     * @return DateTime The request time of the current request
     */
    public static function RequestTime(): DateTime {
        return $_SESSION["RequestTime"];
    }

    /**
     * Get the request url without the querystring parameters
     * @return string The request url without the querystring
     */
    public static function RequestUrlWithoutQuerystring(): string {
        return strtok($_SERVER["REQUEST_URI"], '?');
    }

    /**
     * Get the request url with the querystring parameters
     * @return string The request url with the querystring
     */
    public static function RequestUrlWithQuerystring(): string {
        return $_SERVER["REQUEST_URI"];
    }
}
?>