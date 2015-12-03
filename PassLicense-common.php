<?php

/**
 * PassLicense: botclases.php based MediaWiki for semiautomated license review
 *
 *  (c) 2015 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 *  Warning: This script uses a modified version of botclasses.php
 *  You SHOULD use that modified version instead of the original one.
 *
 *  This script is still in developement, but works as desired.
 *  Any contributions is welcome.
 *
 **/
if(!defined('IN_PassLicense')) die();
define('TEMP_PATH',realpath(sys_get_temp_dir()));

require_once('class.php');

$site_url = parse_url($project);
$site_url = $site_url['scheme'].'://'.$site_url['host'].'/wiki/';

$wiki = new wiki($project);

if(isset($_GET['pass'])){

	$login = $wiki->login($user,$password);
	if($login['login']['result'] != 'Success') die('Not logged in. Check your user and password');

	if(empty($_POST['pagename'])) die('No data given');
	
	$pages = $_POST['pagename'];

	//var_dump($_POST);

	foreach($pages as $page){
		$page = str_replace(' ','_',urldecode($page));
		// Consider to use regular expressions. See $wiki->replacestring() for more information
		if(!empty($_POST['replaceg'])) $replace = $_POST['replaceg'];
		elseif(!empty($_POST['replace'][$page])) $replace = $_POST['replace'][$page];
		
		if(!empty($_POST['withg'])) $with = $_POST['withg'];
		elseif(!empty($_POST['with'][$page])) $replace = $_POST['with'][$page];
		
		if(!empty($_POST['regexg'])) $regex = true;
		elseif(!empty($_POST['regex'][$page])) $regex = true;
		
		$content = $wiki->replacestring($page,$replace,$with,$regex);
	
		$summary = 'License passed';
		$result[] = $wiki->edit($page,$content,$summary);
	}
	
	// This meanwhile I develop the page that contains the result.
	var_dump($result);
	echo '<p><a href="'.$_SERVER['PHP_SELF'].'">Return to home</a></p>';	
}else{
	if(!empty($_GET['category'])){
		$category = $_GET['category'];
		$categories = $wiki->categorymembers("Category:$category",5,false);
	}
	require_once('check_files.tpl.php');
}
?>