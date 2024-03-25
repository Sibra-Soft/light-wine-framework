<?php
namespace LightWine\Modules\Files\Services;

use LightWine\Core\Helpers\Helpers;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\Database\Services\MysqlConnectionService;
use LightWine\Modules\Files\Models\FileReturnModel;

class FileService {
    private MysqlConnectionService $dbConnection;

    public function __construct(){
        $this->dbConnection = new MysqlConnectionService();
    }

    /**
     * Get the specified file from the database, based on filename or itemId
     * @param string $name The name of the file you want to get
     * @param int $itemId The itemId of the file you want to get
     * @throws \Exception If the specified file could not be found
     */
    private function GetFileFromDatabase(string $name = "", int $itemId = 0){
        $queryWherePart = "";

        if(StringHelpers::IsNullOrWhiteSpace($name)){
            $queryWherePart = "WHERE item_id = ?itemId";
        }else{
            $queryWherePart = "WHERE filename = ?fileName";
        }

        $this->dbConnection->ClearParameters();
        $this->dbConnection->AddParameter("fileName", $name);
        $this->dbConnection->AddParameter("itemId", $itemId);
        $this->dbConnection->GetDataset("
            SELECT
                id,
                item_id,
                filename,
                content,
                content_type,
                date_added,
                date_modified,
                download_count,
                LENGTH(content) AS file_size,
                cache,
                user_id
            FROM site_files
            ".$queryWherePart."
            LIMIT 1;
        ");

        // Check if the file is found
        if($this->dbConnection->rowCount == 0) Throw new \Exception("Specified file not found: ".$name);
    }

    /**
     * Gets a file based on the name
     * @param string $name The name of the file you want to get
     * @param int $folder Optional folder id where the file is placed
     * @return FileReturnModel A model containing all the details of the file
     */
    public function GetFileByName(string $name, int $folder = 0): FileReturnModel {
        $returnModel = new FileReturnModel;

        $this->GetFileFromDatabase($name);

        // Add details
        $returnModel->Id = $this->dbConnection->DatasetFirstRow("id");
        $returnModel->LinkedItemId = $this->dbConnection->DatasetFirstRow("item_id", "integer");
        $returnModel->Name = $this->dbConnection->DatasetFirstRow("filename");
        $returnModel->Mime = $this->dbConnection->DatasetFirstRow("content_type");
        $returnModel->DateAdded = $this->dbConnection->DatasetFirstRow("date_added", "datetime");
        $returnModel->DateModified = $this->dbConnection->DatasetFirstRow("date_modified", "datetime");
        $returnModel->DownloadCount = $this->dbConnection->DatasetFirstRow("download_count");
        $returnModel->Size = $this->dbConnection->DatasetFirstRow("file_size");
        $returnModel->Url = $this->dbConnection->DatasetFirstRow("url");
        $returnModel->Blob = $this->dbConnection->DatasetFirstRow("content");
        $returnModel->UserId = $this->dbConnection->DatasetFirstRow("user_id");

        // Add policies
        $returnModel->Policies->FILE_IS_CACHED = $this->dbConnection->DatasetFirstRow("cache", "boolean");
        $returnModel->Policies->USER_MUST_BE_LOGGED_IN = ($returnModel->UserId <> 0 ? true : false);

        return $returnModel;
    }

    /**
     * Gets a file based on the name
     * @param int $id The itemId of the file you want to get
     * @param int $folder Optional folder id where the file is placed
     * @return FileReturnModel A model containing all the details of the file
     */
    public function GetFileById(int $id, int $folder = 0): FileReturnModel {
        $returnModel = new FileReturnModel;

        $this->GetFileFromDatabase("", $id);

        // Add details
        $returnModel->Id = $this->dbConnection->DatasetFirstRow("id");
        $returnModel->LinkedItemId = $this->dbConnection->DatasetFirstRow("item_id", "integer");
        $returnModel->Name = $this->dbConnection->DatasetFirstRow("filename");
        $returnModel->Mime = $this->dbConnection->DatasetFirstRow("content_type");
        $returnModel->DateAdded = $this->dbConnection->DatasetFirstRow("date_added", "datetime");
        $returnModel->DateModified = $this->dbConnection->DatasetFirstRow("date_modified", "datetime");
        $returnModel->DownloadCount = $this->dbConnection->DatasetFirstRow("download_count");
        $returnModel->Size = $this->dbConnection->DatasetFirstRow("file_size");
        $returnModel->Url = $this->dbConnection->DatasetFirstRow("url");
        $returnModel->Blob = $this->dbConnection->DatasetFirstRow("content");
        $returnModel->UserId = $this->dbConnection->DatasetFirstRow("user_id");

        // Add policies
        $returnModel->Policies->FILE_IS_CACHED = $this->dbConnection->DatasetFirstRow("cache", "boolean");
        $returnModel->Policies->USER_MUST_BE_LOGGED_IN = ($returnModel->UserId <> 0 ? true : false);

        return $returnModel;
    }

    /**
     * Upload a file based on the specified url
     * @param string $url The url of the file to upload
     * @param int $itemId The item id of the item in the database the file must be linked to
     * @param int $folder The folder the file must be added to after uploading
     * @param int $user The user the file must be linked to
     * @param string $property The property this file must take
     * @param string $filename The filename that must be used when the file is added to the database
     * @return int The id of the file after it has been uploaded
     */
    public function UploadFileBasedOnUrl(string $url, int $itemId = 0, int $folder = 0, int $user = 0, string $property = "images", string $filename = ""): int {
        $content = file_get_contents($url);

        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $createdByName = "System";

        // Generate filename when not set
        if(StringHelpers::IsNullOrWhiteSpace($filename)){
            $filename = Helpers::NewGuid().".".$ext;
        }

        // Get username when loggedin
        if($user > 0){
            $createdByName = $_SESSION["UserFullname"];
        }

        $this->dbConnection->ClearParameters();
        $this->dbConnection->AddParameter("content", $content);
        $this->dbConnection->AddParameter("user_id", $user);
        $this->dbConnection->AddParameter("item_id", $itemId);
        $this->dbConnection->AddParameter("parent_id", $folder);
        $this->dbConnection->AddParameter("type", "file");
        $this->dbConnection->AddParameter("filename", $filename);
        $this->dbConnection->AddParameter("created_by", $createdByName);
        $this->dbConnection->AddParameter("content_type", "image/jpeg");

        // Upload the file
        $uploadId = $this->dbConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_files");

        return $uploadId;
    }

    /**
     * Upload a file using a webform
     * @param string $requestFieldname The name of the field in the webform containing the file
     * @param int $itemId The item id of the item in the database the file must be linked to
     * @param int $folder The folder the file must be added to after uploading
     * @param int $user The user the file must be linked to
     * @param string $property The property this file must take
     * @param string $filename The filename that must be used when the file is added to the database
     * @return int The id of the file after it has been uploaded
     */
    public function UploadFileFromWebform(string $requestFieldname, int $itemId = 0, int $folder = 0, int $user = 0, string $property = "images", string $filename = ""): int {

    }

    /**
     * Move a specified file to a specified folder
     * @param int $fileId The id of the file you want to move
     * @param int $folderId The id of the folder you want to move the file to
     * @return bool True is the move action has been completed
     */
    public function MoveFileToFolder(int $fileId, int $folderId): bool {
        $this->dbConnection->ClearParameters();
        $this->dbConnection->AddParameter("parent_id", $folderId);
        $this->dbConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_files", $fileId);

        return ($this->dbConnection->rowsAffected > 0 ? true : false);
    }
}
?>