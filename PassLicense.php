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
 **/

// Set the Error reporting if you need
//error_reporting(E_ERROR | E_WARNING | E_PARSE /*| E_NOTICE */);
error_reporting(E_ALL ^ E_WARNING & E_NOTICE);

$user = '';
$password = '';
$project = '';

$replace = '';
$with = '';

require_once('botclasses.php');

$wiki = new wiki($project);

define('IN_PassLicense',true);
define('TEMP_PATH',realpath(sys_get_temp_dir()));

if(isset($_GET['pass'])){

	$login = $wiki->login($user,$password);
	if($login['login']['result'] != 'Success') die('Not logged in. Check your user and password');

	if(empty($_POST['pagename'])) die('No data given');
	
	$pages = $_POST['pagename'];

	if(!empty($_POST['replace'])) $replace = $_POST['replace'];
	if(!empty($_POST['with'])) $with = $_POST['with'];
	
	foreach($pages as $page){
		$page = str_replace(' ','_',urldecode($page));
		$content = $wiki->replacestring($page,$replace,$with);
		$summary = 'License passed';
		$result = $wiki->edit($page,$content,$summary);
		var_dump($result);
	}
}else{
	$category = $_GET['category'];
	$categories = $wiki->categorymembers("Category:$category",50,false);
	require_once('check_files.tpl.php');
}
?>