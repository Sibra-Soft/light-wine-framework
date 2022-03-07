<?php
namespace LightWine\Core\Helpers;

class ConvertHelpers
{
    /**
     * Array to object
     * @param array $data
     * @return object
     */
    public static function ArrayToObject(array $data = [], $instance) {
        foreach (get_object_vars($obj = $instance) as $property => $default) {
            if(!array_key_exists($property, $data)) continue;
            if(StringHelpers::IsNullOrWhiteSpace($data[$property])) continue;

            $obj->{$property} = $data[$property];
        }

        return $obj;
    }

    /**
     * Convert the given value to decimal
     * @param float $price
     * @return string
     */
    public static function ToMoney(float $price):string {
        return str_replace(".", ",", number_format($price, 2, ",", ""));
    }

    /**
     * Convert a specified number of byes to a readable format
     * @param integer $bytes
     * @return string
     */
    public static function ToSizeFormatFromBytes($bytes):string {
        $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes , $base) , count($si_prefix) - 1);

        return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
    }

    /**
     * Get the percentage between two values
     * @param integer $total
     * @param integer $number
     * @return float
     */
    public static function ToPercentage($total, $number):float {
        if ( $total > 0 ) {
            return round($number / ($total / 100),2);
        } else {
            return 0;
        }
    }

    /**
     * Convert seconds to timestamp
     * @param integer $seconds
     * @return string
     */
    public static function SecondsToTimeserial(int $seconds):string {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);

        return StringHelpers::Pad($hours).':'.StringHelpers::Pad($mins).':'.StringHelpers::Pad($secs);
    }
}