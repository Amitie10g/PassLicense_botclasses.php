<?php

/**
 * UploadFile: botclases.php based MediaWiki file uploader - Classes library.
 *
 *  (c) 2015 Davod - https://commons.wikimedia.org/wiki/User:Amitie_10g
 *
 *  Contains parts of the Chris G's botclasses.php library. More information at
 *  https://www.mediawiki.org/wiki/Manual:Chris_G%27s_botclasses
 *
 *  (c) 2008-2012 Chris G - https://en.wikipedia.org/wiki/User:Chris_G
 *  (c) Cobi - https://en.wikipedia.org/wiki/User:Cobi
 *
 *  This program is free software, and you are welcome to redistribute it under
 *  certain conditions. This program comes with ABSOLUTELY NO WARRANTY.
 *  see README.md and LICENSE for more information
 *
 **/
 
/**
 * This class is designed to provide a simplified interface to cURL which maintains cookies.
 * @author Cobi
 **/
class http {
    private $ch;
    private $uid;
    public $cookie_jar;
    public $postfollowredirs;
    public $getfollowredirs;
    public $quiet=false;
    public $userAgent = 'php wikibot classes';
    public $httpHeader = array('Content-type: multipart/form-data');
    public $defaultHttpHeader = array('Content-type: multipart/form-data');

	public function http_code () {
		return curl_getinfo( $this->ch, CURLINFO_HTTP_CODE );
	}

    function data_encode ($data, $keyprefix = "", $keypostfix = "") {
        assert( is_array($data) );
        $vars=null;
        foreach($data as $key=>$value) {
            if(is_array($value))
                $vars .= $this->data_encode($value, $keyprefix.$key.$keypostfix.urlencode("["), urlencode("]"));
            else
                $vars .= $keyprefix.$key.$keypostfix."=".urlencode($value)."&";
        }
        return $vars;
    }

    function __construct () {
        $this->ch = curl_init();
        $this->uid = dechex(rand(0,99999999));
        $this->postfollowredirs = 0;
        $this->getfollowredirs = 1;
        $this->cookie_jar = array();
    }

    function post( $url, $data ) {

        /* Crappy hack to add extra cookies, should be cleaned up */
        $cookies = null;
        foreach ($this->cookie_jar as $name => $value) {
            if (empty($cookies))
                $cookies = "$name=$value";
            else
                $cookies .= "; $name=$value";
        }

	curl_setopt($this->ch,CURLOPT_URL,$url);
	curl_setopt($this->ch,CURLOPT_USERAGENT,$this->userAgent);
	curl_setopt($this->ch,CURLOPT_COOKIE,$cookies);
	curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->postfollowredirs);
	curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
	curl_setopt($this->ch,CURLOPT_HTTPHEADER,$this->httpHeader);
	curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
	curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($this->ch,CURLOPT_POST,1);
	curl_setopt($this->ch,CURLOPT_SAFE_UPLOAD,1);
	curl_setopt($this->ch,CURLOPT_POSTFIELDS,$data);
	curl_setopt($this->ch,CURLOPT_COOKIEJAR,TEMP_PATH.'cluewikibot.cookies.'.$this->uid.'.dat');
	curl_setopt($this->ch,CURLOPT_COOKIEFILE,TEMP_PATH.'cluewikibot.cookies.'.$this->uid.'.dat');
	curl_setopt($this->ch,CURLOPT_MAXCONNECTS,100);
			 
