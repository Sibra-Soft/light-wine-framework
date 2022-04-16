<?php
namespace LightWine\Modules\Language\Services;

use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Cache\Services\CacheService;
use LightWine\Modules\Language\Interfaces\ILanguageService;

class LanguageService implements ILanguageService
{
    private ConfigurationManagerService $config;
    private MysqlConnectionService $databaseConnection;
    private CacheService $cacheService;

    private $currentLangauge = "";

    public function __construct(){
        $this->config = new ConfigurationManagerService();
        $this->databaseConnection = new MysqlConnectionService();
        $this->cacheService = new CacheService();
    }

    /**
     * Function writes the translations to the current session cache
     */
    public function WriteOrRefreshCacheTranslations(){
        $cacheResponse = $this->cacheService->CheckCache("translations");

        // Get the specified langauge
        $currentLangauge = $this->config->GetAppSetting("default_langauge", 3);
        $_SESSION["CurrentLangauge"] = $currentLangauge;

        if(!$cacheResponse){
            $translations = [];

            // Get the translations from the database
            $this->databaseConnection->ClearParameters();
            $this->databaseConnection->AddParameter("currentLangauge", $currentLangauge);

            $dataset = $this->databaseConnection->getDataset("
                SELECT
                    translations.anchor,
                    translations.translation,
                    CONCAT('lang_', languages.id) AS language_code
                FROM site_languages AS languages
                INNER JOIN site_translations AS translations ON translations.language_code = languages.id
                WHERE languages.id = ?currentLangauge
            ");

            // Load the translations into a translation object
            foreach($dataset as $row){
                $translations[$row["language_code"]][$row["anchor"]] = $row["translation"];
            }

            // Write the information
            $this->cacheService->AddArrayToCache("translations", $translations);
        }
    }

    /**
     * Gets the translation from the database
     * @param string $anchor
     * @return string
     */
    public function GetTranslation(string $anchor): string {
        if(!$this->cacheService->CheckCache("translations")){
            $this->cacheService->GetCache();
        }

        $this->currentLangauge = $_SESSION["CurrentLangauge"];
        $langaugeArray = $this->cacheService->GetArrayFromCacheBasedOnName("translations")["lang_".$this->currentLangauge];
        $translation = "";

        if(array_key_exists($anchor, $langaugeArray)){
            $translation = $langaugeArray[$anchor];
        }else{
            $translation = "Unknown:".$anchor;

            // Check if missing translation must be created
            if($this->config->GetAppSetting("automatic_create_missing_translations", false)){
                $this->databaseConnection->ClearParameters();
                $this->databaseConnection->AddParameter("language_code", $this->currentLangauge);
                $this->databaseConnection->AddParameter("anchor", $anchor);
                $this->databaseConnection->AddParameter("translation", $translation);
                $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_translations", null, true);
            }
        }

        return $translation;
    }
}