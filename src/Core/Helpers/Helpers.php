<?php
namespace LightWine\Core\Helpers;

use \DateTime;

class Helpers {
    /**
     * Gets the absolute path of a specified file on the server
     * @param string $path The path you want to make absolute
     * @return string The new path inclusing the necessary path information
     */
    public static function MapPath(string $path): string {
        $path = str_replace("~", dirname(__FILE__, 3), $path);
        $path = str_replace("#", dirname(__FILE__, 4), $path);
		
		if(str_starts_with($path, "..")){
			$path = str_replace("..", $_SERVER["DOCUMENT_ROOT"], $path);
		}
		
        $path = str_replace("/src/src/", "/src/", $path);
        $path = str_replace("\src/src/", "/src/", $path);

        return $path;
    }

    /**
     * Generate a pincode with four decimals
     * @return int The generated pincode containing 4 digits
     */
    public static function GeneratePincode(): int {
        return mt_rand(1111, 9999);
    }

    /**
     * Push a specified array into a other array
     * @param array $targetArray The array you want to push into a other array
     * @param array $pushArray The array the specified array must be pushed into
     * @return array The array containing both
     */
    public static function PushArrayIntoArray(array $targetArray, array $pushArray){
        $array = $targetArray;

        foreach($pushArray as $optionKey => $optionValue){
            $array[$optionKey] = $optionValue;
        }

        return $array;
    }

    /**
     * Gets the first item of the array, if nothing or empty will return empty string
     * @param array $array The array of data
     * @return string The value of the first item in the array
     */
    public static function FirstOrDefault(array $array){
        if(count($array) == 0){
            return "";
        }else{
            return $array[0];
        }
    }

    /**
     * This function repairs specified encoded json
     * @param string $json The json content you want to repair
     * @return string The repaired json content
     */
    public static function RepairJson(string $json)
    {
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json);
    }

    /** This function gets the mime type of a specified file */
    public static function GetMimeType(string $file): string {
        $mtype = false;

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $file);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mtype = mime_content_type($file);
        }

        return $mtype;
    }

    /**
     * Get the current date as timestamp
     * @return DateTime
     */
    public static function Now(){
        return new DateTime("now");
    }

    /**
     * This function gets the page source code and returns it
     * @param string $url The url of the page you want to get the source
     * @return string
     */
    public static function GetPageContent(string $url) {
        $ch = curl_init();
        $timeout = 5;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * This function checks if a specified folder exists, if not it will be created
     * @param string $folderName
     */
    public static function CreateFolderIfNotExists(string $folderName){
        if (!file_exists($folderName)) {
            mkdir($folderName);
        }
    }

    /**
     * This function loads a file and returns the content of the file
     * @param string $filename
     * @return string
     */
    public static function GetFileContent($filename){
        $content = "";

        $filename = self::MapPath($filename);
        $filesize = filesize($filename);

        if($filesize > 0){
            $myfile = fopen($filename, "r") or die("Unable to open file: ".$filename);
            $content = fread($myfile, $filesize);
            fclose($myfile);
        }

        return iconv("UTF-8","ISO-8859-1//IGNORE", $content);
    }

    /**
     * This function downloads a external website file to the server
     * @param string $url
     * @param string $filename
     */
    public static function DownloadExternalFile($url, $filename){
        $ch = curl_init();
        $timeout = 5;

        // Download the file
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $data = curl_exec($ch);
        curl_close($ch);

        // Save the file to the server
        file_put_contents($filename, $data);
    }

    /**
     * This function creates a random integer value
     * @param integer $lowerBound The value to start from
     * @param integer $upperBound The value to end
     * @return integer
     */
    public static function RandomInteger($lowerBound, $upperBound){
        return rand($lowerBound, $upperBound);
    }

    /**
     * This function creates a guid
     * @return string
     */
    public static function NewGuid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }
}
?>