        return curl_exec($this->ch);
    }

    function get ( $url ) {
        curl_setopt($this->ch,CURLOPT_URL,$url);
        curl_setopt($this->ch,CURLOPT_USERAGENT,$this->userAgent);
        /* Crappy hack to add extra cookies, should be cleaned up */
        $cookies = null;
        foreach ($this->cookie_jar as $name => $value) {
            if (empty($cookies))
                $cookies = "$name=$value";
            else
                $cookies .= "; $name=$value";
        }
        if ($cookies != null) curl_setopt($this->ch,CURLOPT_COOKIE,$cookies);
        curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->getfollowredirs);
        curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
        curl_setopt($this->ch,CURLOPT_HEADER,false);
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
        curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($this->ch,CURLOPT_HTTPGET,1);

        return curl_exec($this->ch);
    }

    function setHTTPcreds($uname,$pwd) {
        curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($this->ch, CURLOPT_USERPWD, $uname.":".$pwd);
    }

    function __destruct () {
        curl_close($this->ch);
        @unlink(TEMP_PATH.'/cluewikibot.cookies.'.$this->uid.'.dat');
    }
}

/**
 * This class is interacts with wikipedia using api.php
 * @author Chris G and Cobi, modified by Davod
 **/
class Uploader {
    private $http;
    private $token;
    private $ecTimestamp;
    public $url;
    public $echoRet = false; // For debugging unserialize errors

    function __construct ($url='http://commons.wikimedia.org/w/api.php',$hu=null,$hp=null) {
        $this->http = new http;
        $this->token = null;
        $this->url = $url;
        $this->ecTimestamp = null;
        if ($hu!==null)
        	$this->http->setHTTPcreds($hu,$hp);
    }

    function __set($var,$val) {
	switch($var) {
  		case 'quiet':
			$this->http->quiet=$val;
     			break;
   		default:
     			echo "WARNING: Unknown variable ($var)!\n";
 	}
    }

    function setUserAgent ($userAgent) {
	$this->http->userAgent = $userAgent;
    }
    
    function setHttpHeader ($httpHeader) {
	$this->http->httpHeader = $httpHeader;
    }

    function useDefaultHttpHeader () {
	$this->http->httpHeader = $this->http->defaultHttpHeader;
    }

    function query ($query, $post=null, $repeat=0) {
    
        if ($post==null) {
            $ret = $this->http->get($this->url.$query);
        } else {
            $ret = $this->http->post($this->url.$query,$post);
        }
	if ($this->http->http_code() != "200") {
		if ($repeat < 10) {
			return $this->query($query,$post,++$repeat);
		} else {
			throw new Exception("HTTP Error " . $this->http->http_code());
		}
	}
	if( $this->echoRet ) {
	    if( @unserialize( $ret ) === false ) {
		return array( 'errors' => array(
		    "The API query result can't be unserialized. Raw text is as follows: $ret\n" ) );
	    }
	}
        return unserialize( $ret );
    }

    function login ($user,$pass) {
    	$post = array('lgname' => $user, 'lgpassword' => $pass);
        $ret = $this->query('?action=login&format=php',$post);
        /* This is now required - see https://bugzilla.wikimedia.org/show_bug.cgi?id=23076 */
        if ($ret['login']['result'] == 'NeedToken') {
        	$post['lgtoken'] = $ret['login']['token'];
        	$ret = $this->query( '?action=login&format=php', $post );
        }
        if ($ret['login']['result'] != 'Success') {
            echo "Login error: \n";
            print_r($ret);
            die();
        } else {
            return $ret;
        }
    }

    /* crappy hack to allow users to use cookies from old sessions */
    function setLogin($data) {
        $this->http->cookie_jar = array(
        $data['cookieprefix'].'UserName' => $data['lgusername'],
        $data['cookieprefix'].'UserID' => $data['lguserid'],
        $data['cookieprefix'].'Token' => $data['lgtoken'],
        $data['cookieprefix'].'_session' => $data['sessionid'],
        );
    }

    function getedittoken () {
        $x = $this->query('?action=query&prop=info&intoken=edit&titles=Main%20Page&format=php');
        foreach ($x['query']['pages'] as $ret) {
            return $ret['edittoken'];
        }
    }

