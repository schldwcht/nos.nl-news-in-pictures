# Project mission statement
Ever wanted to see the most interesting news of the year in pictures selected by editors of one of the best news agencies in world?
That's what this project is all about: A glance on what's happening in the world through your picture frame at home.

## Background
The NOS https://nos.nl, also known as the Dutch Broadcast Foundation, is one of the broadcasting organizations making up the Netherlands Public Broadcasting system. They have a public API and provide access to a selected set of daily news photos in high resolution. We use this API to fetch and store the photos among with some metadata such as the title / event that comes with the photo. Over time, we've build a quiet big archive of photos that you can use to impress your family. In our case we use a slideshow player in an infinite loop going through the archive on a digital picture frame.

![NOS 4 september](/originals/example1.jpg)

## Quick start instructions
1. Simply clone this project in a terminal on a computer with Git:
```
git clone git@github.com:schldwcht/nos.nl-news-in-pictures.git
cd nos.nl-news-in-pictures
```
2. Run the nos.php file with:
```
php nos.php
```
3. When running this app for the first time, a sub directory 'news-in-pictures' will be created. The latest photos will be retrieved automatically.

You should see an output similar like:
```
* Start
> Scanning id 575809
+ Saving [filename]
...
* Done
```

4. (Optional): Add a line to your crontab file to update your local archive. By default it is set to every hour:
```
0 * * * * root cd /var/www/vhosts/nos.nl/nos && /usr/bin/php nos.php >/dev/null 2>&1
```
5. (Optional): If you want to save lower resolution images, you can change the property ``` $minimumResolution ``` in ``` nos.php ```. The default is ``` 3000 ``` pixels or more. 

## What the app does on technical level
The app retrieves the available data via the API https://public-api.nos.nl/feed/nieuws-in-beeld.json.
Next the JSON is parsed and only the highest resolution of a photo is being selected. We noticed that the original photos are sometimes of lower resolution and scaled to a higher format by the NOS. For our purposes it doesn't matter as the majority is of high resolution nowadays anyway.
The image filename is set to the original description that came with the photo via the API. This way it's easy to browse through the image library and see the subject instantly. The title, description and copyright data that came with the JSON is added to the images as well via the [IPTC](https://iptc.org) header in the jpg.

## Minimal requirements
* A system running PHP version 7.2+
* php-mbstring enabled
* Storage for the photos, about 10Mb per day
* Optional: A picture frame device with a slideshow function, a Raspberry PI can be used for this as well in combination with a monitor. We bought an 8-inch monitor on https://www.beetronics.nl/8-inch-monitor (not cheap, but better than the ones we ordered via Amazon).

## Known limitations
* Only the most interesting photos of the day are provided via the NOS API, no historical data is available. It takes time to build a historical archive yourself.
* There is no documentation available about the endpoint https://public-api.nos.nl at the time of writing.

## How to contribute
It would be awesome if you contribute to this project. Please follow the code of conduct https://www.contributor-covenant.org/version/1/4/code-of-conduct when doing so.
The coding standard for this project is to use the Object Calisthenics rules, see https://github.com/object-calisthenics/phpcs-calisthenics-rules. Open a pull request if you have additions to the project.
Where you can help:
* An item on the road map is to retrieve all photos on one day published by the NOS regardless of the selected ones. There is no known endpoint for that, so based on the id numbers using the nieuws-in-beeld.json it would be able to guess the missing numbers and retrieve the 'missing' photos. The filename has a known pattern:
```
https://nos.nl/data/image/2019/09/07/575618/3840x2160a.jpg
```
The number 575618 is the id number of the photo in this example. The filename '3840x2160a.jpg' is always the same (for this resolution).
* The original metadata could be stored in a file or database and it would be great if you open a pull request for that.

## Acknowledgments
These professionals have contributed valuable insights and program code:
- [Dennis van der Hammen - TIG](https://github.com/tig-dennisvanderhammen)
- [Jeffrey Peeters - TIG](https://github.com/tig-jeffreypeeters)

## Frequently Asked Questions (FAQ)
I only see a couple of photos when I run the code for the first time, is that correct?
_Yes. Only the latest photos are download as the there is no complete archive available._

## Need help?
Open an issue via GitHub and follow the issue template steps.

___
The information and code of this repository is provided free of charge for personal use, without warranty or assumed liability of any kind. The example photo used in this README.md is copyright by https://github.com/schldwcht.
