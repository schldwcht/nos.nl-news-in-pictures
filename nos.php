<?php
/* process images from the NOS news feed

https://public-api.nos.nl/feed/nieuws-in-beeld.json"
*/
/*
[{"id":"482691","title":"In vuur en vlam","description":"Volle maan schijnt boven een brandend bos in het Britse Saddleworth ","copyright":"Getty Images","formats":[{"width":128,"height":72,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/128x72.jpg"}},{"width":250,"height":141,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/s.jpg"}},{"width":320,"height":180,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/320x180.jpg"}},{"width":480,"height":270,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/480x270.jpg"}},{"width":640,"height":360,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/640x360.jpg"}},{"width":800,"height":450,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/xxl.jpg"}},{"width":1008,"height":567,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/1008x567.jpg"}},{"width":1200,"height":675,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/1200x675.jpg"}},{"width":1600,"height":900,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/1600x900.jpg"}},{"width":2048,"height":1152,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482691\/2048x1152.jpg"}}]},{"id":"482673","title":"Warm welkom ","description":"Een muzikaal welkom voor koningin Máxima in Zevenaar. De koningin kwam daar om de ondertekening bij te wonen van een samenwerkingsconvenant over muziekonderwijs op basisscholen ","copyright":"ANP","formats":[{"width":128,"height":72,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482673\/128x72.jpg"}},{"width":250,"height":141,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482673\/s.jpg"}},{"width":320,"height":180,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482673\/320x180.jpg"}},{"width":480,"height":270,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482673\/480x270.jpg"}},{"width":640,"height":360,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482673\/640x360.jpg"}},{"width":800,"height":450,"url":{"jpg":"https:\/\/nos.nl\/data\/image\/2018\/06\/27\/482673\/xxl.jpg"}},{"width":1008,"height":567,"url":
*/

function getimg($url)
{
    $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
    $headers[] = 'Connection: Keep-Alive';
    $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
    $user_agent = 'php';
    $process = curl_init($url);
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

function cleanString($text)
{
    $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',
        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
        '/[“”«»„]/u'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}

function createSlug($slug)
{
    $slug = cleanString($slug);
    $lettersNumbersSpacesHyphens = '/[^\-\s\pN\pL]+/u';
    $spacesDuplicateHypens = '/[\-\s]+/';

    $slug = preg_replace($lettersNumbersSpacesHyphens, '', $slug);
    $slug = preg_replace($spacesDuplicateHypens, '-', $slug);

    $slug = trim($slug, '-');

    return mb_strtolower($slug, 'UTF-8');
}

$json = file_get_contents("https://public-api.nos.nl/feed/nieuws-in-beeld.json");
$json_data = json_decode($json, true);
$next = false;

foreach ($json_data as $key1 => $value1) {
    //if($json_data[$key1]["formats"]["width"] > 1600) {
    //	}
    //print print_r($value1, true).'<br>';

    foreach ($value1 as $key2 => $value2) {
        //print print_r($value2, true).'<br>';
        if ($key2=="id") {
            $nos_id = $value2;
        }//echo $key2;
        if ($key2=="title") {
            $nos_title = $value2;
        }
        if ($key2=="description") {
            $nos_description = $value2;
        }
        if ($key2=="aspect_ratios") {
            foreach ($value2 as $key3 => $value3) {
                foreach ($value3 as $key4 => $value4) {
                    //echo '>'.$value3.'<br>'; //
                    if ($key4=="formats") {
                        foreach ($value4 as $key5 => $value5) {
                            foreach ($value5 as $key6 => $value6) {
                                if ($key6=="width") {
                                    if ($value6 >= 3840) {
                                        //echo "-------> ".$value6." <------- <br>";
                                        $next = true;
                                    } else {
                                        $next = false;
                                    }
                                }
                                //echo $key6." - ";
                                if ($key6=="url" && $next == true) {
                                    foreach ($value6 as $key7 => $value7) {
                                        if ($key7=="jpg") {
                                            $nos_jpg = $value7;
                                    
                                            echo $nos_id.'<br>\n';
                                            echo $nos_title.'<br>\n';
                                            echo $nos_description.'<br>\n';
                                            echo $nos_jpg.'<br>\n';
                                
                                            $imgurl = $nos_jpg;
                                            //$imagename= $nos_id.'-'.str_replace(' ', '', $nos_title).'-'.basename($imgurl);
                                            $imagename= $nos_id.'-'.substr(createSlug($nos_description), 0, 200).'-'.basename($imgurl);
                                    
                                            if (!file_exists('./nosjpg/'.$imagename)) {
                                                $image = getimg($imgurl);
                                                file_put_contents('nosjpg/'.$imagename, $image);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /*
    $keyz=array_keys($value1);
    $i=1;
    foreach($keyz as $vall)
    {	echo "element $i = $vall<br />";
    $i++;
    //print print_r($value1, true).'<br>';
    }
    */
}
