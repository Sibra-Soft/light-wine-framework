<?php
namespace LightWine\Modules\Cache\Services;

use LightWine\Core\Helpers\Helpers;
use LightWine\Modules\Cache\Models\CacheServiceReturnModel;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;

class CacheService {
    protected $CacheFolder;

    private ConfigurationManagerService $config;

    public function __construct() {
        $this->config = new ConfigurationManagerService();

        $this->CacheFolder = $_SERVER["DOCUMENT_ROOT"].$this->config->GetAppSetting("cache_folder");

        Helpers::CreateFolderIfNotExists($this->CacheFolder);

        // Check if the cache file exisits, and create it if it does not exist
        if(file_exists($this->CacheFolder."/app_cache.cache")){
            $this->GetCache();
        }else{
            $this->SaveCache();
        }
    }

    /**
     * This function gets or generates a cache file from the specified content
     * @param mixed $content The content that must be cached, can be: image, html, php, etc.
     * @param string $filename The filename of the cache file (must be unique)
     * @param int $period The period in hours the cache must be preserved
     * @return CacheServiceReturnModel
     */
    public function GetOrCacheFile($content, string $filename, int $period = 8): CacheServiceReturnModel {
        $returnModel = new CacheServiceReturnModel;

        $cacheFileSha = sha1($filename);
        $cacheFile = $this->CacheFolder."/".$cacheFileSha.".cache";

        if(file_exists($cacheFile)){
            $returnModel->Data = $content;
            $returnModel->DateCached = new \DateTime(date("Y-m-d H:i:s", filemtime($cacheFile)));
            $returnModel->UniqueId = $cacheFileSha;
            $returnModel->IsFromCache = true;
        }

        return $returnModel;
    }

    /**
     * This function saves the cache array to a file
     */
    private function SaveCache(){
        file_put_contents($this->CacheFolder."/app_cache.cache",  json_encode($_ENV["Cache"]));
    }

    /**
     * This function gets the cache array form the file
     */
    private function GetCache(){
        if(!$_ENV["Cache"]["Cached"]){
            $_ENV["Cache"] = json_decode(file_get_contents($this->CacheFolder."/app_cache.cache"), true);
            $_ENV["Cache"]["Cached"] = true;
        }
    }

    /**
     * This function checks if content has already been cached based on the specified name
     * @param string $name The name of the cache entry
     * @return bool
     */
    public function CheckCache(string $name){
        return key_exists($name, $_ENV["Cache"]);
    }

    /**
     * This function adds a specified array to the cache
     * @param string $name The name the data must be saved under to the cache
     * @param array $array The array of data that must be added to the cache
     */
    public function AddArrayToCache(string $name, array $array){
        $_ENV["Cache"][$name] = $array;
        $this->SaveCache();
    }

    /**
     * This function gets a value from the cache based on the specified name
     * @param string $name The name of the data to get from the cache
     * @return array|string
     */
    public function GetArrayFromCacheBasedOnName(string $name){
        return $_ENV["Cache"][$name];
    }

    /**
     * This function clears the memcache
     */
    public function ClearCache(){
        $_ENV["Cache"] = [];
        unlink($this->CacheFolder."/app_cache.cache");
        $this->SaveCache();
    }

    /**
     * This function clears the memcache and file cache on the server
     */
    public function ClearAllCache(){
        $files = glob($_SERVER["DOCUMENT_ROOT"].'/cache/*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }

        $this->ClearCache();
    }
}
?>