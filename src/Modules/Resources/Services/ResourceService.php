<?php
namespace LightWine\Modules\Resources\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\Helpers\Helpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Resources\Models\ResourcePackagesReturnModel;
use LightWine\Modules\Templating\Services\TemplatingService;
use LightWine\Modules\Templates\Services\TemplatesService;
use LightWine\Modules\Templates\Models\TemplateModel;
use LightWine\Modules\Cache\Services\CacheService;
use LightWine\Core\Helpers\HttpContextHelpers;
use LightWine\Modules\Resources\Interfaces\IResourceService;
use LightWine\Modules\Resources\Enums\ResourceTypeEnum;

class ResourceService implements IResourceService {
    private string $contentJS = "";
    private string $contentCSS = "";

    private MysqlConnectionService $databaseConnection;
    private ConfigurationManagerService $settings;
    private TemplatingService $templatingService;
    private TemplatesService $templateService;
    private CacheService $cacheService;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
        $this->settings = new ConfigurationManagerService();
        $this->templatingService = new TemplatingService();
        $this->templateService = new TemplatesService();
        $this->cacheService = new CacheService();
    }

    /**
     * Gets the specified package form the CMS based on the specified type
     * @param string $type The type of packages you want to get (CSS, JS)
     * @return ResourcePackagesReturnModel Model containg the scripts for styling and javascript
     */
    public function GetPackages(): ResourcePackagesReturnModel {
        $returnModel = new ResourcePackagesReturnModel;

        $this->databaseConnection->ClearParameters();

        $dataset = $this->databaseConnection->GetDataset("SELECT * FROM `site_packages` ORDER BY `order`");

        foreach($dataset as $row){
            if($row["type"] === "javascript"){
                $returnModel->JavascriptPackages .= '<script src="'.$row["link"].'" type="text/javascript"></script>';
            }else{
                $returnModel->CssPackages .= '<link rel="stylesheet" type="text/css" href="'.$row["link"].'" />';
            }
        }

        return $returnModel;
    }

    /**
     * This function generates the masterpage file for the website
     * @param string $type
     * @return string
     */
    private function GenerateMasterpageFile($type){
        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/cache/masterpage.js") and file_exists($_SERVER["DOCUMENT_ROOT"]."/cache/masterpage.css")){
            $this->contentCSS = Helpers::GetFileContent($_SERVER["DOCUMENT_ROOT"]."/cache/masterpage.css");
            $this->contentJS = Helpers::GetFileContent($_SERVER["DOCUMENT_ROOT"]."/cache/masterpage.js");
        }else{
            // Check if a external content template has been specified
            $currentEnvironment = $this->settings->GetAppSetting("Environment");
            
            // Get masterpage content from the database
            $dataset = $this->databaseConnection->GetDataset("
                SELECT
	                templates.id,
	                version.content,
	                templates.type
                FROM site_templates AS templates
                LEFT JOIN site_template_versioning AS version ON version.template_id = templates.id AND version.version = templates.template_version_$currentEnvironment
                WHERE policies LIKE '%1003%'
                ORDER BY templates.`order`
            ");

            foreach($dataset as $row){
                if($row["type"] === "javascript"){
                    $content = $this->templatingService->ReplaceContent($row["content"]);

                    $this->contentJS .= HttpContextHelpers::MinifyJavascript($content);
                }else{
                    $this->contentCSS .= $row["content"];
                }
            }
            
            if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/cache/masterpage.js")){
                file_put_contents($_SERVER["DOCUMENT_ROOT"]."/cache/masterpage.js", $this->contentJS);
            }
            if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/cache/masterpage.css")){
                $this->contentCSS = HttpContextHelpers::MinifyStylesheet($this->contentCSS);
                file_put_contents($_SERVER["DOCUMENT_ROOT"]."/cache/masterpage.css", $this->contentCSS);
            }
        }

        if($type == ResourceTypeEnum::JS){
            return $this->contentJS;
        }else{
            return $this->contentCSS;
        }
    }

    /**
     * This function gets the resources based on the specified filename
     * @param string $filename The filename of the resource
     * @param string $type The type of resource (javascript or stylesheet)
     * @param bool $singleFileRequest Tells if this is a request for a single file
     * @return string The content of the request resource(s)
     */
    public function GetResourcesBasedOnFilename(string $filename, string $type, bool $singleFileRequest){
        if($singleFileRequest){
            $filename = $this->templateService->GetTemplateByName($filename, $type)->Id;
        }

        //$currentCacheFolder = $this->settings->GetAppSetting("CacheFolder", "/cache/");
        $cacheFilename = $_SERVER["DOCUMENT_ROOT"]."/cache/".$filename;

        // Create the cache folder if it not exists
        Helpers::CreateFolderIfNotExists($_SERVER["DOCUMENT_ROOT"]."/cache/");

        // If we request a masterpage file, just get the content and return it.
        if($filename == "masterpage.js" or $filename == "masterpage.css"){
            return $this->GenerateMasterpageFile($type);
        }

        // Check if the file has been cached, if so just return the content
        if(!file_exists($cacheFilename)){
            // Get the content of the specified templates and generate the resource file
            $returnContent = "";

            $filename = str_replace(".css", "", $filename);
            $filename = str_replace(".js", "", $filename);
            $filename = str_replace("general_", "", $filename);

            $templates = explode("_", $filename);
            $templates = array_unique($templates);

            if(count($templates) <= 1 and StringHelpers::IsNullOrWhiteSpace($templates[0])){
                echo("There are no resources specified for this template");
            }else{
                foreach($templates as $templateId){
                    $returnContent .= $this->templateService->GetTemplateById((int)$templateId, $type)->ContentNotMinified;
                }

                file_put_contents($cacheFilename, $returnContent);
            }
        }else{
            $returnContent = Helpers::GetFileContent($cacheFilename);
        }

        if($type == ResourceTypeEnum::CSS){
            return HttpContextHelpers::MinifyStylesheet($returnContent);
        }else{
            $returnContent = $this->templatingService->ReplaceContent($returnContent);
            return HttpContextHelpers::MinifyJavascript($returnContent);
        }
    }

    /**
     * Generates the resource URL based on the selected Javascript and Styling templates
     * @param string $type The type of URL to generate (scripts, styling)
     * @return string The generated resource URLs
     */
    public function GenerateResourceURL(string $type, TemplateModel $template): string {
        if($type === "scripts"){
            $returnTemplate = "";
            $ids = str_replace(",", "_", $template->Settings->ScriptResources);

            $returnTemplate .= '<script src="/res/javascript/masterpage.js" type="text/javascript"></script>';
            $returnTemplate .= '<script src="/res/javascript/general_'.$ids.'.js" type="text/javascript"></script>';

            return $returnTemplate;
        }else{
            $returnTemplate = "";
            $ids = str_replace(",", "_", $template->Settings->StylingResources);

            $returnTemplate .= '<link rel="stylesheet" type="text/css" href="/res/css/masterpage.css">';
            $returnTemplate .= '<link rel="stylesheet" type="text/css" href="/res/css/general_'.$ids.'.css">';

            return $returnTemplate;
        }
    }
}
?>