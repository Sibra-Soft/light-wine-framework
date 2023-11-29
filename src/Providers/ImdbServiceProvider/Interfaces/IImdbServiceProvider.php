<?php
namespace LightWine\Providers\ImdbServiceProvider\Interfaces;

use LightWine\Providers\ImdbServiceProvider\Models\ImdbApiReturnModel;

interface IImdbServiceProvider
{
    public function Render();
    public function GetSeasonEpisodes(string $imdbId, int $season): array;
    public function GetSerieEpisodes(string $imdbId, array $seasons): array;
    public function SearchSerie(string $name): array;
    public function SearchMovie(string $name): array;
    public function GetTitleBasedOnImdbId(string $imdbId) : ImdbApiReturnModel;
}
