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

set_time_limit(300);

session_start();

$wiki_url_headers = get_headers($wiki_url);
if($wiki_url_headers[0] == 'HTTP/1.1 200 OK'){
	// License tags to search inside the pages
	$licenses_search = array('LicenseReview',
				 'Flickrreview',
				 'Ipernityreview',
				 'Picasareview',
				 'Panoramioreview',
				 'OpenStreetMapreview',
				 'Indian navy');

	// License tags for replace
	$licenses_replace = array('{{subst:Lrw|site=<site>}}',
				 '{{subst:Frw}}',
				 '{{Ipernityreview|{{subst:REVISIONUSER}}|~~~~~}}',
				 '{{Picasareview|{{subst:REVISIONUSER}}|~~~~~}}',
				 '{{Panoramioreview|{{subst:REVISIONUSER}}|~~~~~}}',
				 '{{OpenStreetMapreview|{{subst:REVISIONUSER}}|~~~~~}}',
				 '{{Indian navy|status=confirmed|reviewer=~~~}}',
				 '{{Cc-by-sa-3.0-FilmiTadka|passed|~~~}}',
				 '{{Cc-by-3.0-BollywoodHungama|status=confirmed|reviewer=~~~}}');

	// Categories to list (without the Category: prefix)
	$categories_review = array('License_review_needed',
				   'Flickr images needing human review',
				   'Picasa Web Albums files needing human review',
				   'Panoramio images needing human review',
				   'Ipernity review needed',
				   'Unreviewed photos from indiannavy.nic.in',
				   'Filmitadka review needed',
				   'Fotopolska review needed',
				   'Files from Freesound.org lacking source',
				   'Images from HatenaFotolife needing License Review',
				   'Unreviewed files from National Repository of Open Educational Resources',
				   'OpenPhoto review needed',
				   'Lemill Web Albums files needing human review',
				   'Unreviewed files from Bollywood Hungama');

	// The licenses ID not allowed in the wiki, according to
	// https://www.flickr.com/services/api/explore/flickr.photos.licenses.getInfo
	$flickr_licenses_blacklist = array(0,2,3,6,10);

	// Same as above, for Ipernity. See http://www.ipernity.com/help/api/method/doc.setLicense
	$ipernity_licenses_blacklist = array(0,3,5,7,11);

	$picasa_licenses_blacklist = array(0,1,2,3,6);

	require_once('PassLicense.class.php');
	$wiki = new PassLicense($wiki_url,
				$flickr_licenses_blacklist,
				$ipernity_licenses_blacklist,
				$picasa_licenses_blacklist,
				$flickr_api_key,
				$ipernity_api_key);
		
	$wiki->setUserAgent('PassLicense/0.9 (https://github.com/Amitie10g/PassLicense_botclasses.php; davidkingnt@gmail.com) Botclasses.php/1.0');

	if(isset($_GET['pass'])){

		$pages = $_POST['pagename'];
		$category = $_POST['category'];
	
		$login = $wiki->login($wiki_user,$wiki_password);
		if($login['login']['result'] != 'Success') $error = 'Not logged in';
		if(empty($pages)) $error = 'No data given';

		if(!empty($error)){
			$_SESSION['result'] = array('errors'=>$error);
			header('Location: '.$_SERVER['PHP_SELF']."?category=$category");
			die();	
		}
	
		$count = 0;
		foreach($pages as $page){
			// Avoid the API blocking by sleeping the script each certain ammount of queries
			if(!is_int($max_queries)) $max_queries = 30;
			$num = $count/$max_queries;
			if(!is_int($num) && $num > 0) sleep(5);

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

			$summary = 'License review passed (using PassLicense)';
			$result[] = $wiki->edit($page,$content,$summary,true);
		
			// Unset the page contents cached
			unset($_SESSION['wiki_page_contents'][$page]);
		
			$count++;
		}

		$_SESSION['result'] = $result;
		header('Location: '.$_SERVER['PHP_SELF']."?category=$category");
		die();

	}elseif(isset($_GET['clear_cache'])){
		$category = $_GET['category'];
		if(isset($_GET['show_blacklisted'])) $blacklisted = "&show_blacklisted";
		session_destroy();
		header('Location: '.$_SERVER['PHP_SELF']."?category=$category$blacklisted");
		die();
	}else{
		// Validating the colour values
		$color_body_bg = $wiki->hexColor($color_body_bg);
		$color_details_1 = $wiki->hexColor($color_details_1);
		$color_details_2 = $wiki->hexColor($color_details_2);

		if(!empty($_GET['category'])){
			$category = $_GET['category'];
			$uri_o = str_replace(' ','_',$_SERVER['PHP_SELF']."?category=$category");
			$uri_b = str_replace(' ','_',"$uri_o&show_blacklisted");
			if(isset($_GET['show_blacklisted'])) $uri_s = array($uri_b,$uri_o,'Hide blacklisted');
			else $uri_s = array($uri_o,$uri_b,'Show blacklisted');
			$pages = $wiki->categorymembers("Category:$category",250);
		}
	
		require_once('PassLicense-web.tpl.php');
		unset($_SESSION['result']);
	}
}else die('Invalid or unresponsive Wiki URL. Check the URL or your Internet connection.');
?>