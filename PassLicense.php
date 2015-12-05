<?php
error_reporting(E_ALL ^ E_WARNING & E_NOTICE);
define('IN_PassLicense',true);

// Edit the following as you need
$user = '';
$password = '';
$project = ''; // eg. https://commons.wikimedia.org/w/api.php

require_once('PassLicense.common.php');
?>