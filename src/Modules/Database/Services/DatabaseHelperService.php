<?php
namespace LightWine\Modules\Database\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Modules\QueryBuilder\Services\QueryBuilderService;
use LightWine\Modules\QueryBuilder\Enums\QueryExtenderEnum;
use LightWine\Modules\QueryBuilder\Enums\QueryOperatorsEnum;
use LightWine\Core\Helpers\Helpers;
use LightWine\Modules\Database\Models\UploadBlobModel;
use LightWine\Core\Helpers\HttpContextHelpers;

class DatabaseHelperService {
    private MysqlConnectionService $databaseConnection;
    private QueryBuilderService $queryBuilder;

    public function __construct(MysqlConnectionService $connection){
        $this->databaseConnection = $connection;
        $this->queryBuilder = new QueryBuilderService();
    }

    /**
     * This function adds, or edits rows in a table based on the specified array
     * @param array $mutationArray The array of data to add or edit
     * @param string $targetTable The table the data must be added, or changed
     */
    public function SyncSet(array $mutationArray, string $targetTable, array $options){
        $queries = "";

        foreach ($mutationArray as $key) {
            if(StringHelpers::IsNullOrWhiteSpace($key["id"])){
                // Must be added
                unset($key["id"]);

                $key += $options;
                $key += ["user_id" => $_SESSION["UserId"]];

                foreach($key as $column => $value){
                    $this->queryBuilder->Insert($targetTable, $column, $value);
                }

                $queries .= $this->queryBuilder->Render().";";
            }else{
                // Must be added
                $id = $key["id"];
                unset($key["id"]);

                $key += $options;
                $key += ["user_id" => $_SESSION["UserId"]];

                foreach($key as $column => $value){
                    $this->queryBuilder->Update($targetTable, $column, $value);
                    $this->queryBuilder->Where(QueryExtenderEnum::Nothing, "id", QueryOperatorsEnum::EqualTo, $id);
                }

                $queries .= $this->queryBuilder->Render().";";
            }
        }

        if(count($mutationArray) > 0){
            $this->databaseConnection->ExecuteQuery($queries);
        }
    }

    /**
     * Use the Lookup function to display the value of a field that isn't in the record source for your form or report
     * @param string $expr An expression that identifies the field whose value you want to return
     * @param string $table A string expression identifying the set of records that constitutes the domain
     * @param string $criteria A string expression used to restrict the range of data on which the DLookup function is performed
     */
    public function Lookup(string $expr, string $table, string $criteria){
        // Build the criteria
        $criteria = (StringHelpers::IsNullOrWhiteSpace($criteria) ? "1=1": $criteria);

        // Build the lookup query
        $this->databaseConnection->GetDataset("SELECT $expr FROM $table WHERE $criteria LIMIT 1;");

        // Return the selected value
        return $this->databaseConnection->DatasetFirstRow($expr);
    }

    /**
     * This function deletes a record from a specified tabel based on the specified id
     * @param string $table The table the record must be deleted from
     * @param int $id The id of the record that must be deleted
     */
    public function DeleteRecord(string $table, int $id){
        $this->queryBuilder->Delete($table);
        $this->queryBuilder->Where(QueryExtenderEnum::Nothing, "id", QueryOperatorsEnum::EqualTo, $id);

        $this->databaseConnection->ExecuteQuery($this->queryBuilder->Render());
    }

    /**
     * This function updates or inserts a record in a specific database table
     * @param string $table The table a record must be updated or inserted into
     * @param int|null $id The id of the row that must be updated
     */
    public function UpdateOrInsertRecordBasedOnParameters(string $table, int $id = null, bool $ignoreDuplicates = false):int {
        $this->queryBuilder->ignoreDuplicatesOnInsert = $ignoreDuplicates;

        // Generate the query build on the specified parameters
        $parameters = $this->databaseConnection->mysqlQueryParameters;

        // Check if it's a add or a update query
        if(StringHelpers::IsNullOrWhiteSpace($id)){
            foreach($parameters as $key => $value){
                $this->queryBuilder->Insert($table, str_replace("?", "", $key), $value);
            }
        }else{
            foreach($parameters as $key => $value){
                $this->queryBuilder->Update($table, str_replace("?", "", $key), $value);
            }
            $this->queryBuilder->Where(QueryExtenderEnum::Nothing, "id", QueryOperatorsEnum::EqualTo, $id);

        }

        // Render and execute the query
        $query = $this->queryBuilder->Render();
        $this->databaseConnection->ExecuteQuery($query);

        return $this->databaseConnection->rowInsertId;
    }

    /**
     * Upload a file based on a specified url
     * @param string $url The url of the file to upload to the database
     */
    public function UploadBlobBasedOnUrl(string $url){
        $uploadModel = new UploadBlobModel;

        $targetDirectory = $_SERVER["DOCUMENT_ROOT"]."/temp/uploads/";

        $uploadModel->Filename = Helpers::NewGuid().".jpg";
        $uploadModel->File = $targetDirectory.$uploadModel->Filename;

        // Download the file from the specified url
        Helpers::DownloadExternalFile($url, $uploadModel->File);

        // Get the content of the current file
        $image = file_get_contents($uploadModel->File);

        // Add the details to the upload model
        $uploadModel->ItemId = (int)HttpContextHelpers::RequestVariable("item_id", 0);
        $uploadModel->FileSize = filesize($uploadModel->File);
        $uploadModel->MimeType = Helpers::GetMimeType($uploadModel->File);
        $uploadModel->ObjectType = HttpContextHelpers::RequestVariable("type");
        $uploadModel->ImageWidth = getimagesize($uploadModel->File)[0];
        $uploadModel->ImageHeight = getimagesize($uploadModel->File)[1];
        $uploadModel->Extension = strtolower(pathinfo($uploadModel->File, PATHINFO_EXTENSION));
        $uploadModel->ParentFolder = (int)HttpContextHelpers::RequestVariable("parent_folder");

        // Add the parameters to the database connection
        $this->databaseConnection->ClearParameters();
        $this->databaseConnection->AddParameter("user_id", $_SESSION["UserId"]);
        $this->databaseConnection->AddParameter("content", $image);
        $this->databaseConnection->AddParameter("filename", $uploadModel->Filename);
        $this->databaseConnection->AddParameter("created_by", $_SESSION["UserFullname"]);
        $this->databaseConnection->AddParameter("item_id", $uploadModel->ItemId, 0);
        $this->databaseConnection->AddParameter("type", $uploadModel->ObjectType, "image");
        $this->databaseConnection->AddParameter("content_type", $uploadModel->MimeType, "");
        $this->databaseConnection->AddParameter("parent_id", $uploadModel->ParentFolder, 0);

        if($uploadModel->ItemId <> 0){
            $this->databaseConnection->GetDataset("SELECT `id` FROM `site_files` WHERE `user_id` = ?user_id AND item_id = ?item_id LIMIT 1;");

            if($this->databaseConnection->rowCount > 0){
                $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_files", $this->databaseConnection->DatasetFirstRow("id"));
            }else{
                $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_files");
            }
        }else{
            $this->databaseConnection->helpers->UpdateOrInsertRecordBasedOnParameters("site_files");
        }

        $uploadModel->Id = $this->databaseConnection->rowCount;

        return $uploadModel;
    }
}
?>