<?php
namespace LightWine\Modules\Files\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\RequestVariables;
use LightWine\Modules\Files\Models\FileUploadModel;
use LightWine\Modules\Files\Interfaces\IUploadFileService;

class UploadFileService implements IUploadFileService
{
    private MysqlConnectionService $databaseConnection;

    public function __construct(MysqlConnectionService $connection){
        $this->databaseConnection = $connection;
    }

    /** {@inheritdoc} */
    public function UploadFileBasedOnUrl(string $url){
        $fileModel = new FileUploadModel();

        $targetDirectory = $_SERVER["DOCUMENT_ROOT"]."/temp/uploads/";

        $fileModel->Filename = Helpers::NewGuid().".jpg";
        $fileModel->File = $targetDirectory.$fileModel->Filename;

        // Download the file from the specified url
        Helpers::DownloadExternalFile($url, $fileModel->File);

        $this->UploadFile($fileModel);
    }

    /** {@inheritdoc} */
    public function UploadFileFromWebform(){
        $fileModel = new FileUploadModel();

        $fileModel->File = $_FILES["file_upload"]["tmp_name"];
        $fileModel->Filename = Helpers::NewGuid().".jpg";

        $this->UploadFile($fileModel);
    }

    /**
     * Upload the file to the database based on the specified FileModel
     * @param FileUploadModel $fileModel The model containing all the details of the file
     * @return FileUploadModel The model containing the details of the uploaded file
     */
    private function UploadFile(FileUploadModel $fileModel): FileUploadModel {
        $image = file_get_contents($fileModel->File);

        // Add the details to the upload model
        $fileModel->ItemId = (int)RequestVariables::Get("item_id", 0);
        $fileModel->FileSize = filesize($fileModel->File);
        $fileModel->MimeType = Helpers::GetMimeType($fileModel->File);
        $fileModel->ObjectType = RequestVariables::Get("type");
        $fileModel->Extension = strtolower(pathinfo($fileModel->File, PATHINFO_EXTENSION));
        $fileModel->ParentFolder = (int)RequestVariables::Get("parent_folder");
        $fileModel->UserId = (isset($_SESSION["UserId"])) ? $_SESSION["UserId"] : 0;

        // Add the parameters to the database connection
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("user_id", $fileModel->UserId);
        $this->databaseConnection->AddParameter("content", $image);
        $this->databaseConnection->AddParameter("filename", $fileModel->Filename);
        $this->databaseConnection->AddParameter("created_by", $_SESSION["UserFullname"]);
        $this->databaseConnection->AddParameter("item_id", $fileModel->ItemId, 0);
        $this->databaseConnection->AddParameter("type", $fileModel->ObjectType, "file");
        $this->databaseConnection->AddParameter("content_type", $fileModel->MimeType, "");
        $this->databaseConnection->AddParameter("parent_id", $fileModel->ParentFolder, 0);

        if($fileModel->ItemId <> 0){
            $this->databaseConnection->GetDataset("SELECT `id` FROM `site_files` WHERE `user_id` = ?user_id AND item_id = ?item_id LIMIT 1;");

            if($this->databaseConnection->rowCount > 0){
                $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_files", $this->databaseConnection->DatasetFirstRow("id"));
            }else{
                $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_files");
            }
        }else{
            $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_files");
        }

        $fileModel->Id = $this->databaseConnection->rowCount;

        return $fileModel;
    }
}