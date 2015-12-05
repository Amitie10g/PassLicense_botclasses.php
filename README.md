# PassLicense (botclasses.php)
Simple, general purpose script to replace strings (with regex support) writen in PHP as a Web application,
to mass replacement in MediaWiki pages. Intended to be used mainly for License review at Wikimedia Commons
(by replacing tags from, eg. "{{review needed}}" with "{{review passed}}".

How it works?
It (tries to) obtains all the template tags (anything between "{{}}"), and a series of alternatives to replace.
Regardless the tags got an the alternatives, it allows to replace any string with another one.

It uses the Chris G's Bot classes library.
