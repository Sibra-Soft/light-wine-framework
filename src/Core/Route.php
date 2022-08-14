<?php
namespace LightWine\Core;

class Route
{
    public static $Routes = [];

    public static function Get(string $url){
        array_push(self::$Routes, [
            "type" => "get",
            "method" => "GET",
            "url" => $url,
            "source" => ""
        ]);
    }

    public static function Post(string $url){

    }

    public static function Put(string $url){

    }

    public static function Delete(string $url){

    }

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

    public static function View(string $url, string $template, array $options){
        array_push(self::$Routes, [
            "type" => "view",
            "method" => "GET",
            "url" => $url,
            "source" => $template,
            "options" => $options
        ]);
    }

    public static function WebMethod(string $url, string $name, array $options){
        array_push(self::$Routes, [
            "type" => "webmethod",
            "method" => "GET",
            "url" => $url,
            "source" => $name,
            "options" => $options
        ]);
    }

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