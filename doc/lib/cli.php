<?php

/**
 * UploadFile: botclases.php based MediaWiki file uploader - CLI mode script
 *
 *  (c) 2015 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 *  This program is free software, and you are welcome to redistribute it under
 *  certain conditions. This program comes with ABSOLUTELY NO WARRANTY.
 *  see README.md and COPYING for more information
 *
 **/
 
// Check if SAPI is CLI
if(php_sapi_name() != "cli") die("\nThis script should be executed from CLI.\n");

define('TEMP_PATH',realpath(sys_get_temp_dir()));

require_once('class.php');

// Declare the arguments to be taken from command line. User and Password may be received
// from the arguments --user and --password for convenience, but them can also
// be hardcoded (see bellow)
$shortopts  = "";
$longopts   = array("user::","password::","project::","filelist::","help::","license::");
$options    = getopt($shortopts, $longopts);

if(empty($user))     $user     = $options['user'];
if(empty($password)) $password = $options['password'];
if(empty($project))  $project  = $options['project'];
$filelist                      = $options['filelist'];
$help                          = $options['help'];
$license                       = $options['license'];

// Declare the Help and License text
$help_text = <<<EOH

$bs::: Davod Uploader (botclasses.php)  Copyright (C) 2015  Davod (Amitie 10g) :::$be

This simple script  allows to upload one or multiple files to Wikimedia Commons
or any wiki of your choice, determined by the  Arguments and filelist.ini file.

Also,  this script checks if the file already exists in the Wiki,  and then the
existing files will be skipped.
$bs
Parameters:

   --user$be     Your Wiki username (hardcoded by default)

   $bs--password$be Your Wiki password (hardcoded by default)

   $bs--project$be  Your Wiki projet where you  will upload your file(s),  with the
	      "http(s)://"  prefix.  This parameter is optional;  the default
	      value is "https://commons.wikimedia.org"
	    
   $bs--filelist$be The file list in csv format, by default "filelist.csv".
	      Fields are the following, separated with semicolon (";"):

	      ###############################################################
	      #    "Filename";"Pagename";"Description";"Date";"Source";     #
	      #    "Author";"Optional";"License";"Categories"               #
	      ###############################################################
	      Characters in each field$bs should$be be properly escaped.
		
   $bs--help$be     Show this help

   $bs--license$be  Show the license of this program

With no parameters, the script will parse './filelist.ini' by default.

See README.md for detailed information about its usage.


EOH;

$license_text = <<<EOL
$bs
Davod Uploader (botclasses.php)  Copyright (C) 2015  Davod (Amitie 10g)$be

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


EOL;

// Output the Help and License as requested with --help and --license
if(isset($help)) die($help_text);
if(isset($license)) die($license_text);
	
// If --filelist is not set, provide one by default
if(!isset($filelist)) $filelist = 'filelist.csv';

if($filelist == 'stdin') $handler = @fopen('php://stdin','r') or die("Error: No valid fileist data found\n");
else $handler = @fopen($filelist,'r') or die("\nError: No valid filelist data found\n");
stream_set_blocking($handler,1);

// Call the class Commons and login in
$wiki      = new Uploader;
$wiki->url = $project;
$wiki->setUserAgent('User-Agent: FooBot (http://mediawiki.org/wiki/User:FooBot)');

$wiki->login($user,$password);
	
while (($data = fgetcsv($handler, 2097152, ";","'")) !== FALSE){

	// Get the values from the CSV
	$filename    = realpath($data[0]);
	$pagename    = $data[1];
	$description = str_replace('\n',"\n",stripcslashes($data[2]));
	$date        = $data[3];
	$source      = stripcslashes($data[4]);
	$author      = stripcslashes($data[5]);
	$optional    = stripcslashes($data[6]);
	$license     = stripcslashes($data[7]);
	$categories  = stripcslashes($data[8]);
	$categories .= "\n[[Category:Files uploaded with Davod Uploader]]";
	$summary     = stripcslashes($data[9]);
		
	var_dump($wiki->upload($filename,$pagename,$description,$date,$source,$author,$optional,$license,$categories,$summary));
}
	
fclose($handler);

die();

?>