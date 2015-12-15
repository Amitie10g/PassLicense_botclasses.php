# PassLicense (botclasses.php)
Simple, general purpose PHP Web script to replace strings (with regex support) to mass replacement in MediaWiki pages. Intended to be used mainly for License review at Wikimedia Commons (by replacing tags from, eg. "{{review needed}}" with "{{review passed}}" at pages in File: namespace) for files that review by bots failed and require human review. Massive ammount of files could be reviewed and passed in minutes!

Contains code from the Chris G's Bot classes library. It uses the MediaWiki and external services API.

Warning: This tool is intended to be used only locally, and lacks of any authentication method by itself. For security reasons, DON'T expose it to Internet! or protect it with any authentication method supported by your Web server.

#Features:

* Listing File: members from a arbitary category, with a selection of categories for review
* Displaying page contents using the MediaWiki API, to improve performance at the client side
* Display information (picture preview, date and license) from external sources using their API, for faster reviews.
* API results caching, for (much, MUCH) greater performance and load times
* License Blacklist support, to hide files where external source uses licenses not allowed at Wiki

External services API support:

* Flickr
* Ipernity
* Picasa

Why not use Flinfo?

It is a good tool and I want to research and merge some code into PassLicense, but it is
not totally accurate and does not show the actual license at the source, based on the API.

#Requiriments:

* php 5.5 or above, with cURL and PCRE support

#To do:

* Add support for more services (Panoramio as priority)
* Add support for pages in large categories (the MediaWiki API make this very hard)

I can do this job alone, but any help is welcome.