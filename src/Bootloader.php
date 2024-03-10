<?php
namespace LightWine;

use LightWine\Core\Route;
use LightWine\Core\Services\ServerService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\HttpResponse;
use LightWine\Modules\Tracing\Services\TracingService;

use \Exception;
use \TypeError;
use \Error;
use \DateInterval;

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
        // Add framework routes
        Route::Get("provider.scheduler", "/scheduler.dll", "@SchedulerServiceProvider", "controller");
        Route::Get("provider.images", "/images/{filename}", "@ImageServiceProvider", "controller");
        Route::Get("provider.resources", "/res/{type}/{filename}", "@ResourceServiceProvider", "controller");

        Route::Post("provider.component", "/component.dll", "@ComponentServiceProvider", "controller");
        Route::Post("provider.modals", "/modal.dll", "@ModalServiceProvider", "controller");
        Route::Post("provider.partial", "/partial.dll", "@PartialServiceProvider", "controller");
        Route::Post("provider.module", "/module.dll", "@ModuleServiceProvider", "controller");
        Route::Post("provider.templates", "/template.dll", "@TemplateServiceProvider", "controller");
        Route::Post("provider.imdb", "/imdb.dll", "@ImdbServiceProvider", "controller");
        Route::Post("provider.json", "/json.dll", "@JsonServiceProvider", "controller");

        // Add framework route parameters
        Route::RegisterRouteParameter("get@provider.images", "filename", "string", false, true);
        Route::RegisterRouteParameter("get@provider.resources", "type", "string", false, true);
        Route::RegisterRouteParameter("get@provider.resources", "filename", "string", false, true);

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
        $composerJson = json_decode(Helpers::GetFileContent("~/composer.json"), false);

        $message = StringHelpers::SplitString($exception->getMessage(), "#", 0);
        $specifiedSource = StringHelpers::SplitString($exception->getMessage(), "#", 1);

        if(StringHelpers::IsNullOrWhiteSpace($specifiedSource)) $specifiedSource = $exception->getTraceAsString();

        $view = str_replace("{{source_file_line}}", $exception->getLine(), $view);
        $view = str_replace("{{source_file}}", $exception->getFile(), $view);
        $view = str_replace("{{error_message}}", $message, $view);
        $view = str_replace("{{source}}", $specifiedSource, $view);
        $view = str_replace("{{framework_version}}", $composerJson->version);

        http_response_code(500);

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