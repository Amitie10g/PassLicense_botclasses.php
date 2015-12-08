<?php
/**
 * botclasses.php - Bot classes for interacting with mediawiki.
 *
 *  (c) 2008-2012 Chris G - http://en.wikipedia.org/wiki/User:Chris_G
 *  (c) 2009-2010 Fale - http://en.wikipedia.org/wiki/User:Fale
 *  (c) 2010      Kaldari - http://en.wikipedia.org/wiki/User:Kaldari
 *  (c) 2011      Gutza - http://en.wikipedia.org/wiki/User:Gutza
 *  (c) 2012      Sean - http://en.wikipedia.org/wiki/User:SColombo
 *  (c) 2012      Brain - http://en.wikipedia.org/wiki/User:Brian_McNeil
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
 *  Developers (add yourself here if you worked on the code):
 *      Cobi    - [[User:Cobi]]         - Wrote the http class and some of the wikipedia class
 *      Chris   - [[User:Chris_G]]      - Wrote the most of the wikipedia class
 *      Fale    - [[User:Fale]]         - Polish, wrote the extended and some of the wikipedia class
 *      Kaldari - [[User:Kaldari]]      - Submitted a patch for the imagematches function
 *      Gutza   - [[User:Gutza]]        - Submitted a patch for http->setHTTPcreds(), and http->quiet
 *      Sean    - [[User:SColombo]]     - Wrote the lyricwiki class (now moved to lyricswiki.php)
 *      Brain   - [[User:Brian_McNeil]] - Wrote wikipedia->getfileuploader() and wikipedia->getfilelocation
 *
 *  Modified by Davod <https://commons.wikimedia.org/wiki/User:Amitie 10g> and relicensed under the GNU GPLv3
 *
 *  Changes:
 *
 *    Class "wikipedia" renamed to "wiki"
 *    Functions in Class "extended" merged into "wiki", to avoid issues
 *    Some functions modified to accept more info
 *    Removed the echo statements. The classes should not output anything
 *    Removed parts commented intended for debugging purposes
 *    Removed several unneded functions
 *    Fixed the cURL issues with PHP5+
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
    public $httpHeader = array( 'Expect:' );
    public $defaultHttpHeader = array( 'Expect:' );

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
        curl_setopt($this->ch,CURLOPT_COOKIEJAR,TEMP_PATH.'cluewikibot.cookies.'.$this->uid.'.dat');
        curl_setopt($this->ch,CURLOPT_COOKIEFILE,TEMP_PATH.'cluewikibot.cookies.'.$this->uid.'.dat');
        curl_setopt($this->ch,CURLOPT_MAXCONNECTS,100);
        $this->postfollowredirs = 0;
        $this->getfollowredirs = 1;
        $this->cookie_jar = array();
    }

    function post( $url, $data ) {
        $time = microtime(1);
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
        if ($cookies != null)
            curl_setopt($this->ch,CURLOPT_COOKIE,$cookies);
        curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->postfollowredirs);
        curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
	curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $this->httpHeader );
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
        curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($this->ch,CURLOPT_POST,1);
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($this->ch);
        return $data;
    }

    function get ( $url ) {
        $time = microtime(1);
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
        if ($cookies != null)
            curl_setopt($this->ch,CURLOPT_COOKIE,$cookies);
        curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->getfollowredirs);
        curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
        curl_setopt($this->ch,CURLOPT_HEADER,0);
        curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
        curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($this->ch,CURLOPT_HTTPGET,1);
        //curl_setopt($this->ch,CURLOPT_FAILONERROR,1);
        $data = curl_exec($this->ch);
        return $data;
    }

    function setHTTPcreds($uname,$pwd) {
        curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($this->ch, CURLOPT_USERPWD, $uname.":".$pwd);
    }

    function __destruct () {
        curl_close($this->ch);
        @unlink('/tmp/cluewikibot.cookies.'.$this->uid.'.dat');
    }
}

/**
 * This class is interacts with wikipedia using api.php
 * @author Chris G and Cobi
 **/
class wiki {
    private $http;
    private $token;
    private $ecTimestamp;
    public $url;
    public $echoRet = false; // For debugging unserialize errors

