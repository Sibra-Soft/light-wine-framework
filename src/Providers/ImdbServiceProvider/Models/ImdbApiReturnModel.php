<?php
namespace LightWine\Providers\ImdbServiceProvider\Models;

class ImdbApiReturnModel
{
    public string $Id = "";
    public string $Title = "";
    public string $Plot = "";
    public string $PlotLocal = "";
    public string $Tagline = "";
    public string $CoverImage = "";
    public string $CoverImageSmall = "";
    public string $ContentRating = "";
    public string $Countries = "";
    public string $ObjectType = "";
    public string $Languages = "";

    public int $Year = 0;
    public int $RuntimeMins = 0;

    public array $DirectorList = [];
    public array $StarList = [];
    public array $ActorList = [];
    public array $GenreList = [];
    public array $CountryList = [];
    public array $LanguageList = [];
    public array $SeasonList = [];

    public string $FirstDirector = "";
    public string $FirstActor = "";
    public string $FirstGenre = "";
    public string $FirstLanguage = "";
    public string $FirstCountry = "";

    public float $ImdbRating = 0.0;
}