<?php

namespace Schldwcht\Newsinpictures;

use mysql_xdevapi\Exception;
use Schldwcht\Newsinpictures\Service\Slug;

require_once('Service/Slug.php');

class NosImageData
{
    private $saveDir           = './news-in-pictures/';
    private $apiUrl            = "https://public-api.nos.nl/feed/nieuws-in-beeld.json";
    private $minimumResolution = 3000; // minimum resolution in width of images to retrieve, in pixels
    private $fileUrl;
    private $imageName;
    private $imageIPTCData;
    private $elementsArray;

    public function getJsonData()
    {
        $json                = file_get_contents($this->apiUrl);
        $this->elementsArray = json_decode($json, true);
    }

    public function getElements()
    {
        foreach ($this->elementsArray as $newsItem) {
            echo "> Scanning id " . $newsItem['id'] . "\n";
            $this->getImageName($newsItem);
        }
    }

    /**
     * @param $rec
     * @param $data
     * @param $value
     *
     * @return string
     */
    public function makeIPTCTags($rec, $data, $value)
    {
        $valueLength = strlen($value);
        $returnValue = chr(0x1C) . chr($rec) . chr($data);

        if ($valueLength < 0x8000) {
            $returnValue .= chr($valueLength >> 8) . chr($valueLength & 0xFF);

            return $returnValue . $value;
        }

        $returnValue .= chr(0x80) .
                        chr(0x04) .
                        chr(($valueLength >> 24) & 0xFF) .
                        chr(($valueLength >> 16) & 0xFF) .
                        chr(($valueLength >> 8) & 0xFF) .
                        chr($valueLength & 0xFF);

        return $returnValue . $value;
    }

    /**
     * @param $newsItem
     */
    public function setIPTCTags($newsItem)
    {
        // See https://www.iptc.org/std/photometadata/specification/IPTC-PhotoMetadata, set the IPTC tags
        $IPTCArray = array(
            '2#80'  => 'https://github.com/schldwcht',
            '2#05'  => $newsItem['title'],
            '2#116' => $newsItem['copyright'],
            '2#120' => $newsItem['description']
        );

        // Convert the IPTC tags into binary code, starting with unicode (UTF-8) text in IPTC fields
        $utf8seq             = chr(0x1b) . chr(0x25) . chr(0x47);
        $length              = strlen($utf8seq);
        $this->imageIPTCData = chr(0x1C) . chr(1) . chr('090') . chr($length >> 8) . chr($length & 0xFF) . $utf8seq;

        foreach ($IPTCArray as $tag => $string) {
            $tag                 = substr($tag, 2);
            $this->imageIPTCData .= $this->makeIPTCTags(2, $tag, $string);
        }
    }

    /**
     * @param $newsItem
     */
    public function getImageName($newsItem)
    {
        $imageElement = array_pop($newsItem['aspect_ratios'][0]['formats']);

        if ($imageElement['width'] < $this->minimumResolution) {
            return;
        }
        $descSlug        = Slug::createSlug($newsItem['description']);
        $this->fileUrl   = $imageElement['url']['jpg'];
        $this->imageName = $newsItem['id'] . '-' . substr($descSlug, 0,
                200) . '-' . basename($this->fileUrl); // createSlug fixes issue with OS filename conventions

        $this->setIptcTags($newsItem); // in order to store meta info about the photo in the image, prepare the Iptc tags
        $this->saveImageName();
    }

    public function embedIPTCData()
    {
        // Embed the IPTC data
        $imageContent = IPTCembed($this->imageIPTCData, $this->saveDir . $this->imageName);

        // Write the new image data out to the file.
        $fp = fopen($this->saveDir . $this->imageName, "wb");
        fwrite($fp, $imageContent);
        fclose($fp);
    }

    public function retrieveImage()
    {
        $imageData = $this->getImage($this->fileUrl);
        if ($imageData) {
            echo "+ Saving " . $this->imageName . "\n";
            file_put_contents(basename($this->saveDir) . "/" . $this->imageName, $imageData);
            $this->embedIPTCData();
        }
    }

    public function saveImageName()
    {
        if (is_dir(basename($this->saveDir)) === false) {
            mkdir(basename($this->saveDir));
            echo "* Creating directory " . $this->saveDir . "\n";
        }

        if ((file_exists($this->saveDir . $this->imageName) === false)
            || (filesize($this->saveDir . $this->imageName) == 0)) {
            //check whether the file is saved in a previous run.
            $this->retrieveImage();
        }
    }

    /**
     * @param $url
     *
     * @return bool|string
     */
    private function getImage($url)
    {
        $headers[]  = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
        $headers[]  = 'Connection: Keep-Alive';
        $headers[]  = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $user_agent = 'php';
        $process    = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_USERAGENT, $user_agent); //check here
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($process);
        curl_close($process);

        return $return;
    }
}

if (!extension_loaded('mbstring')) {
    throw new Exception("* Cannot start, php-mbstring missing");
}

echo "* Start" . "\n";

$obj = new NosImageData;
$obj->getJsonData();
$obj->getElements();

echo "* Done" . "\n";
