<?php
namespace LightWine\Core\Helpers;

class TraceHelpers
{
    public static array $tracing = [];

    /**
     * Writes trace information to the trace log.
     * @param string $message The message you want to write to the trace log
     */
    public static function Write(string $message){
        array_push(self::$tracing, [
            "category" => "page",
            "timestamp" => Helpers::Now()->format("Y-m-d h:i:s"),
            "message" => $message,
            "warn" => false
        ]);
    }

    /**
     * Writes trace information to the trace log. Unlike Write(String), all warnings appear in the log as red text.
     * @param string $message The message you want to write to the trace log
     */
    public static function Warn(string $message){
        array_push(self::$tracing, [
            "category" => "page",
            "timestamp" => Helpers::Now()->format("Y-m-d h:i:s"),
            "message" => $message,
            "warn" => true
        ]);
    }
}