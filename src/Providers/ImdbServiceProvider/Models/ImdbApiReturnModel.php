<?php
namespace LightWine\Providers\ImdbServiceProvider\Models;

class ImdbApiReturnModel
{
    public string $Id = "";
    public string $Title = "";
    public string $Plot = "";
    public string $CoverImage = "";
    public string $ContentRating = "";
    public string $Countries = "";
    public string $ObjectType = "";
    public string $Languages = "";

    public int $Year = 0;
    public int $RuntimeMins = 0;
    public int $NumberOfSeasons = 0;

    public array $DirectorList = [];
    public array $ActorList = [];
    public array $GenreList = [];
    public array $CountryList = [];
    public array $LanguageList = [];

    public string $FirstDirector = "";
    public string $FirstActor = "";
    public string $FirstGenre = "";
    public string $FirstLanguage = "";
    public string $FirstCountry = "";

    public float $ImdbRating = 0.0;
}