# Mission statement
Ever wanted to see the most interesting news of the year in pictures selected by editors of one of the best news agencies in world?
That's what this project is all about: A private World Press Exhibition at home.

## Background
The NOS https://nos.nl/, also known as the Dutch Broadcast Foundation, is one of the broadcasting organizations making up the Netherlands Public Broadcasting system. They have a public API and provide access to a selected set of daily news photos in high resolution. We use this API to fetch and store the photos among with some metadata such as the title / event that comes with the photo. Over time, we build a quiet big archive of photos that you can use to impress your family a home. In our case we use a slideshow player in an infinite loop going through the archive on a digital picture frame.

![NOS 4 september](/originals/example1.jpg)

## Quick start instructions
1. Simply clone this project on a computer running using:
```
git clone <repository>
cd <directory repository>
```
1. Run the nos.php file with:
```
php nos.php
```
1. A sub directory 'news-in-pictures' will be created for the first time and the latest photos are being retrieved automatically.

You should see an output similar like:
```
* Start
> Scanning id 575809
+ Saving [filename]
...
* Done
```

1. (Optional): Add a line to your crontab file to update your local archive, we have set it to every hour:
```
0 * * * * root cd /var/www/vhosts/nos.nl/nos && /usr/bin/php nos.php >/dev/null 2>&1
```

## What the code does
The code retrieve the available data via the API https://public-api.nos.nl/feed/nieuws-in-beeld.json.
Next the json is parsed and only the highest resolution of a photo is being selected. We noticed that the original photos are sometimes of lower resolution and scaled to a higher format by the NOS. For our purposes it doesn't matter as the majority is of high resolution nowadays anyway.
The image filename is set to the original description that came with the photo via the API. This way it's easy to browse through the image library and see instantly what it is about.

## Minimal requirements
* A system running PHP version 7.2+
* Storage for the photos, about 10Mb per day
* Optional: A picture frame device with a slideshow function, a Raspberry PI can be used for this as well in combination with a monitor. We bought an 8-inch monitor on https://www.beetronics.nl/8-inch-monitor (not cheap, but better than the ones we ordered via Amazon).

## Known limitations
* Only the most interesting photos of the day are provided via the NOS API, no historical data is available. It takes time to build a historical archive yourself.
* There is no technical documentation available online about the NOS API at the time of writing.

## How to contribute
Please open a pull request if you have additions to the project.
1. An item on the road map is to retrieve all photos on one day published by the NOS regardless of the selected ones. There is no known endpoint for that, so based on the id numbers using the nieuws-in-beeld.json it would be able to guess the missing numbers and retrieve the 'missing' photos. The filename has a known pattern:
```
https://nos.nl/data/image/2019/09/07/575618/3840x2160a.jpg
```
The number 575618 is the id number of the photo in this example. The filename '3840x2160a.jpg' is always the same (for this resolution).
1. If you care about the original metadata feel free to store the JSON data in a file or database yourself and it would be great if you open a pull request.

## Acknowledgments
These professionals have contributed valuable insights and program code:
- Dennis van der Hammen - TIG

## Frequently Asked Questions (FAQ)
1. I only see a couple of photos when I run the code for the first time, is that correct?

_Yes. Only the latest photos are download as the there is no complete archive available._

## Need help?
Open an issue on this repository via GitHub and follow the issue template questions.

## License
The information and code of this repository is provided free of charge for personal use, without warranty or assumed liability of any kind. It is not allowed to use or include this data in commercial products or offerings.