    /**
     * This is our constructor.
     * @return void
     **/
    function __construct ($url='https://commons.wikimedia.org/w/api.php',$hu=null,$hp=null) {
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

    /**
     * Changes the user agent.
     * @param $userAgent The user agent string.
     **/
    function setUserAgent ( $userAgent ) {
	$this->http->userAgent = $userAgent;
    }
    
    /**
     * Changes the http header.
     * @param $httpHeader The http header.
     **/
    function setHttpHeader ( $httpHeader ) {
	$this->http->httpHeader = $httpHeader;
    }

    /**
     * Changes the http header.
     * @param $httpHeader The http header.
     **/
    function useDefaultHttpHeader () {
	$this->http->httpHeader = $this->http->defaultHttpHeader;
    }

    /**
     * Sends a query to the api.
     * @param $query The query string.
     * @param $post POST data if its a post request (optional).
     * @param $repeat How many times the request will be repeated
     * @param $url The URL where we want to work (for external services API)
     * @return The api result.
     **/
    function query($query,$post=null,$repeat=0,$url=null){
	if(empty($url)) $url = $this->url;
        if($post==null) $ret = $this->http->get($url.$query);
        else $ret = $this->http->post($url.$query,$post);
	if($this->http->http_code() != "200"){
		if($repeat < 10) return $this->query($query,$post,++$repeat);
		else throw new Exception("HTTP Error " . $this->http->http_code() );
	}
	if( $this->echoRet ) {
	    if( @unserialize( $ret ) === false ) {
		return array( 'errors' => array(
		    "The API query result can't be unserialized. Raw text is as follows: $ret\n" ) );
	    }
	}
        return unserialize( $ret );
    }

    /**
     * Gets the content of a page. Returns false on error.
     * @param $page The wikipedia page to fetch.
     * @param $revid The revision id to fetch (optional)
     * @return The wikitext for the page.
     **/
    function getpage ($page,$revid=null,$detectEditConflict=false) {
        $append = '';
        if ($revid!=null)
            $append = '&rvstartid='.$revid;
        $x = $this->query('?action=query&format=php&prop=revisions&titles='.urlencode($page).'&rvlimit=1&rvprop=content|timestamp'.$append);
        foreach ($x['query']['pages'] as $ret) {
            if (isset($ret['revisions'][0]['*'])) {
                if ($detectEditConflict)
                    $this->ecTimestamp = $ret['revisions'][0]['timestamp'];
                return $ret['revisions'][0]['*'];
            } else
                return false;
        }
    }

    /**
     * Gets the page id for a page.
     * @param $page The wikipedia page to get the id for.
     * @return The page id of the page.
     **/
    function getpageid ($page) {
        $x = $this->query('?action=query&format=php&prop=revisions&titles='.urlencode($page).'&rvlimit=1&rvprop=content');
        foreach ($x['query']['pages'] as $ret) {
            return $ret['pageid'];
        }
    }

    /**
     * Gets the number of contributions a user has.
     * @param $user The username for which to get the edit count.
     * @return The number of contributions the user has.
     **/
    function contribcount ($user) {
        $x = $this->query('?action=query&list=allusers&format=php&auprop=editcount&aulimit=1&aufrom='.urlencode($user));
        return $x['query']['allusers'][0]['editcount'];
    }

    /**
     * Returns an array with all the members of $category
     * @param $category The category to use.
     * @param $subcat (bool) Go into sub categories?
     * @return array
     **/
    function categorymembers ($category,$limit=10,$continue=null,$subcat=false) {
    
	$res = $this->query('?action=query&list=categorymembers&cmtype=file&cmsort=timestamp&cmdir=newer&format=php&cmtitle='.urlencode($category).'&cmlimit='.$limit.$continue);
            
	if (isset($res['error'])) return false;
    
        foreach($res['query']['categorymembers'] as $page) {
		$title = $page['title'];
		// For a strange reason, "&cmtype=file&cmsort=timestamp" does not return File:-only reults
		if(preg_match('/^(File:){1}[\p{L}\p{N}\p{P}\p{S}_ ]+$/',$title) >= 1) $pages[] = $title;
        }
	
	// Previous and Next page (aka. Continue in the MediaWiki API) rewriten, and in developement
	// For now, the function will return just the members inside the limit of 250
	return $pages;
    }

    /**
     * Returns a list of pages that link to $page.
     * @param $page
     * @param $extra (defaults to null)
     * @return array
     **/
    function whatlinkshere ($page,$extra=null) {
        $continue = '';
        $pages = array();
        while (true) {
            $res = $this->query('?action=query&list=backlinks&bltitle='.urlencode($page).'&bllimit=500&format=php'.$continue.$extra);
            if (isset($res['error'])) {
                return false;
            }
            foreach ($res['query']['backlinks'] as $x) {
                $pages[] = $x['title'];
            }
            if (empty($res['query-continue']['backlinks']['blcontinue'])) {
                return $pages;
            } else {
                $continue = '&blcontinue='.urlencode($res['query-continue']['backlinks']['blcontinue']);
            }
        }
    }

    /**
    * Returns a list of pages that include the image.
    * @param $image
    * @param $extra (defaults to null)
    * @return array
    **/
    function whereisincluded ($image,$extre=null) {
        $continue = '';
        $pages = array();
        while (true) {
            $res = $this->query('?action=query&list=imageusage&iutitle='.urlencode($image).'&iulimit=500&format=php'.$continue.$extra);
            if (isset($res['error']))
                return false;
            foreach ($res['query']['imageusage'] as $x) {
                $pages[] = $x['title'];
            }
            if (empty($res['query-continue']['imageusage']['iucontinue']))
                return $pages;
            else
                $continue = '&iucontinue='.urlencode($res['query-continue']['imageusage']['iucontinue']);
        }
    }
    
    /**
    * Returns a list of pages that use the $template.
    * @param $template the template we are intereste into
    * @param $extra (defaults to null)
    * @return array
    **/
    function whatusethetemplate ($template,$extra=null) {
        $continue = '';
        $pages = array();
        while (true) {
            $res = $this->query('?action=query&list=embeddedin&eititle=Template:'.urlencode($template).'&eilimit=500&format=php'.$continue.$extra);
            if (isset($res['error'])) {
                return false;
            }
            foreach ($res['query']['embeddedin'] as $x) {
                $pages[] = $x['title'];
            }
            if (empty($res['query-continue']['embeddedin']['eicontinue'])) {
                return $pages;
            } else {
                $continue = '&eicontinue='.urlencode($res['query-continue']['embeddedin']['eicontinue']);
            }
         }
     }
    
    /**
     * Returns an array with all the subpages of $page
     * @param $page
     * @return array
     **/
    function subpages ($page) {
        /* Calculate all the namespace codes */
        $ret = $this->query('?action=query&meta=siteinfo&siprop=namespaces&format=php');
        foreach ($ret['query']['namespaces'] as $x) {
            $namespaces[$x['*']] = $x['id'];
        }
        $temp = explode(':',$page,2);
        $namespace = $namespaces[$temp[0]];
        $title = $temp[1];
        $continue = '';
        $subpages = array();
        while (true) {
            $res = $this->query('?action=query&format=php&list=allpages&apprefix='.urlencode($title).'&aplimit=500&apnamespace='.$namespace.$continue);
            if (isset($x['error'])) {
                return false;
            }
            foreach ($res['query']['allpages'] as $p) {
                $subpages[] = $p['title'];
            }
            if (empty($res['query-continue']['allpages']['apfrom'])) {
                return $subpages;
            } else {
                $continue = '&apfrom='.urlencode($res['query-continue']['allpages']['apfrom']);
            }
        }
    }

    /**
     * This function takes a username and password and logs you into wikipedia.
     * @param $user Username to login as.
     * @param $pass Password that corrisponds to the username.
     * @return array
     **/
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

    /**
     * Check if we're allowed to edit $page.
     * See http://en.wikipedia.org/wiki/Template:Bots
     * for more info.
     * @param $page The page we want to edit.
     * @param $user The bot's username.
     * @return bool
     **/
    function nobots ($page,$user=null,$text=null) {
        if ($text == null) {
            $text = $this->getpage($page);
        }
        if ($user != null) {
            if (preg_match('/\{\{(nobots|bots\|allow=none|bots\|deny=all|bots\|optout=all|bots\|deny=.*?'.preg_quote($user,'/').'.*?)\}\}/iS',$text)) {
                return false;
            }
        } else {
            if (preg_match('/\{\{(nobots|bots\|allow=none|bots\|deny=all|bots\|optout=all)\}\}/iS',$text)) {
                return false;
            }
        }
        return true;
    }

    /**
     * This function returns the edit token for the current user.
     * @return edit token.
     **/
    function getedittoken () {
        $x = $this->query('?action=query&prop=info&intoken=edit&titles=Main%20Page&format=php');
        foreach ($x['query']['pages'] as $ret) {
            return $ret['edittoken'];
        }
    }
    /**
     * Edits a page.
     * @param $page Page name to edit.
     * @param $data Data to post to page.
     * @param $summary Edit summary to use.
     * @param $minor Whether or not to mark edit as minor.  (Default false)
     * @param $bot Whether or not to mark edit as a bot edit.  (Default true)
     * @return api result
     **/
    function edit ($page,$data,$summary = '',$minor = false,$bot = true,$section = null,$detectEC=false,$maxlag='') {
        if ($this->token==null) {
            $this->token = $this->getedittoken();
        }
        $params = array(
            'title' => $page,
            'text' => $data,
            'token' => $this->token,
            'summary' => $summary,
            ($minor?'minor':'notminor') => '1',
            ($bot?'bot':'notbot') => '1'
        );
        if ($section != null) {
            $params['section'] = $section;
        }
        if ($this->ecTimestamp != null && $detectEC == true) {
            $params['basetimestamp'] = $this->ecTimestamp;
            $this->ecTimestamp = null;
        }
        if ($maxlag!='') {
            $maxlag='&maxlag='.$maxlag;
        }
        return $this->query('?action=edit&format=php'.$maxlag,$params);
    }

    /**
    * Add a text at the bottom of a page
    * @param $page The page we're working with.
    * @param $text The text that you want to add.
    * @param $summary Edit summary to use.
    * @param $minor Whether or not to mark edit as minor.  (Default false)
    * @param $bot Whether or not to mark edit as a bot edit.  (Default true)
    * @return api result
    **/
    function addtext( $page, $text, $summary = '', $minor = false, $bot = true )
    {
        $data = $this->getpage( $page );
        $data.= "\n" . $text;
        return $this->edit( $page, $data, $summary, $minor, $bot );
    }
    /**
     * Uploads an image.
     * @param $page The destination file name.
     * @param $file The local file path.
     * @param $desc The upload description (defaults to '').
     **/
     function upload ( $page, $file, $desc='' ) {
	if ( !file_exists( $file ) ) {
	    return array( 'errors' => array(
		    "File does not exist!" ) );
	}
        if ($this->token == null) {
                $this->token = $this->getedittoken();
        }

        $params = array(
                'filename'        => $page,
                'comment'         => $desc,
                'text'            => $desc,
                'token'           => $this->token,
                'ignorewarnings'  => '1',
                'file'            => '@' . $file
        );
        return $this->query( '?action=upload&format=php', $params );
    }
    /**  BMcN 2012-09-16
     * Retrieve a media file's actual location.
     * @param $page The "File:" page on the wiki which the URL of is desired.
     * @return The URL pointing directly to the media file (Eg http://upload.mediawiki.org/wikipedia/en/1/1/Example.jpg)
     **/
    function getfilelocation ($page) {
        $x = $this->query('?action=query&format=php&prop=imageinfo&titles='.urlencode($page).'&iilimit=1&iiprop=url');
        foreach ($x['query']['pages'] as $ret ) {
            if (isset($ret['imageinfo'][0]['url'])) {
                return $ret['imageinfo'][0]['url'];
            } else
                return false;
        }
    }
 
    /**  BMcN 2012-09-16
     * Retrieve a media file's uploader.
     * @param $page The "File:" page
     * @return The user who uploaded the topmost version of the file.
     **/
    function getfileuploader ($page) {
        $x = $this->query('?action=query&format=php&prop=imageinfo&titles='.urlencode($page).'&iilimit=1&iiprop=user');
        foreach ($x['query']['pages'] as $ret ) {
            if (isset($ret['imageinfo'][0]['user'])) {
                return $ret['imageinfo'][0]['user'];
            } else
                return false;
        }
    }
/**
 *Functions originaly form extended Class
 **/

    /**
     * Find a string
     * @param $page The page we're working with.
     * @param $string The string that you want to find.
     * @return bool value (1 found and 0 not-found)
     **/
    function findstring( $page, $string )
    {
        $data = $this->getpage( $page );
        if( strstr( $data, $string ) )
            return 1;
        else
            return 0;
    }

    /**
     * Replace a string
     * @param $page The page we're working with.
     * @param $string The string that you want to replace. (it can be a string or an array
     * @param $newstring The string that will replace the present string. (same as above)
     * @param $regex If $string will be considered as a regular expression
     * @return the new text of page
     **/
    function replacestring($page,$string,$newstring,$regex=false){
        $data = $this->getpage($page);
	if($data != false){
		if(regex === true){
			if(is_array($string) && is_array($newstring)){
				foreach($string as $key=>$str){
					$data = preg_replace($str,$newstring[$key],$data);
				}

			}elseif(is_string($string) && is_string($newstring)){
				$data = preg_replace($newstring,$string,$data);
			}else{
				$data = false;
			}
		}else{
			$data = str_replace($string,$newstring,$data);
		}
		return $data;
	}
    }
    
    /**
     * Get a template from a page
     * @param $page The page we're working with
     * @param $template The name of the template we are looking for
     * @return the searched (NULL if the template has not been found)
     **/
    function gettemplate($page,$template){
       $data = $this->getpage($page);
       $template = preg_quote( $template, " " );
       $r = "/{{" . $template . "(?:[^{}]*(?:{{[^}]*}})?)+(?:[^}]*}})?/i";
       preg_match_all( $r, $data, $matches );
       if( isset( $matches[0][0])) return $matches[0][0];
       else return null;
    }

/**
 * Functions added by me
 **/

    /**
     * Get the contents from the Wiki in several formats, using the MediaWiki API
     * @param $page The page that we're working
     * @param $props The properties that we want to obtain from the query
     * @return the contents as array
     **/
     function getPageContents($page,$props=null){
     
	if(!empty($_SESSION['wiki_page_contents'][$page][$props])) $contents = $_SESSION['wiki_page_contents'][$page][$props];
	else{
		$contents = $this->query("?action=parse&format=php&prop=$props&disabletoc=&mobileformat=&noimages=&page=".urlencode($page));
		$_SESSION['wiki_page_contents'][$page][$props] = $contents;
	}	
	return $contents;
    }

    /**
     * Get the URL of the thumbnail of a File
     * @param $page The page in File: namespace that we want to get the URL
     * @param $width The desired width
     * @param $width The desired height
     * @return the URL as string
     **/
     function getThumbURL($page,$width=null,$height=null){
	if(empty($width)) $width = '2000';
	if(empty($height)) $height = '2000';

	if(!empty($_SESSION['thumburl'][$page][$width.'_'.$height])) $thumburl = $_SESSION['thumburl'][$page][$width.'_'.$height];
	else{
		$thumburl = $this->query("?action=query&format=php&titles=$page&prop=imageinfo&iiprop=url&iiurlwidth=$width&iiurlheight=$height");

		$thumburl = $thumburl['query']['pages'];
		sort($thumburl);
		$thumburl = $thumburl[0]['imageinfo']['0']['thumburl'];

		$_SESSION['thumburl'][$page][$width.'_'.$height] = $thumburl;
	}
	return $thumburl;
    }

    /**
     * Get the template tags from the given page, with its parameters (everything between {{}})
     * @param $content The contents we're working with
     * @param $tags The specific template tags what we want to match (not used for now)
     * @return the desired template tags as array
     **/
    function getTemplates($content,$tags=null){
	$pattern_search = '/\{\{([\p{L}\p{N}\p{P}\|= ]*)+\}\}/';
	preg_match_all($pattern_search,$content,$templates);
	$templates = $templates[0];
	return $templates;
    }

    /**
     * Get useful information from a Flickr file using the Flickr API
     * @param $id The Flickr License ID
     * @param $api_key The Flickr API key (required to interact with the Flickr API
     * @return the text of the given ID as string
     **/
     function getFlickrInfo($id,$api_key=null){
	if(!empty($_SESSION['flickr_info'][$id])) $result = $_SESSION['flickr_info'][$id];
	else{
		$url = "https://api.flickr.com/services/rest/";
		$query = "?method=flickr.photos.getInfo&format=php_serial&api_key=$api_key&photo_id=$id";
		$result = $this->query($query,null,null,$url);
		$_SESSION['flickr_info'][$id] = $result;
	}

	if($result['stat'] == 'ok') return $result;
	else return false;
    }

    /**
     * Get the license text from a Flickr License ID
     * @param $id The Flickr ID of the file
     * @param $api_key The Flickr API key (required to interact with the Flickr API
     * @return the license text as array
     **/
     function getFlickrLicense($id,$api_key=null){
	if(!empty($_SESSION['flickr_licenses'])) $result = $_SESSION['flickr_licenses'];
	else{
		$url = "https://api.flickr.com/services/rest/";
		$query = "?method=flickr.photos.licenses.getInfo&format=php_serial&api_key=$api_key";
		$result = $this->query($query,null,null,$url);
		$_SESSION['flickr_licenses'] = $result;
	}
	if($result['stat'] == 'ok'){
		$licenses = $result['licenses']['license'];

		foreach($licenses as $license){
			if($id == $license['id']){
				$name = $license['name'];
				break;
			}
		}
		if(!empty($name)) return $name;
		else return false;
		
	}else return false;
    }
    
    function getFlickrThumbURL($id,$api_key=null,$max_height=200){
	if(!empty($_SESSION['flickr_thumburl'][$id])) $result = $_SESSION['flickr_thumburl'][$id];
	else{
		$url = "https://api.flickr.com/services/rest/";
		$query = "?method=flickr.photos.getSizes&format=php_serial&api_key=$api_key&photo_id=$id";
		$result = $this->query($query,null,null,$url);
		$_SESSION['flickr_thumburl'][$id] = $result;
	}
	
	if($result['stat'] == 'ok'){
		$get_sizes = $result['sizes']['size'];
		
		foreach($get_sizes as $size){
			$sizes[] = $size['height'];
		}
		
		$best_fit = $this->bestFit($max_height,$sizes,true);
	
		return $get_sizes[$best_fit]['source'];
	}else return false;    
    }

    /**
     * Extract the Flickr photo ID from URL
     * @param $url The URL to be parsed
     * @return the numeric ID as string
     **/
    function getFlickrPhotoID($url){
	$id = explode('/',parse_url($url,PHP_URL_PATH));
	$id = $id[3];

	return $id;
    }

    /**
     * Get information about external sources using their API (for now, only Flickr is supported,
     * and requires a Flickr API key. Support for more service is in developement)
     * @param $url The URL to be parsed
     * @return the service, license, and the external thumbnail URL as array
     **/
     function getExternalInfo($url_g){
	if(is_array($url_g)){
		foreach($url_g as $url){
			if(preg_match('/^(http|https){1}\:\/\/(www\.|){1}(flickr\.com\/photos\/){1}[\w@]+\/[\w@]+/',$url) >= 1){
				$url = $url;
				$service = 'flickr';
				break;
			}
		}
	}else{
		if(preg_match('/^(http|https){1}\:\/\/(www\.|){1}(flickr\.com\/photos\/){1}[\w@]+\/[\w@]+/',$url_g) >= 1){
			$url = $url_g;
			$service = 'flickr';
		}
	}
	
	if(empty($url)) return false;
	
	switch($service){
		case 'flickr':
			global $flickr_licenses_blacklist;
			global $flickr_api_key;
			
			$photo_id = $this->getFlickrPhotoID($url);
			$photo_info = $this->getFlickrInfo($photo_id,$flickr_api_key);

			if($photo_info['stat'] == 'ok'){
				$photo_license = $photo_info['photo']['license'];
				$photo_url = $photo_info['photo']['urls']['url'][0]['_content'];

				if(in_array($photo_license,$flickr_licenses_blacklist)) $license = 'blacklisted';
				else{
					$license = $this->getFlickrLicense($photo_license,$flickr_api_key);
					$thumburl = $this->getFlickrThumbURL($photo_id,$flickr_api_key);
				}
				break;
			}else{
				$license = false;
				return false;
			} 
		default: $license = false;
	}

	return array('service'=>$service,'license'=>$license,'thumburl'=>$thumburl,'url'=>$photo_url);
    }
    
    
    /**
     * Get the closest number present in an array against an arbitrary number
     * Credits to "Tim Cooper" at StackOverflow: http://stackoverflow.com/users/142162/tim-cooper
     * @param $haystack The Uarbithary number where find it
     * @param $needle The array with the values to get the closest one
     * @param $use_key To return the array key instead of its value
     * @param $allow_greater To allow if the closest value cam be greater than the $haystack
     * @return the closest value or key
     **/
    function bestFit($haystack,$needle,$use_key=false) {
	$closest = null;
		foreach ($needle as $key=>$item) {
			if ($closest === null || (abs($haystack - $closest) > abs($item - $haystack) && $item <= $haystack)) {
				if($use_key === true) $closest = $key;
				else $closest = $item;
			}
		}
	return $closest;
}
    
}
?>