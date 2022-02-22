<?php
namespace LightWine\Core\Helpers;

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

    public static function IsValidDate($date): bool {
        $format = "Y-m-d";
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Add leading zeros to a specified integer
     * @param integer $integer
     * @param integer $numberOfZeros
     * @return string
     */
    public static function Pad(int $integer, int $numberOfZeros = 2):string {
        return sprintf('%0'.$numberOfZeros.'d', $integer);
    }

    /**
     * This function can split a string and return the value at a posistion
     * @param string $value
     * @param string $delimiter
     * @param integer $posistion
     * @return string
     */
    public static function SplitString(string $value, string $delimiter, int $posistion): string{
        $splitString = explode($delimiter, $value);

        if(StringHelpers::IsNullOrWhiteSpace($splitString[$posistion])){
            return "";
        }else{
            return $splitString[$posistion];
        }
    }

    /**
     * Gets the string between two other strings, for example <tag>test</tag> the output would be test
     * @param string $string
     * @param string $start
     * @param string $end
     * @return string
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
     * @param string $string
     * @param integer $start
     * @param integer $end
     * @return string
     */
    public static function Mid($string, $start, $end){
        $length = $start - $end;

        return substr($string, $start, abs($length));
    }

    /**
     * This function gets string between two string
     * @param string $string
     * @param integer $start
     * @param integer $end
     * @return string
     */
    public static function Between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);

        if ($ini == 0) return '';

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    /**
     * Join array parts with a specified delimiter
     * @param mixed $subject
     * @param string $delimiter
     * @return string
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
     * @return boolean
     */
    public static function StartsWith(string $haystack, string $needle):bool {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * Function to check if the given string ends with the specified value
     * @param string $haystack The subject string
     * @param string $needle The string to search in the subject
     * @return boolean
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
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function Contains($haystack, $needle):bool {
        if (strpos($haystack, $needle) !== false) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * Function for checking if the specified value is a empty string
     * @param mixed $value The value you want to check
     * @return boolean
     */
    public static function IsNullOrWhiteSpace($value):bool {
        if($value == "" or $value == null){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Escape a string from special chars
     * @param string $value
     * @return string
     */
    public static function Escape($value){
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $value);
    }

    /**
     * Truncate a specfied string to a number of charters and add dots if longer
     * @param string $inputString
     * @param integer $maxLength
     * @return string
     */
    public static function TruncateEllipsis(string $inputString, int $maxLength):string {
        if(strlen($inputString) > $maxLength){
            return substr($inputString, 0, $maxLength).'...';
        }else{
            return $inputString;
        }
    }
}