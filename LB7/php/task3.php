<?php
require_once("../class/Weather.php");

$town = $_POST['town'];
if($town=="") exit("Не введен город");

$obj = new OpenWeatherMapAPI();
$weatherServices = array(new OpenWeatherMapAPI(),new StormGlassAPI());
$weatherServices[0]->getLocation($town);//получение географических координат
$temp=0; $amount=0;
foreach($weatherServices as $service){
    $retTemp = $service->getWeather($town);
    if($retTemp!==false){
        $temp+=$retTemp;
        $amount++;
    }
}
if($amount==0){
    echo "Не получилось собрать данные о погоде";
}else{
    $temp = $temp/$amount;
    echo "Сейчас в {$town} <span style=\"color: red;\">"  . round($temp,2) ." </span><sup>o</sup>C";
}
