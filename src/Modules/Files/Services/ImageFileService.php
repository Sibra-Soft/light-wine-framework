<?php
namespace LightWine\Modules\Files\Services;

use LightWine\Modules\Cache\Models\CacheReturnModel;
use LightWine\Modules\Cache\Services\CacheService;
use LightWine\Modules\Files\Models\ImageFileReturnModel;
use LightWine\Modules\Sam\Services\SamService;

class ImageFileService
{
    private FileService $fileService;
    private SamService $samService;
    private CacheService $cacheService;

    public function __construct(){
        $this->fileService = new FileService;
        $this->samService = new SamService;
        $this->cacheService = new CacheService;
    }

    /**
     * Gets a image from the dBase based on the specified filename
     * @param string $filename The name of the image you want to get
     * @throws \Exception When the current user is not allowed to download the image
     * @return ImageFileReturnModel A model containing all the details of the image
     */
    public function GetImage(string $filename): ImageFileReturnModel {
        $returnModel = new ImageFileReturnModel;
        $cacheModel = new CacheReturnModel();

        $cacheModel = $this->cacheService->CheckFileCache($filename, 8, "/image_cache/");

        if(!$cacheModel->Cached){
            $imageFile = $this->fileService->GetFileByName($filename);

            // Check if the image is allowed to be downloaded by the current user
            if($imageFile->Policies->USER_MUST_BE_LOGGED_IN){
                if($this->samService->CheckIfUserIsLoggedin() && $_SESSION["UserId"] !== $imageFile->UserId){
                    Throw new \Exception("You are not authorized to download the specified image");
                }
            }

            $returnModel->Name = $imageFile->Name;
            $returnModel->ItemId = $imageFile->LinkedItemId;
            $returnModel->File = $imageFile;

            // Get image size information
            $image = imagecreatefromstring($imageFile->Blob);
            $returnModel->Width = imagesx($image);
            $returnModel->Height = imagesy($image);

            if($returnModel->File->Policies->FILE_IS_CACHED){
                file_put_contents($cacheModel->CacheFile, serialize($returnModel));
            }
        }else{
            $returnModel = unserialize(file_get_contents($cacheModel->CacheFile));
        }

        return $returnModel;
    }
}