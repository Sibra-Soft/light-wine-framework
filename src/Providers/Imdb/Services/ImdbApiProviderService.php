<?php
namespace LightWine\Providers\Imdb\Services;

use LightWine\Core\Helpers\StringHelpers;
use LightWine\Core\Helpers\Helpers;
use LightWine\Providers\Imdb\Models\ImdbApiReturnModel;
use LightWine\Modules\ConfigurationManager\Services\ConfigurationManagerService;

class ImdbApiProviderService
{
    private ConfigurationManagerService $config;

    public function __construct(){
        $this->config = new ConfigurationManagerService();
    }

    /**
     * Handels the request for the API
     * @param string $function The name of the function you want to request
     * @param string $id The title id
     * @return array Array of information returned by the API
     */
    private function HandleAPIRequest(string $function, string $id): array {
        $key = $this->config->GetAppSetting("imdb")["api_key"];

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://imdb-api.com/nl/API/".$function."/".$key."/".$id,
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
     * Gets the episodes of a specified TV serie season
     * @param string $imdbId The imdb.com TV serie id
     * @param int $season The season the episodes must be downloaded from
     * @return array A array of episodes of the specifid season
     */
    public function GetSerieSeasonEpisodes(string $imdbId, int $season){
        $apiResponse = $this->HandleAPIRequest("SeasonEpisodes", $imdbId."/".$season);
        return $apiResponse;
    }

    /**
     * Gets the episodes of a specified TV serie season
     * @param string $imdbId The imdb.com TV serie id
     * @param int $season The season the episodes must be downloaded from
     * @return array A array of episodes of the specifid season
     */
    public function GetSerieMultipleSeasonEpisodes(string $imdbId, array $seasons){
        $seasonsEpisodeList= [];

        foreach($seasons as $season){
            $apiResponse = $this->HandleAPIRequest("SeasonEpisodes", $imdbId."/".$season);

            array_push($seasonsEpisodeList, $apiResponse["episodes"]);
        }

        return $seasonsEpisodeList;
    }

    /**
     * Search for a TVSerie on imdb.com based on the specified name
     * @param string $name The name of the serie to search for
     * @return array A array containing all the results of the search
     */
    public function SearchSerie(string $name){
        $apiResponse = $this->HandleAPIRequest("SearchSeries", $name);
        return $apiResponse;
    }

    /**
     * Search for a movie on imdb.com based on the specified name
     * @param string $name The name of the movie to search for
     * @return array A array containing all the results of the search
     */
    public function SearchMovie(string $name){
        $apiResponse = $this->HandleAPIRequest("SearchMovie", $name);
        return $apiResponse;
    }

    /**
     * Gets the information of a specified title based on the imdb.com id
     * @param string $imdbId The imdb.com title id (starts with tt...)
     * @return ImdbApiReturnModel The return model containing all the information of the movie
     */
    public function GetTitleBasedOnImdbId(string $imdbId) : ImdbApiReturnModel {
        $returnModel = new ImdbApiReturnModel;
        $apiResponse = $this->HandleAPIRequest("Title", $imdbId);

        $returnModel->Id = $apiResponse["id"];
        $returnModel->CoverImage = $apiResponse["image"];
        $returnModel->RuntimeMins = (StringHelpers::IsNullOrWhiteSpace($apiResponse["runtimeMins"]) ? 0 : $apiResponse["runtimeMins"]);
        $returnModel->Plot = (StringHelpers::IsNullOrWhiteSpace($apiResponse["plot"]) ? "" : $apiResponse["plot"]);
        $returnModel->PlotLocal = $apiResponse["plotLocal"];
        $returnModel->Year = $apiResponse["year"];
        $returnModel->Title = $apiResponse["title"];
        $returnModel->ActorList = $apiResponse["actorList"];
        $returnModel->DirectorList = $apiResponse["directorList"];
        $returnModel->GenreList = $apiResponse["genreList"];
        $returnModel->ImdbRating = (StringHelpers::IsNullOrWhiteSpace($apiResponse["imDbRating"]) ? 0 : $apiResponse["imDbRating"]);
        $returnModel->Tagline = (StringHelpers::IsNullOrWhiteSpace($apiResponse["tagline"]) ? "" : $apiResponse["tagline"]);
        $returnModel->ContentRating = (StringHelpers::IsNullOrWhiteSpace($apiResponse["contentRating"])? "" : $apiResponse["contentRating"]);
        $returnModel->StarList = $apiResponse["starList"];
        $returnModel->Countries = $apiResponse["countries"];
        $returnModel->CountryList = $apiResponse["countryList"];
        $returnModel->LanguageList = $apiResponse["languageList"];
        $returnModel->ObjectType = $apiResponse["type"];
        $returnModel->Languages = $apiResponse["languages"];

        // Only for TV series
        if($returnModel->ObjectType === "TVSeries"){
            $returnModel->SeasonList = $apiResponse["tvSeriesInfo"]["seasons"];
        }

        // Generate small cover image URL
        $coverImageAt = str_repeat("@", substr_count($returnModel->CoverImage, '@'));
        $smallCoverImageUrl = StringHelpers::StripAfterString($returnModel->CoverImage, "@");
        $smallCoverImageUrl = $smallCoverImageUrl.$coverImageAt."._V1_SX300.jpg";
        $returnModel->CoverImageSmall = $smallCoverImageUrl;

        $returnModel->FirstLanguage = Helpers::FirstOrDefault($returnModel->LanguageList)["value"];
        $returnModel->FirstCountry = Helpers::FirstOrDefault($returnModel->CountryList)["value"];
        $returnModel->FirstActor = Helpers::FirstOrDefault($returnModel->StarList)["name"];
        $returnModel->FirstDirector = Helpers::FirstOrDefault($returnModel->DirectorList)["name"];
        $returnModel->FirstGenre = Helpers::FirstOrDefault($returnModel->GenreList)["value"];

        return $returnModel;
    }
}