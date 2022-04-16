<?php
namespace LightWine\Modules\Files\Services;

use LightWine\Modules\Files\Models\ImageFileReturnModel;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Files\Interfaces\IImageFileService;

class ImageFileService implements IImageFileService
{
    private MysqlConnectionService $databaseConnection;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
    }

    /**
     * Gets a image from the database or cache using the specified filename
     * @param string $filename The filename of the image you want to get
     * @return ImageFileReturnModel Model containing all the details of the image
     */
    public function GetImageByName(string $filename): ImageFileReturnModel {
        $returnModel = new ImageFileReturnModel;

        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("filename", $filename);

        $returnModel->CacheKey = sha1($filename);
        $returnModel->CacheFile = $_SERVER["DOCUMENT_ROOT"]."/cache/image_cache/".$returnModel->CacheKey.".cache";

        $cacheFileExists = file_exists($returnModel->CacheFile);

        $returnModel->SaveToCache = $this->databaseConnection->DatasetFirstRow("cache", "boolean");

        if($cacheFileExists){
            // Check if the file is already saved in the cache
            $returnModel->FileData = file_get_contents($returnModel->CacheFile);
        }else{
            $dataset = $this->databaseConnection->getDataset("SELECT * FROM `site_files` WHERE filename = ?filename LIMIT 1;");
            
            // The file could not be found
            if($this->databaseConnection->rowCount <= 0 && !$cacheFileExists) return $returnModel;

            $userId = $this->databaseConnection->DatasetFirstRow("user_id", "integer");

            if($userId !== 0 && $userId !== $_SESSION["UserId"]){
                // Check if the current user has permission to view the image
                $returnModel->Permission = false;

                return $returnModel;
            }else{
                foreach($dataset as $row){
                    $returnModel->FileData = $row["content"];

                    file_put_contents($returnModel->CacheFile, $returnModel->FileData); // Write the file to the cache
                }
            }
        }

        $returnModel->FileSize = strlen($returnModel->FileData);
        $returnModel->ContentType = $this->databaseConnection->DatasetFirstRow("content_type");
        $returnModel->Found = true;

        return $returnModel;
    }
}