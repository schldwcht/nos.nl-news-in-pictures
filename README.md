 # nos-news-in-pictures

## Background
It's nice to have a overview of the news in the world not in words but in pictures. The NOS API makes it possible to get exactly this. 

## Requirements
* PHP version 7.2
* Cronjob
* Storage (About 10Mb per day).

## Limitations
* Only the latest headline photos are provided via the NOS API, no historical data is available.

## Installation instructions
1. Simply run the nos.php file and a folder nosjpg will be created automatically, storing the latest photos of todays headlines.
1. Add to the crontab a line such as: 
`* * * * * root cd /var/www/vhosts/schildwacht.com/nos && /usr/bin/php nos.php >/dev/null 2>&1`

## How it works
The code retrieves the available data via the API https://public-api.nos.nl/feed/nieuws-in-beeld.json. 
Next the json is parsed and only the highest resolution of a photo is being selected. I noted that the original photos are sometimes of lower resolution and scaled to a higher format. For my purpose it doesn't matter as the majority is of 4k resolution nowadays.
The image filename is set to the original description that came with the photo via the API. This way it's easy to browse through the image library and see instantly what it is about.

If you care about the original meta data (and others) feel free to store that into a file or database yourself via the API. 
