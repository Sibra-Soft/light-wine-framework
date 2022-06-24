<?php
namespace LightWine\Providers\ImdbServiceProvider\Interfaces;

use LightWine\Providers\ImdbServiceProvider\Models\ImdbApiReturnModel;

interface IImdbServiceProvider
{
    public function Render();
    public function GetSerieSeasonEpisodes(string $imdbId, int $season);
    public function GetSerieMultipleSeasonEpisodes(string $imdbId, array $seasons);
    public function SearchSerie(string $name);
    public function SearchMovie(string $name);
    public function GetTitleBasedOnImdbId(string $imdbId):ImdbApiReturnModel;
    public function GetTop250();
}
