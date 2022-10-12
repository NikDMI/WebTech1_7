<?php

require_once("../class/imageSiteReader.php");

$url = $_POST["url"];
$dirName = $_POST["dir"];
if($dirName=="" || $url=="") exit("Не введены парметры");

$siteReader = new ImageSiteReader($url,$dirName);
$siteReader->parseSitePage();

echo "Успешно выполнено";

//"https://unsplash.com/"
//"../images/"