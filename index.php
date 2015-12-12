<?php
// Uncomment the following for benchmark porposes
//$timea = microtime();

error_reporting(E_ALL ^ E_WARNING & E_NOTICE);
define('IN_PassLicense',true);

// Edit the following as you need
$user = '';
$password = '';
$project = ''; // eg. https://commons.wikimedia.org/w/api.php
$flickr_api_key = ''; // Required to interact with the Flickr API
$ipernity_api_key = ''; // Required to interact with the Ipernity API
$max_queries = 30; // Maximum queries to MediaWiki at once. This should be 30 for normal users, much more for bots

require_once('PassLicense.common.php');

// Uncomment the following for benchmark porposes
//$timeb = microtime(true);
//$timec = $timeb-$timea;
//echo "Loading time: $timec";
?>