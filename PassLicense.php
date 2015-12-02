<?php
error_reporting(E_ALL ^ E_WARNING & E_NOTICE);
define('IN_PassLicense',true);

// Edit the following as you need
$user = '';
$password = '';
$project = ''; // https://commons.wikimedia.org/w/api.php

// Default tag replacement. Please consider to use regular expressions. See bellow
$replace = '';
$with = '';

require_once('PassLicense-common.php');
?>