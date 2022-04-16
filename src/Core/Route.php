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

    }

    public static function View(string $url, int $template, array $options){
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
}