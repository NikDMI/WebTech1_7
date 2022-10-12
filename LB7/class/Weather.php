<?php

class Weather{
    static protected $lat;//географические параметры
    static protected $lon;

    public function __construct(){

    }

    public function getWeather(string $townNane):float|bool{

    }
}


class OpenWeatherMapAPI extends Weather{ //https://api.openweathermap.org
    private $keyAPI = "4a100352a8de214c0d85757d6f4b6de7";
   

    public function getLocation(string $townName):bool{//получение географических координат города
        $serverResponse = file_get_contents("http://api.openweathermap.org/geo/1.0/direct?q={$townName}&limit=5&appid={$this->keyAPI}");
        if($serverResponse=="")return false;
        $json = json_decode($serverResponse,true);
        Weather::$lat = $json[0]["lat"];
        Weather::$lon = $json[0]["lon"];
        return true;
    }

    public function getWeather(string $townName):float|bool{
        if(static::getLocation($townName)===false) return false;
        $lat =  Weather::$lat;  $lon = Weather::$lon;
        $serverResponse = file_get_contents("https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$this->keyAPI}");
        if($serverResponse=="") return false;
        $json = json_decode($serverResponse,true);
        if(isset($json)===false) return false;
        //var_dump($json);
        $temperatureK = $json["main"]["temp"];
        return ($temperatureK-273);
    }
}


class StormGlassAPI extends Weather{ //https://stormglass.io
    private $keyAPI = "178a0c66-e0d6-11ec-ab6b-0242ac130002-178a0ce8-e0d6-11ec-ab6b-0242ac130002";
    private $params = 'airTemperature';

    public function getWeather(string $townName):float|bool{
        $crequest = curl_init();

        curl_setopt($crequest, CURLOPT_HEADER, 0);
        curl_setopt($crequest, CURLOPT_RETURNTRANSFER, 1);
        $lat =  Weather::$lat;  $lon = Weather::$lon;
        curl_setopt($crequest, CURLOPT_URL, "https://api.stormglass.io/v2/weather/point?lat={$lat}&lng={$lon}&params={$this->params}");
        curl_setopt($crequest, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($crequest, CURLOPT_VERBOSE, 0);
        curl_setopt($crequest, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($crequest, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->keyAPI,
          ));
        $serverResponse = curl_exec($crequest);

        //var_dump($serverResponse);
        curl_close($crequest);
        //header("Authorization: '{$this->keyAPI}'");
        //$lat =  Weather::$lat;  $lon = Weather::$lon;
        //$serverResponse = file_get_contents("https://api.stormglass.io/v2/weather/point?lat={$lat}&lng={$lon}&params={$this->params}");
        if($serverResponse=="") return false;
        $json = json_decode($serverResponse,true);
        if(isset($json)===false) return false;
        //var_dump($json);
        $temperatureC = $json["hours"][(int)date("H")]["airTemperature"]["sg"];
        return $temperatureC;
    }
}