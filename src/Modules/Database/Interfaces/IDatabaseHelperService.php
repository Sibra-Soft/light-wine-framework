<?php
namespace LightWine\Modules\Database\Interfaces;

interface IDatabaseHelperService
{
    public function SyncSet(array $mutationArray, string $targetTable, array $options);
    public function Lookup(string $expr, string $table, string $criteria);
    public function DeleteRecord(string $table, int $id);
    public function UpdateOrInsertRecordBasedOnParameters(string $table, int $id = null, bool $ignoreDuplicates = false):int;
    public function UploadBlobBasedOnUrl(string $url);
}
?>