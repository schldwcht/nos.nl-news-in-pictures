<?php

class NosImageData
{
    private $saveDir = './nosjpg/';
    private $apiUrl  = "https://public-api.nos.nl/feed/nieuws-in-beeld.json";
    private $fileUrl;
    private $imageName;
    private $imageIPTCData;
    private $elementsArray;

    /**
     * @param string $text
     *
     * @return string
     *
     */
    private function cleanString($text)
    {
        $utf8 = array(
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u'  => 'A',
            '/[ÍÌÎÏ]/u'   => 'I',
            '/[íìîï]/u'   => 'i',
            '/[éèêë]/u'   => 'e',
            '/[ÉÈÊË]/u'   => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u'  => 'O',
            '/[úùûü]/u'   => 'u',
            '/[ÚÙÛÜ]/u'   => 'U',
            '/ç/'         => 'c',
            '/Ç/'         => 'C',
            '/ñ/'         => 'n',
            '/Ñ/'         => 'N',
            '/–/'         => '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'  => ' ', // Literally a single quote
            '/[“”«»„]/u'  => ' ', // Double quote
            '/ /'         => ' ', // nonbreaking space (equiv. to 0x160)
        );

        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }

    /**
     * @param $slug
     *
     * @return string
     */
    private function createSlug($slug)
    {
        $slug                        = $this->cleanString($slug);
        $lettersNumbersSpacesHyphens = '/[^\-\s\pN\pL]+/u';
        $spacesDuplicateHypens       = '/[\-\s]+/';

        $slug = preg_replace($lettersNumbersSpacesHyphens, '', $slug);
        $slug = preg_replace($spacesDuplicateHypens, '-', $slug);

        $slug = trim($slug, '-');

        return mb_strtolower($slug, 'UTF-8');
    }

    /**
     *
     */
    public function getJsonData()
    {
        $json                = file_get_contents($this->apiUrl);
        $this->elementsArray = json_decode($json, true);
    }

    /**
     *
     */
    public function getElements()
    {
        foreach ($this->elementsArray as $newsItem) {
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

        if ($imageElement['width'] < 3000) {
            return;
        }
        $this->fileUrl   = $imageElement['url']['jpg'];
        $this->imageName = $newsItem['id'] . '-' . substr($this->createSlug($newsItem['description']),
                0,
                200) . '-' . basename($this->fileUrl); // createSlug fixes issue with OS filename conventions

        $this->setIptcTags($newsItem); // in order to store meta info about the photo in the image, prepare the Iptc tags
        $this->saveImageName();
    }

    /**
     *
     */
    public function embedIPTCData()
    {
        if (extension_loaded('gd')) {
            // Embed the IPTC data
            $imageContent = IPTCembed($this->imageIPTCData, $this->saveDir . $this->imageName);

            // Write the new image data out to the file.
            $fp = fopen($this->saveDir . $this->imageName, "wb");
            fwrite($fp, $imageContent);
            fclose($fp);
        }
    }

    /**
     *
     */
    public function saveImageName()
    {
        if (is_dir(basename($this->saveDir)) === false) {
            mkdir(basename($this->saveDir));
            echo "* creating directory " . $this->saveDir;
        }

        if ((file_exists($this->saveDir . $this->imageName) === false) || (filesize($this->saveDir . $this->imageName) == 0)) {
            //check whether the file is saved in a previous run.
            $imageData = $this->getImage($this->fileUrl);
            if ($imageData) {
                file_put_contents(basename($this->saveDir) . "/" . $this->imageName, $imageData);
                $this->embedIPTCData();
            }
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

$obj = new NosImageData;
$obj->getJsonData();
$obj->getElements();
