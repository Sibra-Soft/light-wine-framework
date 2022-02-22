<?php
namespace LightWine\Modules\Logger\Services;

use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\Helpers\DeviceHelpers;
use LightWine\Core\Helpers\Helpers;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;

class LoggerService
{
    private MysqlConnectionService $databaseConnection;
    private ConfigurationManagerService $config;

    private function __constructor(){
        $this->config = new ConfigurationManagerService();
        $this->databaseConnection = new MysqlConnectionService();
    }

    /**
     * Call this function to log the current website visitor
     */
    public function LogSiteVisitor(){
        $logFolder = $_SERVER["DOCUMENT_ROOT"]."/logs/traffic/";

        $logEntry = Helpers::Now()->format("Y-m-d h:i:s").";";
        $logEntry .= $_SERVER['REQUEST_METHOD'].";";
        $logEntry .= $_SERVER['CONTENT_LENGTH'].";";
        $logEntry .= http_response_code().";";
        $logEntry .= DeviceHelpers::IP().";";
        $logEntry .= DeviceHelpers::OS().";";
        $logEntry .= DeviceHelpers::Browser().";";
        $logEntry .= DeviceHelpers::DeviceType().";";
        $logEntry .= $_SERVER["REQUEST_URI"].";";
        $logEntry .= DeviceHelpers::UserAgent();
        
        file_put_contents($logFolder."/test.log", $logEntry . "\n", FILE_APPEND);
    }

    /**
     * Create the log entry in the log table
     * @param string $logLevel The level of the log entry
     * @param string $message The message of the log entry
     * @param string $category The category of the log entry
     */
    private function Log(string $logLevel, string $message, string $category, int $id){
        $logFolder = $_SERVER["DOCUMENT_ROOT"]."/logs/traffic/";

        $logEntry = "";
        $logEntry .= $logLevel;
        $logEntry .= $message;
        $logEntry .= $category;
        $logEntry .= $id;

        file_put_contents($logFolder."/debug.log"."\n", FILE_APPEND);
    }

    public function LogDebug(string $message, string $category = "general", $id = 0){
        $this->Log("debug", $message, $category, $id);
    }

    public function LogError(string $message, string $category = "general", $id = 0){
        $this->Log("error", $message, $category, $id);
    }

    public function LogWarning(string $message, string $category = "general", $id = 0){
        $this->Log("warning", $message, $category, $id);
    }

    public function LogInformation(string $message, string $category = "general", $id = 0){
        $this->Log("information", $message, $category, $id);
    }
}