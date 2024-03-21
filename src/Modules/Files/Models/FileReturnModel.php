<?php
namespace LightWine\Modules\Files\Models;

use DateTime;

class FileReturnModel
{
    public FilePolicyModel $Policies;

    public string $Name;
    public string $Extension;
    public string $Path;
    public string $Mime;
    public string $Url;
    public string $Blob;

    public int $Size;
    public int $DownloadCount;
    public int $LinkedItemId;
    public int $Id;
    public int $UserId;

    public DateTime $DateAdded;
    public DateTime $DateModified;
    public DateTime $FileDate;

    public function __construct(){
        $this->Policies = new FilePolicyModel;
    }

    /**
     * Starts a download from the browser
     */
    public function Download(){
        
    }
}