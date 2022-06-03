<?php
namespace LightWine\Modules\Database\Interfaces;

interface IDatabaseHelperService
{
    /**
     * This function adds, or edits rows in a table based on the specified array
     * @param array $mutationArray The array of data to add or edit
     * @param string $targetTable The table the data must be added, or changed
     */
    public function SyncSet(array $mutationArray, string $targetTable, array $options);

    /**
     * Use the Lookup function to display the value of a field that isn't in the record source for your form or report
     * @param string $expr An expression that identifies the field whose value you want to return
     * @param string $table A string expression identifying the set of records that constitutes the domain
     * @param string $criteria A string expression used to restrict the range of data on which the DLookup function is performed
     */
    public function Lookup(string $expr, string $table, string $criteria);

    /**
     * Deletes multiple records based on the specified ids
     * @param string $table The table the records must be deleted from
     * @param string $ids Comma separated a list of ids of the records you want to delete
     */
    public function DeleteMultipleRecords(string $table, string $ids);

    /**
     * This function deletes a record from a specified tabel based on the specified id
     * @param string $table The table the record must be deleted from
     * @param int $id The id of the record that must be deleted
     */
    public function DeleteRecord(string $table, int $id);

    /**
     * This function updates or inserts a record in a specific database table
     * @param string $table The table a record must be updated or inserted into
     * @param int|null $id The id of the row that must be updated
     */
    public function UpdateOrInsertRecordBasedOnParameters(string $table, int $id = null, bool $ignoreDuplicates = false):int;

    /**
     * Upload a file based on a specified url
     * @param string $url The url of the file to upload to the database
     */
    public function UploadBlobBasedOnUrl(string $url);

    /**
     * Upload a file to the server from the browser
     */
    public function UploadBlob();
}
?>