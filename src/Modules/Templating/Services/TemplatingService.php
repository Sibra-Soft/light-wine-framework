<?php
namespace LightWine\Modules\Templating\Services;

use LightWine\Core\Helpers\DeviceHelpers;
use LightWine\Core\Helpers\StringHelpers;

use LightWine\Modules\Templating\Services\BindingsService;
use LightWine\Modules\Templating\Models\BindingReturnModel;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templates\Models\TemplateModel;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Language\Services\LanguageService;
use LightWine\Modules\Templating\Interfaces\ITemplatingService;

use LightWine\Components\Webform\Webform;
use LightWine\Components\DeviceVerification\DeviceVerification;
use LightWine\Components\Dataview\Dataview;
use LightWine\Components\Account\Account;

use \DateTime;

class TemplatingService implements ITemplatingService
{
    private TemplatesService $templateService;
    private MysqlConnectionService $databaseConnection;
    private BindingsService $bindingService;
    private TemplatingEngineService $compilerService;
    private LanguageService $languageService;

    private array $Store = [];

    public function __construct(){
        $this->templateService = new TemplatesService();
        $this->databaseConnection = new MysqlConnectionService();
        $this->bindingService = new BindingsService($this);
        $this->compilerService = new TemplatingEngineService();
        $this->languageService = new LanguageService();
    }

    /**
     * This function gets all the variables from the template
     * @return array Array of found variables in the specified content
     */
    private function GetVariablesFromContent($content){
        preg_match_all('/@.*\((\'.*?\')\)/', $content, $matches);

        return $matches;
    }

    /**
     * This function adds a variable with value to the current variable store
     * @param string $name The name of the variable
     * @param string $value The value of the variable that
     */
    public function AddReplaceVariable(string $name, $value){
        $this->Store[$name] = $value;
    }

    /**
     * This function replaces a variable in the template with the specified content
     * @param string $variable The complete variable that must be replaced
     * @param string $content The content the variable must be replaced with
     */
    private function ReplaceVariable($variable, $replaceContent, $content){
        return str_replace($variable, (string)$replaceContent, $content);
    }

