<?php
namespace LightWine\Modules\ConfigurationManager\Services;

use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\ConfigurationManager\Interfaces\IConfigurationManagerService;

use \Exception;

class ConfigurationManagerService implements IConfigurationManagerService
{
    private array $Settings = [];

    public function __construct(){
        $this->LoadSettingsFile();
    }

    /**
     * Loads the specified configuration file to the settings object
     */
    private function LoadSettingsFile(){
        $configFile = $_SESSION["ConfigFile"];

        if(!file_exists($configFile)){
            throw new Exception("The specified application configuration file could not be found");
        }

        $badchar = array(chr(239), chr(187), chr(191));
        $jsonString =  str_replace($badchar, '', Helpers::GetFileContent($configFile));

        $this->Settings = json_decode($jsonString, true);
        $GLOBALS["Settings"] = $this->Settings;
    }

    /**
     * Gets a specified application setting from the config
     * @param string $name The name of the setting
     * @return string The value of the requested setting
     */
    public function GetAppSetting(string $name, string $default = ""): string {
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
    public function ConnectionStrings(string $connectionStringName, string $keyValue): string {
        $stringArray = explode(";", $this->Settings["Connections"][$connectionStringName]);
        $returnValue = "";

        foreach($stringArray as $value){
            if(StringHelpers::Contains($value, $keyValue)){
                $returnValue = StringHelpers::SplitString($value, "=", 1);
            }
        }

        if(StringHelpers::IsNullOrWhiteSpace($returnValue)){
            throw new Exception("The specified connectionstring key could not be found: ".$keyValue);
        }

        return $returnValue;
    }
}
?>