    function upload($filename,$pagename,$description,$date,$source,$author,$optional,$license,$categories,$summary){

	if (!file_exists($filename)) return array('errors'=>"Input file does not exist!");

	// If no pagename given, use the basename from $filename
	if(empty($pagename)) $pagename = basename($filename);

	// Check if the target page already exist
	if($this->getfilelocation("File:$pagename") != false) return array('errors'=>"target page \"File:$pagename\" already exist at Wiki!");

	// If no Summary given, provide one by default
	if(empty($summary))  $summary = 'Uploaded with Davod Uploader (botclasses.php)';

	// Set the HTTP headers
	$this->setHttpHeader(array("Content-type: multipart/form-data",
	"Content-Disposition: attachment; filename=\"$pagename\""));

	// The contents to be placed in the File page
	$content = <<<EOC
== {{int:filedesc}} ==
{{Information
 |description    = $description
 |date           = $date
 |source         = $source
 |author         = $author
}}
$optional
== {{int:license}} ==
$license

$categories

EOC;
	$mime  = $this->getMIME($filename);
	$file  = new CURLFile($filename,$mime,'file');
	
        if($this->token == null) $this->token = $this->getedittoken();

        $params = array(
                'filename'	=> $pagename,
                'text'		=> $content,
                'token'		=> $this->token,
		'file'		=> $file,
		'watchlist'	=> 'watch',
		'ignorewarnings'=> '1',
		'comment'       => $summary
        );
    
        return $this->query('?action=upload&format=php',$params);
    }

    // Function to get the MIME type using several alternatives
    function getMIME($filename){
	// Using finfo (recommended, requires finfo PECL extension)
	if(class_exists('finfo')){
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		return $finfo->file($filename);
		
	// Using GD getimagesize() (requires GD library, works only with image files)
	}elseif(is_callable('getimagesize')){
		$info = getimagesize($filename); 
		return $info['mime'];
	
	// Using exif_imagetype() (works only with image files)
	}elseif(is_callable('exif_imagetype')){
		return image_type_to_mime_type(exif_imagetype($filename));
	}else return false;
    }

    function getImg($filename,$class,$width,$height){

	if(!is_file($filename)) return false;

	// Using OOP-based functions	
	if(class_exists($class)){
		// Using ImageMagick or GraphicksMagick that shares the same methods names
		if($class == 'Imagick' || $class == 'Gmagick'){
			$img = new $class($filename);
			$img->thumbnailImage($width,$height,true);
			$img->setImageFormat('png');
			header('Content-type: image/png');
			echo $img;
		}else return false;

	// Using GD as fallback
	}else{
		$getsize = getimagesize($filename);
	
		switch($getsize['mime']){
			case 'image/jpeg': $func_c = 'imagecreatefromjpeg'; $func_o = 'imagejpeg'; break;
			case 'image/png':  $func_c = 'imagecreatefrompng'; $func_o = 'imagegif'; break;
			case 'image/gif':  $func_c = 'imagecreatefromgif'; $func_o = 'imagepng'; break;
			default: return false;
		}
	
		$factor = $getsize['1']/$new_height;

		if($factor > 1) $factor = 1;

		$new_width = $getsize['0']/$factor;
		$new_height = $getsize['1']/$factor;
	
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image = $func_c($filename);
		imagecopyresampled($image_p,$image,0,0,0,0,$new_width,$new_height,$getsize[0],$getsize[1]);

		imagealphablending($image_p,false);
		imagesavealpha($image_p,true);

		header('Content-Type: '.$getsize['mime']);
		return $func_o($image_p);
	}
    }
    
    function getfilelocation ($page) {
        $x = $this->query('?action=query&format=php&prop=imageinfo&titles='.urlencode($page).'&iilimit=1&iiprop=url');
        foreach ($x['query']['pages'] as $ret ) {
            if (isset($ret['imageinfo'][0]['url'])) {
                return $ret['imageinfo'][0]['url'];
            } else
                return false;
        }
    }
}

?>