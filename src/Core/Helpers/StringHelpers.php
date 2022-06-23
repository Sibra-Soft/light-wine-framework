<?php
namespace LightWine\Core\Helpers;

use \DateTime;

class StringHelpers
{
    /**
     * Strips all text after a specified string
     * @param string $subject The string with text to remove
     * @param string $removeAfter The string everything must be deleted after
     * @return string The string with the removed text
     */
    public static function StripAfterString(string $subject, string $removeAfter){
        $pos = strpos($subject, $removeAfter);

        if ($pos !== false){
            return substr($subject, 0, $pos);
        }else{
            return $subject;
        }
    }

    /**
     * Checks if the specified date is valid
     * @param string $date The date you want to check
     * @return bool Returns true if the date is valid, false if the date is not valid
     */
    public static function IsValidDate(string $date): bool {
        $format = "Y-m-d";
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Add leading zeros to a specified integer
     * @param integer $integer The integer you want to add zero's to
     * @param integer $numberOfZeros The number of zero's you want to add
     * @return string String containg the integer with added zero's
     */
    public static function Pad(int $integer, int $numberOfZeros = 2):string {
        return sprintf('%0'.$numberOfZeros.'d', $integer);
    }

    /**
     * This function can split a string and return the value at a posistion
     * @param string $value The string you want to split using a delimiter
     * @param string $delimiter The delimiter that must be used
     * @param integer $position The position of tht string you want to return
     * @return string The string that can be found at the specified position using the specified delimiter
     */
    public static function SplitString(string $value, string $delimiter, int $position): string{
        $splitString = explode($delimiter, $value);

        if(StringHelpers::IsNullOrWhiteSpace($splitString[$position])){
            return "";
        }else{
            return $splitString[$position];
        }
    }

    /**
     * Gets the string between two other strings, for example <tag>test</tag> the output would be test
     * @param string $string The string you want to use
     * @param string $start The beginning char
     * @param string $end The ending char
     * @return string The string that can be found between the beginning char and ending char
     */
    public static function StringBetween(string $string, string $start, string $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    /**
     * This function gets a string between the start integer and end integer
     * @param string $string The string you want to use
     * @param integer $start The start position
     * @param integer $end The ending position
     * @return string The start that can be found between the specified start position and ending position
     */
    public static function Mid($string, $start, $end){
        $length = $start - $end;

        return substr($string, $start, abs($length));
    }

    /**
     * Join array parts with a specified delimiter
     * @param mixed $subject A array containg the values you want to join
     * @param string $delimiter The delimiter that must be used when joining
     * @return string The string containing the joined array
     */
    public static function JoinString($subject, string $delimiter):string {
        if(StringHelpers::IsNullOrWhiteSpace($subject)){
            return "";
        }else{
            if(is_array($subject)){
                if(count($subject) > 1){
                    return str_replace(", ", "", implode($delimiter, $subject));
                }else{
                    return implode($delimiter, $subject);
                }
            }else{
                return $subject;
            }
        }
    }

    /**
     * Function to check if the given string starts with the specified value
     * @param string $haystack The subject string
     * @param string $needle The string to search in the subject
     * @return boolean True if the specifid string begins with the specified begingging
     */
    public static function StartsWith(string $haystack, string $needle):bool {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * Function to check if the given string ends with the specified value
     * @param string $haystack The subject string
     * @param string $needle The string to search in the subject
     * @return boolean True if the specified string end with the specifeid ending
     */
    public static function EndsWith(string $haystack, string $needle):bool {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Function to check if a given string contains a specified value
     * @param string $haystack The string you want to check
     * @param string $needle The string you want to check if exists in the specified string
     * @return boolean True if the needle can be found in the haystack
     */
    public static function Contains(string $haystack, string $needle): bool {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * Function for checking if the specified value is a empty string
     * @param mixed $value The value you want to check
     * @return boolean True if the valud is null or whitespace
     */
    public static function IsNullOrWhiteSpace($value):bool {
        if($value == "" or $value == null){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Truncate a specfied string to a number of charters and add dots if longer
     * @param string $inputString The string you want to ellipis
     * @param integer $maxLength The position you want to start adding the dots
     * @return string Changed string containg dots at the specified position
     */
    public static function TruncateEllipsis(string $inputString, int $maxLength):string {
        if(strlen($inputString) > $maxLength){
            return substr($inputString, 0, $maxLength).'...';
        }else{
            return $inputString;
        }
    }
}