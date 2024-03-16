<?php
namespace LightWine\Modules\Files\Models;

use \DateTime;

use LightWine\Modules\Files\Services\FileModelFunctions;

class FileReturnModel {
    public string $Name;
    public string $Extension;
    public string $Path;
    public string $Mime;
    public string $BlobContent;

    public int $Size;
    public int $DownloadCounter;
    public int $LinkedItemId;

    public FilePoliciesModel $Policies;
    public FileModelFunctions $Helpers;

    public DateTime $DateAdded;
    public DateTime $DateModified;
}
?>