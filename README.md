# PassLicense (botclasses.php)
Simple, general purpose script to replace strings (with regex support) writen in PHP as a Web application,
to mass replacement in MediaWiki pages. Intended to be used mainly for License review at Wikimedia Commons
(by replacing tags from, eg. "{{review needed}}" with "{{review passed}}", in pages in File: namespace.

Contains code from the Chris G's Bot classes library.
It uses the MediaWiki, Flickr and Ipernity API. You need an API Key to use external services API.

Features:

* Listing members from a arbitary category, with a selection of categories for review
* Displaying page contents with fewer resources than viewing the full page
* Display information and licensing from external sources using their API, for faster reviews.
* Show the file directly from the external service, to compare with the file at Wiki (using external services API).
* API results caching, for (much, MUCH) greater performance and load times
* License Blacklist support, to hide files where external source uses licenses not allowed

External services API support:

* Flickr
* Ipernity

To do:

* Add support for more services (I want support for Picasa and Panoramio as priority)
* Improve the obtaing external information (it is almost done)
* Add support for pages in large categories (the MediaWiki API make this very hard)

Why not use Flinfo?

It is a good tool and I want to investigate and merge some code into PassLicense, but is not
totally accurate and does not show the actual license in the source, based on the API.

Please report any problem or bugs to the Issues section.