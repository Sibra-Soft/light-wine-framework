<?php
namespace LightWine;

use LightWine\Core\Services\ServerService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\HttpResponse;
use LightWine\Modules\Tracing\Services\TracingService;

use \Exception;
use \TypeError;
use \Error;
use \DateInterval;
use LightWine\Core\Route;

class Bootloader {
	private function Autoloader(){
        spl_autoload_register(function($class){
            $includeFile = __DIR__.str_replace('\\', '/', str_replace("LightWine", "", $class)).'.php';
            require_once ($includeFile);
        });
    }

    /**
     * Runs before request handeling
     */
    private function BeforeRequest(){
        // Add build-in framework routes
        Route::WebMethod("/template.dll", "TemplateServiceProvider", []);
        Route::WebMethod("/scheduler.dll", "SchedulerServiceProvider", []);
        Route::WebMethod("/imdb.dll", "ImageServiceProvider", []);
        Route::WebMethod("/json.dll", "JsonServiceProvider", []);
        Route::WebMethod("/partial.dll", "PartialServiceProvider", []);
        Route::WebMethod("/module.dll", "ModuleServiceProvider", []);
        Route::WebMethod("/component.dll", "ComponentServiceProvider", []);
        Route::WebMethod("/resources.dll", "ResourceServiceProvider", []);
        Route::WebMethod("/images/{filename}", "ImageServiceProvider", []);
        Route::WebMethod("/res/{type}/{filename}", "ResourceServiceProvider", []);

        // Add variables
        if(!array_key_exists("CsrfToken", $_SESSION)) $_SESSION["CsrfToken"]  = uniqid(time());
        if(!array_key_exists("ClientToken", $_SESSION)) $_SESSION["ClientToken"] = hash("sha1", time());
        if(!array_key_exists("SessionStartTime", $_SESSION)) $_SESSION["SessionStartTime"] = Helpers::Now()->format("h:m:s");
        if(!array_key_exists("SessionEndTime", $_SESSION)) $_SESSION["SessionEndTime"] = Helpers::Now()->add(new DateInterval('PT20M'))->format("h:m:s");

        $_SESSION["RequestTime"] = Helpers::Now()->format("h:m:s");
    }

    /**
     * Runs after request handeling
     */
    private function AfterRequest(){
        $traceService = new TracingService();
        $traceService->GenerateTracing();
    }

    /**
     * Used for error handling of the framework
     * @param mixed $errno The number of the current error
     * @param mixed $errstr The description of the error
     * @param mixed $errfile The file where the error occured
     * @param mixed $errline Linenumber of the file where the error occured
     */
    public function SetErrorHandler($errno, $errstr, $errfile, $errline){

    }

    /**
     * Used for exception handeling of the total framework
     * @param TypeError|Exception|Error $exception The thrown exception
     */
    public function SetExceptionHandler($exception){
        $view = Helpers::GetFileContent("~/src/Views/Exception.tpl");

        $message = StringHelpers::SplitString($exception->getMessage(), "#", 0);
        $specifiedSource = StringHelpers::SplitString($exception->getMessage(), "#", 1);

        if(StringHelpers::IsNullOrWhiteSpace($specifiedSource)) $specifiedSource = $exception->getTraceAsString();

        $view = str_replace("{{source_file_line}}", $exception->getLine(), $view);
        $view = str_replace("{{source_file}}", $exception->getFile(), $view);
        $view = str_replace("{{error_message}}", $message, $view);
        $view = str_replace("{{source}}", $specifiedSource, $view);

        HttpResponse::SetContentType("text/html");
        HttpResponse::SetData($view);
    }

    /**
     * Adds the specified configuration file to the project
     * @param string $file The path to the configuration file
     */
    public function AddConfigurationFile(string $file){
        $_SESSION["ConfigFile"] = $file;
    }

    /**
     * Main bootloader function, this function gets the request and starts the internal server
     */
    public function Run(){
        set_error_handler(array($this, 'SetErrorHandler'));
        set_exception_handler(array($this, "SetExceptionHandler"));

        $this->Autoloader();
        $this->BeforeRequest(); // Runs before the request handeling

        $server = new ServerService();
        $content = $server->Start();

        HttpResponse::SetContentType("text/html");
        HttpResponse::SetData($content);

        $this->AfterRequest(); // Runs after the request handeling
    }
}
?>