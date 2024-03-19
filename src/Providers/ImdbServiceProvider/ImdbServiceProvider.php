<?php
namespace LightWine\Providers\ImdbServiceProvider;

use LightWine\Core\Helpers\RequestVariables;
use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\Helpers\Helpers;
use LightWine\Core\HttpResponse;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;
use LightWine\Providers\ImdbServiceProvider\Interfaces\IImdbServiceProvider;
use LightWine\Providers\ImdbServiceProvider\Models\ImdbApiReturnModel;
use LightWine\Providers\ImdbServiceProvider\Models\ImdbSearchModel;

class ImdbServiceProvider implements IImdbServiceProvider
{
    private ConfigurationManagerService $settings;

    private string $ApiKey;

    public function __construct(){
        $this->settings = new ConfigurationManagerService();
    }

    /**
     * Starts the required action based on the specified request variables
     */
    public function Render(){
        $this->ApiKey = $this->settings->GetAppSetting("imdb")["omdb_api_key"];

        if(StringHelpers::IsNullOrWhiteSpace($this->ApiKey)){
            Throw new \Exception("You must specify a api key in order to use this service");
        }

        // Search a movie
        if(!StringHelpers::IsNullOrWhiteSpace(RequestVariables::Get("search_movie"))){
            HttpResponse::SetReturnJson($this->SearchMovie(RequestVariables::Get("search_movie")));
        }

        // Search a serie
        if(!StringHelpers::IsNullOrWhiteSpace(RequestVariables::Get("search_serie"))){
            HttpResponse::SetReturnJson($this->SearchSerie(RequestVariables::Get("search_serie")));
        }

        // Get title details based on imdb id
        if(!StringHelpers::IsNullOrWhiteSpace(RequestVariables::Get("imdb_id"))){
            HttpResponse::SetReturnJson((array)$this->GetTitleBasedOnImdbId(RequestVariables::Get("imdb_id")));
        }

        // Get list of episodes
        if(!StringHelpers::IsNullOrWhiteSpace(RequestVariables::Get("imdb_serie"))){
            $serieRequestParam = RequestVariables::Get("imdb_serie");

            if(!StringHelpers::Contains($serieRequestParam, "_")){
                throw new \Exception("Parameter syntax error");
            }

            $serieId = StringHelpers::SplitString($serieRequestParam, "_", 0);
            $serieSeason = StringHelpers::SplitString($serieRequestParam, "_", 1);

            if($serieSeason === "*"){
                $seasonsParam = RequestVariables::Get("seasons");
                HttpResponse::SetReturnJson($this->GetSerieEpisodes($serieId, explode(",", $seasonsParam)));
            }else{
                HttpResponse::SetReturnJson($this->GetSeasonEpisodes($serieId, $serieSeason));
            }
        }

        return "Error";
    }

    /**
     * Generates the querystring required for the omdb.com api
     * @param ImdbSearchModel $model The model containing the details of the request
     * @return string The generated querystring
     */
    private function GenerateQuerystringBasedOnModel(ImdbSearchModel $model): string {
        $queryStringBuilder = [];

        foreach($model as $key => $value){
            if(StringHelpers::IsNullOrWhiteSpace($value)){
                continue;
            }

            // Check if serie details is required
            if($key === "ShowSerieDetails" and $value == 1) array_push($queryStringBuilder, "detail=full");

            switch($key){
                case "ImdbId_I": array_push($queryStringBuilder, "i=".urlencode($value)); break;
                case "ImdbTitle_T": array_push($queryStringBuilder, "t=".$value); break;
                case "ImdbSearch_S": array_push($queryStringBuilder, "s=".urlencode($value)); break;
                case "Type": array_push($queryStringBuilder, "type=".$value); break;
                case "Year": array_push($queryStringBuilder, "y=".$value); break;
                case "PlotType": array_push($queryStringBuilder, "plot=".$value); break;
                case "ResponseType": array_push($queryStringBuilder, "r=".$value); break;
                case "Version": array_push($queryStringBuilder, "v=".$value); break;
                case "Season": array_push($queryStringBuilder, "season=".$value); break;
            }
        }

        return implode("&", $queryStringBuilder);
    }

