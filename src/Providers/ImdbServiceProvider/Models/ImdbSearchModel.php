<?php
namespace LightWine\Providers\ImdbServiceProvider\Models;

class ImdbSearchModel
{
    public string $ImdbId_I = "";
    public string $ImdbTitle_T = "";
    public string $ImdbSearch_S = "";
    public string $Type = "";
    public string $Year = "";
    public string $PlotType = "short";
    public string $ResponseType = "json";
    
    public bool $ShowSerieDetails = false;

    public int $PageNumber = 1;
    public int $Version = 1;
    public int $Season = 0;
}
?>