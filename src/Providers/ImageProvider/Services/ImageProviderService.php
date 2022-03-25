<?php
namespace LightWine\Providers\ImageProvider\Services;

use LightWine\Core\Models\PageModel;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\HttpResponse;
use LightWine\Core\Helpers\RequestVariables;

class ImageProviderService
{
    private MysqlConnectionService $databaseConnection;

    public function __construct(){
        $this->databaseConnection = new MysqlConnectionService();
    }

    /**
     * Handles image requested for the image service provider
     * @param PageModel $page The current page of the request
     * @return string The data of the image
     */
    public function HandleImageRequest(PageModel $page): string {
        $requestFilename = RequestVariables::Get("filename");

        if(StringHelpers::IsNullOrWhiteSpace($requestFilename)){
            HttpResponse::ShowError(404, "The requested file could not be found", "File not found");
        }else{
            $fileHash = sha1($requestFilename);

            $this->databaseConnection->clearParameters();
            $this->databaseConnection->addParameter("filename", $requestFilename);

            $dataset = $this->databaseConnection->getDataset("SELECT * FROM `site_files` WHERE filename = ?filename LIMIT 1;");

            $page->Headers["Pragma"] = "public";
            $page->Headers["Cache-Control"] = "max-age=86400";
            $page->Headers["Expires"] = gmdate('D, d M Y H:i:s \G\M\T', time() + 86400);

            if($this->databaseConnection->rowCount > 0){
                $page->Headers["Content-type"] = $this->databaseConnection->DatasetFirstRow("content_type");
            }

            if(file_exists($_SERVER["DOCUMENT_ROOT"]."/cache/image_cache/".$fileHash.".cache")){
                return file_get_contents($_SERVER["DOCUMENT_ROOT"]."/cache/image_cache/".$fileHash.".cache");
            }else{
                if($this->databaseConnection->rowCount > 0){
                    if($this->databaseConnection->DatasetFirstRow("user_id", "integer") !== 0){
                        if($this->databaseConnection->DatasetFirstRow("user_id", "integer") !== $_SESSION["UserId"]){
                            HttpResponse::ShowError(403, "You don't have permission to access the requested content", "Forbidden");
                        }
                    }

                    foreach($dataset as $row){
                        if($row["cache"] == 1){
                            file_put_contents($_SERVER["DOCUMENT_ROOT"]."/cache/image_cache/".$fileHash.".cache", $row['content']);
                        }

                        return $row['content'];
                    }
                }else{
                    HttpResponse::ShowError(404, "The requested file could not be found", "File not found");
                }
            }
        }

        return "";
    }
}