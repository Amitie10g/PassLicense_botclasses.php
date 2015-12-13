<?php
// Uncomment the following for benchmark porposes
//$timea = microtime();

error_reporting(E_ALL ^ E_WARNING & E_NOTICE);
define('IN_PassLicense',true);

// Edit the following as you need
$wiki_user = ''; // Wiki user
$wiki_password = ''; // Wiki password
$wiki_url = ''; // Wiki project and API URL (eg. https://commons.wikimedia.org/w/api.php)
$flickr_api_key = ''; // Required to interact with the Flickr API
$ipernity_api_key = ''; // Required to interact with the Ipernity API
$max_queries = 30; // Maximum queries to MediaWiki at once. This should be 30 for normal users, much more for bots

// CSS colours in hexadecimal notation (either 3 or 6 digits)
$color_body_bg = 'cef'; // fff
$color_details_1 = '06f'; // aaa
$color_details_2 = '5bf'; // ccc

require_once('PassLicense-web.php');

// Uncomment the following for benchmark porposes
//$timeb = microtime(true);
//$timec = $timeb-$timea;
//echo "Loading time: $timec";
?>