<?php
namespace LightWine\Modules\Sam\Models;

class SamLoginResponseModel
{
    public string $Username;
    public string $UserDisplayName;
    public string $UserFullname;
    public string $ClientToken;
    public string $Checksum;

    public array $Roles;
    public array $Settings;

    public int $UserId;

    public bool $LoginCorrect;
}