    /**
     * This function adds all the templating variables to the current variable store
     */
    public function AddTemplatingVariablesToStore(){
        $date = new DateTime("now");

        // Add static variables
        $this->AddReplaceVariable("CurrentYear", $date->format("Y"));
        $this->AddReplaceVariable("CurrentTime", $date->format("hh:mm"));
        $this->AddReplaceVariable("CurrentMonth", $date->format( "m"));
        $this->AddReplaceVariable("CurrentMonthNumber", $date->format("F"));
        $this->AddReplaceVariable("RemoteAddress", $_SERVER['REMOTE_ADDR']);
        $this->AddReplaceVariable("RunAtServer", $_SERVER["PHP_SELF"]);
        $this->AddReplaceVariable("CurrentUrl", substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/'))."/");
        $this->AddReplaceVariable("Browser", strtolower(DeviceHelpers::Browser()));

        // Add get variables
        foreach($_GET as $key => $value){
            $this->AddReplaceVariable("get_".strtolower($key), $value);
        }

        // Add post variables
        foreach($_POST as $key => $value){
            $this->AddReplaceVariable("post_".strtolower($key), $value);
        }

        // Add session variables
        foreach($_SESSION as $key => $value){
            $this->AddReplaceVariable("session_".strtolower($key), $value);
        }

        // Add user settings
        foreach($_SESSION["UserSettings"] as $key => $value){
            $this->AddReplaceVariable("settings_".strtolower($key), $value);
        }
    }

    public function AddBindingValuesToStore(int $templateId){
        $bindingResultModel = new BindingReturnModel;

        $bindingResultModel = $this->bindingService->GetBindingBasedOnTemplateId($templateId);

        if($bindingResultModel->BindingCount > 0){
            foreach($bindingResultModel->BindingResult as $key => $value){
                $this->AddReplaceVariable($key, $value);
            }
        }
    }

    /**
     * This function handels all content replacements
     * @param string $content The current template content
     * @return string The template with the replaced content
     */
    public function ReplaceContent(string $content): string  {
        foreach($this->GetVariablesFromContent($content)[0] as $variable){
            $variableName = StringHelpers::StringBetween($variable, "@", "(");
            $variableValue = str_replace("'", "", StringHelpers::StringBetween($variable, "(", ")"));

            switch($variableName){
                // This will render and include a php script
                case "include-stream":
                    ob_start();
                    include($_SERVER["DOCUMENT_ROOT"]."/app-code/".$variableValue);
                    $ob_stream = ob_get_contents();
                    ob_end_clean();

                    $content = $this->ReplaceVariable($variable, $ob_stream, $content);
                    break;

                // This will include a specified template
                case "include":
                    $templateModel = new TemplateModel;

                    // Add querystring parameters if found
                    parse_str(parse_url($variableValue, PHP_URL_QUERY), $queryString);
                    foreach($queryString as $key => $value){$_GET[$key] = $value;}

                    $parentFolder = "*";
                    $templateName = strtok($variableValue, "?");
                    if(StringHelpers::Contains($variableValue, ".")){
                        $parentFolder = StringHelpers::SplitString($variableValue, ".", 0);
                        $templateName = StringHelpers::SplitString($variableValue, ".", 1);
                    }

                    $templateModel = $this->templateService->GetTemplateByName($templateName, 'html', $parentFolder);

                    $this->AddBindingValuesToStore($templateModel->Id);
                    $this->AddTemplatingVariablesToStore();

                    $includeContent = $this->ReplaceContent($templateModel->Content);
                    $includeContent = $this->ReplaceVariablesFromStore($includeContent);
                    $includeContent = $this->RunCompilers($includeContent);
                    $includeContent = $this->ReplaceExtensions($includeContent);

                    $content = $this->ReplaceVariable($variable, $includeContent, $content);
                    break;

                // This will add a translation to the template
                case "translate":
                    $this->languageService->WriteOrRefreshCacheTranslations();

                    $content = $this->ReplaceVariable($variable, $this->languageService->GetTranslation($variableValue), $content);
                    break;
            }
        }

        // Static content replacements
        $content = str_replace('@csrf', '<input type="hidden" name="csrf_token" value="'.$_SESSION["CsrfToken"].'" />', $content);
        $content = str_replace("jtextarea", "textarea", $content);

        return $content;
    }

    /**
     * This function renders all the controls on the page
     * @param string $content The current template content
     * @return string
     */
    public function ReplaceAndRenderControls(string $content): string  {
        // Get all the controls from the current template
        preg_match_all('/\@control\((.*?)\)/', $content, $matches);

        $index = 0;
        foreach($matches[0] as $variable){
            $controlValue = str_replace("'", "", StringHelpers::StringBetween($variable, "(", ")"));

            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("controlName", $controlValue);
            $this->databaseConnection->GetDataset("
                SELECT
                    component.id,
	                version.content AS settings,
	                JSON_UNQUOTE(JSON_EXTRACT(version.content, '$.Mode')) AS `mode`,
	                JSON_UNQUOTE(JSON_EXTRACT(version.content, '$.Type')) AS `type`
                FROM `site_templates` AS component

                INNER JOIN site_template_versioning AS version ON version.version = component.template_version_dev AND version.template_id = component.id

                WHERE component.`name` = ?controlName
	                AND component.type = 'component'
                LIMIT 1;
            ");

            if($this->databaseConnection->rowCount > 0){
                $controlId = $this->databaseConnection->DatasetFirstRow("id");

                // Run the control code
                switch($this->databaseConnection->DatasetFirstRow("type")){
                    case "device-verification": $myControl = new DeviceVerification($controlId); $controlContent = $myControl->Init(); break;
                    case "dataview": $myControl = new Dataview($controlId); $controlContent = $myControl->Init(); break;
                    case "account": $myControl = new Account($controlId); $controlContent = $myControl->Init(); break;
                    case "webform": $myControl = new Webform($controlId); $controlContent = $myControl->Init(); break;
                }
            }

            // Replace the variable with the control content
            $content = $this->ReplaceVariable($variable, $controlContent, $content);
            $index++;
        }

        return $content;
    }

    /**
     * This function replaces all the extension options in the template
     * @param string $content The content of the template with the extensions to be replaced
     * @return string The HTML content of the template with the extensions replaced
     */
    public function ReplaceExtensions(string $content) : string {
        preg_match_all('/\[@(.*?)\]\]/', $content, $matches);

        $tempContent = $content;
        foreach($matches[0] as $variable){
            $commandName = StringHelpers::StringBetween($variable, "[", "[");
            $commandValue = StringHelpers::StringBetween($variable, $commandName."[", "|");
            $commandParameter = StringHelpers::StringBetween($variable, "|", "]]");

            switch (strtolower($commandName)) {
                case "@decimal":
                    $tempContent = $this->ReplaceVariable($variable, str_replace(".", ",", $commandValue), $tempContent);
                    break;

                case "@format":
                    $date = strtotime($commandValue);
                    $tempContent = $this->ReplaceVariable($variable, strftime($commandParameter, $date), $tempContent);
                    break;

                case "@split":
                    $parameters = explode(",", $commandParameter);

                    if(count($parameters) == 1){
                        $splitValue = explode(",", $commandValue);
                    }else{
                        $splitValue = explode($parameters[1], $commandValue);
                    }

                    $tempContent = $this->ReplaceVariable($variable, $splitValue[$parameters[0]], $tempContent);
                    break;

                case "@truncate":
                    $truncatevalue = $commandValue;
                    $tempContent = $this->ReplaceVariable($variable,  StringHelpers::TruncateEllipsis($truncatevalue, (int)$commandParameter), $tempContent);
                    break;

                case "@minutestotime":
                    $tempContent = $this->ReplaceVariable($variable, date($commandParameter, mktime(0, (int)$commandValue)), $tempContent);
                    break;

                case "@secondstotime":
                    $tempContent = $this->ReplaceVariable($variable, gmdate($commandParameter, (int)$commandValue), $tempContent);
                    break;

                case "@lower":
                    $tempContent = $this->ReplaceVariable($variable, strtolower(trim($commandValue)), $tempContent);
                    break;

                case "@ucfirst":
                    $tempContent = $this->ReplaceVariable($variable, ucfirst(trim($commandValue)), $tempContent);
                    break;

                case "@money":
                    $tempContent = $this->ReplaceVariable($variable, "&euro; ".(float)$commandValue, $tempContent);
                    break;

                case "@urldecode":
                    $tempContent = $this->ReplaceVariable($variable, urldecode($commandValue), $tempContent);
                    break;

                case "@cdate":
                    $tempContent = $this->ReplaceVariable($variable, strftime($commandParameter, strtotime('1899-12-30 + '.$commandValue.' days')), $tempContent);
                    break;

            }
        }

        return $tempContent;
    }

    /**
     * This function replaces all the variables in the template with the variables in the store
     */
    public function ReplaceVariablesFromStore($content){
        $tempContent = $content;

        foreach($this->Store as $name => $value){
            $tempContent = $this->ReplaceVariable('{{$'.$name.'}}', $value, $tempContent);
        }

        return $tempContent;
    }

    /**
     * This function only runs the template compilers
     * @param string $content The content you want to compile
     * @return string The compiled content
     */
    public function RunCompilers(string $content){
        return $this->compilerService->RunEngineCompilers($content, $this->Store);
    }

    /**
     * This function renders the template and runs all the replacements
     * @param int|TemplateModel $templateId The id of the template
     * @return TemplateModel The template model of the specified template
     */
    public function RenderTemplateAndDoAllReplacements($templateOrId): TemplateModel {
        $template = new TemplateModel;

        // Get the template model
        if($templateOrId instanceof TemplateModel){
            $template = $templateOrId;
        }else{
            $template = $this->templateService->GetTemplateById($templateOrId);
        }

        // Check the caching of the template
        $result = $template->Content;

        $this->AddBindingValuesToStore($template->Id);
        $this->AddTemplatingVariablesToStore();

        $result = $this->ReplaceAndRenderControls($result);
        $result = $this->ReplaceVariablesFromStore($result);

        // Render the template and run all compilers
        $result = $this->compilerService->RunEngineCompilers($result, $this->Store);
        $result = $this->ReplaceContent($result);

        $result = $this->ReplaceExtensions($result);

        $template->Content = $result;

        // Return the current template model
        return $template;
    }
}