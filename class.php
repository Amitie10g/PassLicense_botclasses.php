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
 *    Class "wikimedia" renamed to "wiki"
 *    Functions in Class "extended" merged into "wiki", to avoid issues
 *    Some functions modified
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
    function __construct ($url='http://en.wikipedia.org/w/api.php',$hu=null,$hp=null) {
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
     * @return The api result.
     **/
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
			throw new Exception("HTTP Error " . $this->http->http_code() );
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
    function categorymembers ($category,$limit=10,$subcat=false) {
    
        $continue = '';
        $pages = array();
        while (true) {
            $res = $this->query('?action=query&list=categorymembers&cmtype=file&cmtitle='.urlencode($category).'&format=php&cmlimit='.$limit.$continue);
            if (isset($x['error'])) {
                return false;
            }
            foreach ($res['query']['categorymembers'] as $x) {
                $pages[] = $x['title'];
            }
            if (empty($res['query-continue']['categorymembers']['cmcontinue'])) {
                if ($subcat) {
                    foreach ($pages as $p) {
                        if (substr($p,0,9)=='Category:') {
                            $pages2 = $this->categorymembers($p,true);
                            $pages = array_merge($pages,$pages2);
                        }
                    }
                }
                return $pages;
            } else {
                $continue = '&cmcontinue='.urlencode($res['query-continue']['categorymembers']['cmcontinue']);
            }
        }
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
     * @param $string The string that you want to replace.
     * @param $newstring The string that will replace the present string.
     * @param $regex if use preg_replace() instead of str_replace()
     * @return the new text of page
     **/
    function replacestring($page,$string,$newstring,$regex=false)
    {
        $data = $this->getpage( $page );
	if($regex === true) return preg_replace($string,$newstring,$data);
        else return str_replace($string,$newstring,$data);
    }
    
    /**
     * Get a template from a page
     * @param $page The page we're working with
     * @param $template The name of the template we are looking for
     * @return the searched (NULL if the template has not been found)
     **/
    function gettemplate($page,$template) {
       $data = $this->getpage($page);
       $template = preg_quote( $template, " " );
       $r = "/{{" . $template . "(?:[^{}]*(?:{{[^}]*}})?)+(?:[^}]*}})?/i";
       preg_match_all( $r, $data, $matches );
       if( isset( $matches[0][0] ) )
           return $matches[0][0];
       else
           return NULL;
    }
     
/**
 *Functions added by me
 **/
 
    function getThumbURL($page,$width=200){
	$thumbnail = $this->query("?action=query&format=php&titles=$page&prop=imageinfo&&iiprop=url&iiurlwidth=$width");
	
	$thumbnail = $thumbnail['query']['pages'];
	sort($thumbnail);
	$thumbnail = $thumbnail[0]['imageinfo']['0']['thumburl'];
	
	return $thumbnail;
    }
}
?>