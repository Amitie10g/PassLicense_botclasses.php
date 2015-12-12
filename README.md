# PassLicense (botclasses.php)
Simple, general purpose PHP Web script to replace strings (with regex support) to mass replacement in MediaWiki pages.
Intended to be used mainly for License review at Wikimedia Commons (by replacing tags from, eg. "{{review needed}}"
with "{{review passed}}", in pages in File: namespace.

Contains code from the Chris G's Bot classes library.
It uses the MediaWiki, Flickr and Ipernity API. You need an API Key to use external services API.

Warning: This tool is intended to be used only locally, and lacks of any authentication method itself.
         For security reasons, DON'T expose it to Internet! or protect it with any authentication method
	 supported by your Web server.

#Features:

* Listing members from a arbitary category, with a selection of categories for review
* Displaying page contents with fewer resources than viewing the full page
* Display information and licensing from external sources using their API, for faster reviews.
* Show the file directly from the external service, to compare with the file at Wiki (using external services API).
* API results caching, for (much, MUCH) greater performance and load times
* License Blacklist support, to hide files where external source uses licenses not allowed

External services API support:

* Flickr
* Ipernity
* Picasa

Why not use Flinfo?

It is a good tool and I want to research and merge some code into PassLicense, but it is
not totally accurate and does not show the actual license at the source, based on the API.

#Requiriments:

* php 5.5 or above, with cURL and PCRE support
* A valid account at your Wiki (better if that account have bot flag)
* Flickr and Ipernity API keys, to interact with these services (not mandatory but dessirable).
  You can obtain them from these sites at no cost

#To do:

* Add support for more services (Panoramio as priority)
* Add support for pages in large categories (the MediaWiki API make this very hard)

I can do this job alone, but any help is welcome.