<?php

class ImageSiteReader{
    private $urlSite;
    private $imageDir;
    private $lookedDirs = 0;//кол-во просмотренный каталогов
    private $visitedCatalogs = [];

    const MAX_IMAGE_COUNT=3;
    const MAX_RECURSIVE_DIR = 8;

    function __construct(string $url,string $dirLocation){
        $this->urlSite = $url;
        $this->imageDir = $dirLocation;
        if(is_dir($this->imageDir)===false){
            throw new Exception("Выбранной директории не существует, либо это не директория");
        }
    }

    public function parseSitePage(string $fileContent="",string $catalogName=""){
        if($fileContent=="") $fileContent  = file_get_contents($this->urlSite);
        if($catalogName=="") $catalogName=$this->imageDir;
        var_dump($catalogName);
        preg_match_all("/<img [^>]*src[^=>]*= *\"([^\"]+)\"/",$fileContent,$matches);
        //matches[1] - элементы первой подмаски
        //var_dump($matches[1]);
        $imageCount = 0;
        foreach($matches[1] as $imgURL){
            //$imageContent = file_get_contents($imgURL);
            $imagePath = parse_url($imgURL, PHP_URL_PATH);
            $imgName = pathinfo($imagePath)['basename'];
            $fullPath = $catalogName . $imgName;
            if(!isset(pathinfo($imagePath)['extension'])) $fullPath .= ".png";
            if(file_exists($fullPath)===true){
                //throw new Exception("Файл {$fullPath} уже есть в каталоге");
                continue;
            }
            if($imgURL[0]=='/') $imgURL = $this->urlSite . $imgURL;
            //var_dump($imgURL);
            $fileContent = file_get_contents($imgURL);
            $fileHandle = fopen($fullPath,"wb");
            fwrite($fileHandle, $fileContent);
            fclose($fileHandle);
            $imageCount++;
            if($imageCount>=ImageSiteReader::MAX_IMAGE_COUNT)break;
        }
    }

    public function readCatalogs(string $rootUrl=""){
        $this->visitedCatalogs[] = $rootUrl;

        $fullPath = $this->imageDir.$rootUrl;//адрес картинки на локальном компе
        $this->lookedDirs++;
        if($this->lookedDirs>ImageSiteReader::MAX_RECURSIVE_DIR) return;
        if($rootUrl!="" && is_dir($fullPath)===false && mkdir($fullPath)===false){
            throw new Exception("Не удается создать каталог {$fullPath}");
        }
        

        $pageContent  = file_get_contents($this->urlSite . $rootUrl);
        preg_match_all("/<a [^>]*href[^=>]*= *\"(\/[^\"]+)\"/",$pageContent,$matches);
        //загрузить картинки для каталога
        $this->parseSitePage($pageContent,$fullPath."/");
        //пройтись по вложенным каталогам
        foreach($matches[1] as $localCatalogs){
            preg_match_all("/\/([^\/]+)\//",$localCatalogs,$dirs);
            //var_dump($localCatalogs);
            //var_dump($dirs);
            $path = $this->imageDir;
            foreach($dirs[1] as $parentDir){//создание корневых путей, если их не было
                $path .= $parentDir . "/";
                if(is_dir($path)===false){//если нет корневой директории
                    if(mkdir($path)===false){
                        throw new Exception("Не удается создать каталог {$path}");
                    }
                }
            }
            
            $localCatalogs = mb_strcut($localCatalogs,1);//убираем /
            //var_dump($localCatalogs);
            if(array_search($localCatalogs,$this->visitedCatalogs)===false){//если каталог еще не посещался
                $this->readCatalogs($localCatalogs);
            }
        }
    }
}