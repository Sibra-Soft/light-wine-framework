<?php
namespace LightWine\Core;

class Route
{
    public static $Routes = [];

    /**
     * Creates a GET api handler
     * @param string $url The url you want to catch
     * @param string $datasource The datasource you want to use, can be (query or table)
     */
    public static function Get(string $url, string $datasource){
        array_push(self::$Routes, [
            "type" => "get",
            "method" => "GET",
            "url" => $url,
            "source" => $datasource
        ]);
    }

    /**
     * Creates a POST api handler
     * @param string $url The url you want to catch
     * @param string $datasource The datasource you want to use, can be (query or table)
     */
    public static function Post(string $url, string $datasource){
        array_push(self::$Routes, [
            "type" => "post",
            "method" => "POST",
            "url" => $url,
            "source" => $datasource
        ]);
    }

    /**
     * Creates a PUT api handler
     * @param string $url The url you want to catch
     * @param string $datasource The datasource you want to use, can be (query or table)
     */
    public static function Put(string $url, string $datasource){
        array_push(self::$Routes, [
            "type" => "put",
            "method" => "PUT",
            "url" => $url,
            "source" => $datasource
        ]);
    }

    /**
     * Creates a DELETE api handler
     * @param string $url The url you want to catch
     * @param string $datasource The datasource you want to use, can be (query or table)
     */
    public static function Delete(string $url, string $datasource){
        array_push(self::$Routes, [
            "type" => "delete",
            "method" => "DELETE",
            "url" => $url,
            "source" => $datasource
        ]);
    }

    /**
     * Creates a redirect route handler
     * @param string $url The url you want to catch
     * @param string $targetLocation The redirect location
     * @param int $type The type of redirect you want to use (301, 302)
     */
    public static function Redirect(string $url, string $targetLocation, int $type = 302){
        array_push(self::$Routes, [
            "type" => "redirect",
            "method" => "GET",
            "url" => $url,
            "source" => $targetLocation,
            "options" => [
                "redirect_type" => $type
            ]
        ]);
    }

    /**
     * Creates a view handler
     * @param string $url The url you want to catch
     * @param string $template The template you want to show
     * @param array $options Add a array of extra options
     */
    public static function View(string $url, string $template, array $options){
        array_push(self::$Routes, [
            "type" => "view",
            "method" => "GET",
            "url" => $url,
            "source" => $template,
            "options" => $options
        ]);
    }

    /**
     * Creates a webmethod handler
     * @param string $url The url you want to catch
     * @param string $name The name of the webmethod
     * @param array $options Add a array of extra options
     */
    public static function WebMethod(string $url, string $name, array $options){
        array_push(self::$Routes, [
            "type" => "webmethod",
            "method" => "GET",
            "url" => $url,
            "source" => $name,
            "options" => $options
        ]);
    }

    /**
     * Create a controller handler
     * @param string $url The url you want to catch
     * @param string $module The name of the module you want to call
     * @param string $controller The name of the controller function, you want to call
     * @param string $method The method that must be used for the request (GET, POST, PUT, DELETE)
     * @param array $options Add a array of extra options
     */
    public static function Controller(string $url, string $module, string $controller, string $method, array $options){
        array_push(self::$Routes, [
            "type" => "controller",
            "method" => $method,
            "url" => $url,
            "source" => $module."~".$controller,
            "options" => $options
        ]);
    }
}