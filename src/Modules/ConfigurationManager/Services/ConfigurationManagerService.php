<?php
namespace LightWine\Modules\ConfigurationManager\Services;

use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\StringHelpers;

use \Exception;

class ConfigurationManagerService
{
    private array $Settings = [];

    public function __construct(){
        $this->LoadSettingsFile();
    }

    /**
     * Loads the specified configuration file to the settings object
     */
    private function LoadSettingsFile(){
        $configFile = $GLOBALS["ConfigFile"];

        if(!file_exists($configFile)){
            throw new Exception("The specified application configuration file could not be found");
        }

        $this->Settings = json_decode(Helpers::GetFileContent($configFile), true);
        $GLOBALS["Settings"] = $this->Settings;
    }

    /**
     * Gets a specified application setting from the config
     * @param string $name The name of the setting
     * @return string The value of the requested setting
     */
    public function GetAppSetting(string $name, string $default = ""){
        if(array_key_exists($name, $this->Settings)){
            return $this->Settings[$name];
        }else{
            return $default;
        }
    }

    /**
     * Get a connectionstring from the website/app configuration file
     * @param string $connectionStringName
     * @param string $keyValue
     * @return string
     */
    public function ConnectionStrings(string $connectionStringName, string $keyValue = null){
        $connectionProperties = [];

        if(StringHelpers::IsNullOrWhiteSpace($keyValue)){
            return $this->Settings["connections"][$connectionStringName];
        }else{
            $properties = explode(";", $this->Settings["connections"][$connectionStringName]);

            foreach($properties as $property){
                $value = explode("=", $property);
                $connectionProperties[$value[0]] = $value[1];
            }

            return $connectionProperties[$keyValue];
        }
    }
}
?>