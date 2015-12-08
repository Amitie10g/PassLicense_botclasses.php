# PassLicense (botclasses.php)
Simple, general purpose script to replace strings (with regex support) writen in PHP as a Web application,
to mass replacement in MediaWiki pages. Intended to be used mainly for License review at Wikimedia Commons
(by replacing tags from, eg. "{{review needed}}" with "{{review passed}}".

It uses the Chris G's Bot classes library, the MediaWiki and the Flickr API

Features:

* Listing members from a arbitary category, with a selection of categories for review
* Displaying page contents with fewer resources than viewing the full page
* Display information and licensing from external sources using external services API
* API results caching, for (much, MUCH) greater performance and load times
* License blacklist support. Files where the external source are not allowed
will not be displayed unless "show_blacklisted" is passed

Please report any problem or bugs to the Issues section.

To do:

* Add support for more services
* Improve the obtaing external information
* Add support for pages in large categories (the MediaWiki API make this very hard)