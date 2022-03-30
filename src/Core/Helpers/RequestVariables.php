<?php
namespace LightWine\Core\Helpers;

class RequestVariables
{
    /**
     * Gets a specified request variable
     * @param string $key The name of the variable you want to get
     * @param string $default The default value to return of the specified variable can't be found
     * @return mixed The value of the variable
     */
    public static function Get(string $key, string $default = ""){
        $postVar = $_POST[$key];
        $getVar = $_GET[$key];

        if(!StringHelpers::IsNullOrWhiteSpace($postVar)){
            return $postVar;
        }else{
            if(!StringHelpers::IsNullOrWhiteSpace($getVar)){
                return $getVar;
            }else{
                return $default;
            }
        }
    }

    /**
     * Set a request variable for post or get
     * @param string $key The name of the variable
     * @param string $value The value of the variable
     * @param string $type The type of the variable (post, get)
     */
    public static function Set(string $key, string $value, string $type){
        if($type == INPUT_POST){
            $_POST[$key] = $value;
        }else if($type === INPUT_GET){
            $_GET[$key] = $value;
        }
    }

    /**
     * Convert the request variables to array
     */
    public static function ToArray(): array {
        $returnArray = [];

        Helpers::PushArrayIntoArray(filter_input_array(INPUT_GET), $returnArray);
        Helpers::PushArrayIntoArray(filter_input_array(INPUT_POST), $returnArray);

        return $returnArray;
    }

    /**
     * Convert the request variables to a string
     */
    public static function ToString(): string {
        return implode(self::ToArray(), ",");
    }
}