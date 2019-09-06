# Mission statement
Ever wanted to see the most interesting news of the year in pictures selected by editors of one of the best news agencies in world? 
That's what this project is all about: The World Press Exhibition at home.

## Background
The NOS, also known as the Dutch Broadcast Foundation, is one of the broadcasting organizations making up the Netherlands Public Broadcasting system. They have a public API and provide access to a selected set of daily news photos in high resolution. We use this API to fetch and store the photos among with some metadata such as the title / event that comes with the photo. Over time we build a quiet big archive of photos that you can use to impress your familiy a home. In our case we use a slideshow player in an infinate loop going through the archive on a picture frame device.

![NOS 4 september](/originals/example1.jpg)

## Minimal Requirements
* A system running PHP version 7.2+ and your system should be able of running a cron job.
* Storage for the photos, about 10Mb per day.
* Optional: A picture frame device. 

## Limitations
* Only the most interesting photos of the day are provided via the NOS API, no historical data is available. It takes time to build a historical archive yourselve.
* There is no technical documentation available online about the NOS API at the time of writting.

## Quick start instructions
1. Simply clone this project on a computer running using:
`git clone `
1. Run the nos.php file with:
`php nos.jpg`.
1. A folder 'nosjpg' will be created automatically and the latest photos are being retrieved automatically. 
1. (Optional): Add a line to your crontab file to update your local archive over time: 
`0 * * * * root cd /var/www/vhosts/schildwacht.com/nos && /usr/bin/php nos.php >/dev/null 2>&1`

## What the code does
The code retrieves the available data via the API https://public-api.nos.nl/feed/nieuws-in-beeld.json. 
Next the json is parsed and only the highest resolution of a photo is being selected. We noticed that the original photos are sometimes of lower resolution and scaled to a higher format by the NOS. For our purposes it doesn't matter as the majority is of high resolution nowadays anyway.
The image filename is set to the original description that came with the photo via the API. This way it's easy to browse through the image library and see instantly what it is about.

## How to contribute
Please open a pull request if you have additions to the project.
If you care about the original meta data (and others) feel free to store the JSON data in a file or database yourself and it would be great if you open a pull request. 

## Acknowledgements
These professionals have contributed valuable insights and code:
- Dennis van der Hammen - TIG

## Frequently Asked Questions (FAQ)
1. I only see a couple of photos when I run the code for the first time, is that correct?
Yes. Only the latest photos are download as the there is no complete archive available.

## Need help?
Open an issue on this repository via Github and follow the issue template questions.

## License
The information and code of this repository is provided free of charge for personal use, without warranty or assumed liability of any kind. It is not allowed to use or include this data in commercial products or offerings.
