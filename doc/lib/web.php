<?php

/**
 * UploadFile: botclases.php based MediaWiki file uploader - Web mode script
 *
 *  (c) 2015 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 *  This program is free software, and you are welcome to redistribute it under
 *  certain conditions. This program comes with ABSOLUTELY NO WARRANTY.
 *  see README.md and LICENSE for more information
 *
 **/
 
// Check if SAPI is CLI
if(php_sapi_name() == "cli") die("\nThis script should be executed as CGI/HTTP (Webserver).\n");

session_start();
 
define('TEMP_PATH',realpath(sys_get_temp_dir()));
define('IN_UploadFile',true);

if(!is_dir($homepath)) die('$homepath is not set properly. For security reasons, you should set it up');

require_once('UploadFile.class.php');
$wiki = new Uploader($project);

$site_url = parse_url($project);
$site_url = $site_url['scheme'].'://'.$site_url['host'];

if(empty($font_size)) $font_size= '14pt';

if(isset($_GET['img'])){

	$filename = realpath($homepath.$_GET['img']);
	if(preg_match('/^('.addcslashes(realpath($homepath),'/').'){1}\/[\w\W]*$/',realpath($filename)) >= 1){
		
		if(is_numeric($_GET['width'])) $width = $_GET['width'];
		else $width = 2048;
		
		if(is_numeric($_GET['height'])) $height = $_GET['height'];
		else $height = 50;
		
		if(is_numeric($_GET['width']) && !is_numeric($_GET['height'])) $height = 300;
		
		$wiki = new Uploader($project);
		$wiki->getImg($filename,'Gmagick',$width,$height);
	}else die('Invalid image');

}elseif(isset($_GET['set_info'])){

	if(empty($_POST)){
		header('Location: '.$_SERVER['PHP_SELF']);
		die();
	}

	$files = $_POST['filelist'];
	$current_dir = $_POST['current_dir'];
	
	if(empty($alias)) $alias = $user;

	if(preg_match('/^('.addcslashes(realpath($homepath),'/').'){1}\/[\w\W]*$/',realpath("$homepath$current_dir"))) $current_dir = '/';
	
	require_once('web-header.tpl.php');
	require_once('web-body-intro.tpl.php');
	require_once('web-add-info.tpl.php');
	require_once('web-footer.tpl.php');

// Upload mode, that receives all the information via POST
}elseif(isset($_GET['upload'])){

	if(empty($_POST)){
		header('Location: '.$_SERVER['PHP_SELF']);
		die();
	}
	
	$wiki = new Uploader($project);
	$login = $wiki->login($user,$password);

	$item = $_POST['item'];
	$pagename = $_POST['pagename'];
	$description = $_POST['description'];
	$date = $_POST['date'];
	$source = $_POST['source'];
	$author = $_POST['author'];
	$optional = $_POST['optional'];
	$license = $_POST['license'];
	$categories = $_POST['categories'];
	$summary = $_POST['summary'];
	
	foreach($pagename as $key=>$value){
		$filename = $homepath.$item[$key];
		$pagename_g = $pagename[$key];
		$result[$pagename_g] = $wiki->upload($filename,$pagename_g,$description[$key],$date[$key],$source[$key],$author[$key],$optional[$key],$license[$key],$categories[$key],$summary[$key]);
	}

	$_SESSION['result'] = $result;
	header('Location: '.$_SERVER['PHP_SELF'].'?uploaded');
	die();
	
}elseif(isset($_GET['uploaded'])){

	if(isset($_SESSION['result'])){
		$result = $_SESSION['result'];
	}else{
		header('Location: '.$_SERVER['PHP_SELF']);
		die();
	}
	
	require_once('web-header.tpl.php');
	require_once('web-body-intro.tpl.php');
	require_once('web-uploaded.tpl.php');
	require_once('web-footer.tpl.php');
	
	unset($_SESSION['result']);
	
}elseif(isset($_GET['check_auth'])){

	$wiki = new Uploader($project);
	$login = $wiki->login($user,$password);

	require_once('web-login-status.tpl.php');

// Default mode. It receives the User, Password and Project, and leaves a message for the previous upload
}else{

	if(isset($_GET['dir'])) $current_dir = urldecode($_GET['dir']).'/';
	if(preg_match('/^('.addcslashes(realpath($homepath),'/').'){1}\/[\w\W]*$/',realpath("$homepath$current_dir")) == false) $current_dir = '/';

	$files = scandir("$homepath$current_dir",SCANDIR_SORT_ASCENDING);

	foreach($files as $item){
	
		if(is_dir("$homepath$current_dir$item") && $item != '.' && $item != '..') $dirlist[] = $item;
		elseif(is_file("$homepath$current_dir$item")) $filelist[] = $item;
	}
	
	@natsort($dirlist); @natsort($filelist);
	
	require_once('web-header.tpl.php');
	require_once('web-body-intro.tpl.php');
	require_once('web-check-auth.tpl.php');
	require_once('web-select-files.tpl.php');
	require_once('web-footer.tpl.php');

}
?>