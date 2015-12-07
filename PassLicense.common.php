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

session_start();

$site_url = parse_url($project);
$site_url = $site_url['scheme'].'://'.$site_url['host'].'/wiki/';

$licenses_search = array('LicenseReview',
			 'Flickrreview',
			 'picasareview',
			 'Panoramioreview',
			 'OpenStreetMapreview',
			 'Indian navy');

$licenses_passed = array('{{subst:Lrw|<site>}}',
			 '{{subst:Frw}}',
			 '{{Cc-by-3.0-BollywoodHungama|status=confirmed|reviewer=~~~}}',
			 '{{picasareview|{{subst:REVISIONUSER}}|~~~~~}}',
			 '{{Panoramioreview|{{subst:REVISIONUSER}}|~~~~~}}',
			 '{{OpenStreetMapreview|{{subst:REVISIONUSER}}|{{subst:#time:Y-m-d}}}}',
			 '{{Indian navy|status=confirmed|reviewer=~~~}}',
			 '{{Cc-by-sa-3.0-FilmiTadka|passed|~~~}}');

$categories_review = array('License_review_needed',
			     'Flickr images needing human review',
			     'Filmitadka review needed',
			     'Fotopolska review needed',
			     'Files from Freesound.org lacking source',
			     'Images from HatenaFotolife needing License Review',
			     'Unreviewed photos from indiannavy.nic.in',
			     'Ipernity review needed',
			     'Unreviewed files from National Repository of Open Educational Resources',
			     'OpenPhoto review needed',
			     'Panoramio images needing human review',
			     'Lemill Web Albums files needing human review',
			     'Unreviewed files from Bollywood Hungama');

// The licenses ID not allowed in the wiki, according to
// https://www.flickr.com/services/api/explore/flickr.photos.licenses.getInfo
$flickr_licenses_blacklist = array(0,2,3,6,10);

require_once('PassLicense.class.php');
$wiki = new wiki($project);

if(isset($_GET['pass'])){

	$login = $wiki->login($user,$password);
	if($login['login']['result'] != 'Success') die('Not logged in. Check your user and password');

	if(empty($_POST['pagename'])) die('No data given');
	
	$pages = $_POST['pagename'];
	$category = $_POST['category'];
	
	foreach($pages as $page){
		if(!empty($_POST['replace_1'][$page])){
			$replace[1] = $_POST['replace_1'][$page];
			if(!empty($_POST['with_1'][$page])) $with[1] = $_POST['with_1'][$page];
			else $with[1] = null;
		}

		if(!empty($_POST['replace_2'][$page])){
			$replace[2] = $_POST['replace_2'][$page];
			if(!empty($_POST['with_2'][$page])) $with[2] = $_POST['with_2'][$page];
			else $with[2] = null;
		}

		if(!empty($_POST['replace_3'][$page])){
			$replace[3] = $_POST['replace_3'][$page];
			if(!empty($_POST['with_3'][$page])) $with[3] = $_POST['with_3'][$page];
			else $with[3] = null;
		}
		
		if(!empty($_POST['regex'][$page])) $regex = true;

		$page = str_replace(' ','_',urldecode($page));

		$content = $wiki->replacestring($page,$replace,$with,$regex);

		$summary = 'License passed';
		$result[] = $wiki->edit($page,$content,$summary);
	}

	// This meanwhile I develop the page that contains the result.
	$_SESSION['result'] = $result;
	header('Location: '.$_SERVER['PHP_SELF']."?category=$category");
	die();	
}else{
	if(!empty($_GET['category'])){
		$category = $_GET['category'];
		$categories = $wiki->categorymembers("Category:$category",250);
	}	
	require_once('PassLicense.tpl.php');
	unset($_SESSION['result']);
}
?>