<?php
error_reporting(E_ALL ^ E_WARNING & E_NOTICE);
define('IN_PassLicense',true);

// Edit the following as you need
$user = '';
$password = '';
$project = ''; // eg. https://commons.wikimedia.org/w/api.php
$flickr_api_key = ''; // Required to interact with the Flickr API

require_once('PassLicense.common.php');
?>