    /**
     * Handle the request to the api
     * @param ImdbSearchModel $model A model containg all the details of the request
     * @return array A array containg the response of the api
     */
    private function HandleRequest(ImdbSearchModel $model): array {
        $curl = curl_init();

        $queryString = $this->GenerateQuerystringBasedOnModel($model);

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.omdbapi.com/?apikey=".$this->ApiKey."&".$queryString,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    /**
     * Gets the episodes of a specified imdb serie
     * @param string $imdbId The imdb.com id of the serie
     * @param int $season The season you want to get the episodes of
     * @return array Array containing all the episodes of the serie
     */
    public function GetSeasonEpisodes(string $imdbId, int $season): array {
        $searchModel = new ImdbSearchModel;

        $searchModel->ImdbId_I = $imdbId;
        $searchModel->ShowSerieDetails = true;
        $searchModel->Season = $season;

        $apiResponse = $this->HandleRequest($searchModel);

        return $apiResponse;
    }

    /**
     * Gets all the episodes of a specified serie and seasons
     * @param string $imdbId The imdb.com id of the serie
     * @param array $seasons The seasons you want to get the episodes of
     * @return array Array containing all the episodes of the serie
     */
    public function GetSerieEpisodes(string $imdbId, array $seasons): array {
        $seasonsEpisodeList = [];

        foreach($seasons as $season){
            $searchModel = new ImdbSearchModel;

            $searchModel->ImdbId_I = $imdbId;
            $searchModel->ShowSerieDetails = true;
            $searchModel->Season = $season;

            $apiResponse = $this->HandleRequest($searchModel);

            array_push($seasonsEpisodeList, ["Season_".$season => $apiResponse["Episodes"]]);
            sleep(1);
        }

        return $seasonsEpisodeList;
    }

    /**
     * Search for a TVSerie on imdb.com based on the specified name
     * @param string $name The name of the serie to search for
     * @return array A array containing all the results of the search
     */
    public function SearchSerie(string $name): array {
        $searchModel = new ImdbSearchModel;

        $searchModel->ImdbSearch_S = $name;
        $searchModel->Type = "series";

        $apiResponse = $this->HandleRequest($searchModel);

        return $apiResponse;
    }

    /**
     * Search for a movie on imdb.com based on the specified name
     * @param string $name The name of the movie to search for
     * @return array A array containing all the results of the search
     */
    public function SearchMovie(string $name): array {
        $searchModel = new ImdbSearchModel;

        $searchModel->ImdbSearch_S = $name;
        $searchModel->Type = "movie";

        $apiResponse = $this->HandleRequest($searchModel);

        return $apiResponse;
    }

    /**
     * Gets the information of a specified title based on the imdb.com id
     * @param string $imdbId The imdb.com title id (starts with tt...)
     * @return ImdbApiReturnModel The return model containing all the information of the movie
     */
    public function GetTitleBasedOnImdbId(string $imdbId) : ImdbApiReturnModel {
        $returnModel = new ImdbApiReturnModel;
        $searchModel = new ImdbSearchModel();

        $searchModel->ImdbId_I = $imdbId;

        $apiResponse = $this->HandleRequest($searchModel);

        // Get details
        $returnModel->Title = $apiResponse["Title"];
        $returnModel->Id = $apiResponse["imdbID"];
        $returnModel->Year = (is_numeric($apiResponse["Year"]) ? $apiResponse["Year"] : 0);
        $returnModel->RuntimeMins = (int)$apiResponse["Runtime"];
        $returnModel->GenreList = explode(",", $apiResponse["Genre"]);
        $returnModel->CountryList = explode(",", $apiResponse["Country"]);
        $returnModel->DirectorList = explode(",", $apiResponse["Director"]);
        $returnModel->ActorList = explode(",", $apiResponse["Actors"]);
        $returnModel->LanguageList = explode(",", $apiResponse["Language"]);
        $returnModel->CoverImage = $apiResponse["Poster"];
        //$returnModel->ImdbRating = $apiResponse["Metascore"]["imdbRating"];
        $returnModel->ObjectType = $apiResponse["Type"];
        $returnModel->ContentRating = $apiResponse["Rated"];
        $returnModel->Plot = $apiResponse["Plot"];
        $returnModel->NumberOfSeasons = (is_numeric($apiResponse["totalSeasons"]) ? $apiResponse["totalSeasons"] : 0);

        // Get first in list of items
        $returnModel->FirstActor = Helpers::FirstOrDefault($returnModel->ActorList);
        $returnModel->FirstCountry = Helpers::FirstOrDefault($returnModel->CountryList);
        $returnModel->FirstDirector = Helpers::FirstOrDefault($returnModel->DirectorList);
        $returnModel->FirstLanguage = Helpers::FirstOrDefault($returnModel->LanguageList);
        $returnModel->FirstGenre = Helpers::FirstOrDefault($returnModel->GenreList);

        return $returnModel;
    }
}