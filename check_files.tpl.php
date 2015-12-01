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

if(!defined('IN_PassLicense')) die(); ?><html>
  <head>
    <title>PassLicense (botclasses.php) by Davod</title>
    <style>
	body{
		font-size:12pt;
		font-family: Roboto, Arial sans-serif;
	}
	.checkbox, .thumb, .item, .thumb2, .col1, .col2{
		vertical-align:middle;
		display:table-cell;
		border:5px #fff solid;
	}
	.element{
		display:table-cell;
		border-right:30px #fff solid;
	}
	.thumb{
		width:70px;
		text-align:center;
	}
	.element2{
		margin:auto;
		display:block;
		border:2px #000 dotted;
		width:600px;
	}
	.thumb2{
		width:300px;
	}
	.upload_details{
		padding:10px;
		font-size:10pt
	}
	.collapse{
		cursor:pointer;
		-webkit-touch-callout: none;
		-webkit-user-select: none;
		-khtml-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
		float:right;
	}	
	.collapse + input{
		display:none;
	}
	.collapse + input + *{
		display:none;
	}
	.collapse+ input:checked + *{
		display:block;
	}
	.details{
		float:right;
	}
    </style>
    
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <meta charset=utf-8 />
   
  </head>
  <body>
  
  <h2>Select files</h2>
<div>
<form method="get" action="<?= $_SERVER['PHP_SELF'] ?>">
<b>Enter a category:</b> <input style="width:300px" type="text" name="category" value="<?= $category ?>"><input type="submit">
</form>
<?php if(!empty($category)) { ?>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?pass">
<p>Replace <input type="text" name="replace"> with <input type="text" name="with"></p>
<?php $num = 0; foreach($categories as $page){ $page = str_replace(' ','_',$page); if($num%2 == 0) $bg = 'DDD'; else $bg = 'EEE'; ?>
<div style="background:#<?= $bg ?>;margin:auto">
<input style="float:left !important" type="checkbox" name="pagename[]" value="<?= urlencode($page) ?>">
<label style="float:left" class="collapse" for="<?= urlencode($page) ?>_details"><?= $page ?></label>
<input id="<?= urlencode($page) ?>_details" type="checkbox">
<div class="upload_details" id="<?= urlencode($page) ?>_details"> 
<iframe style="width:100%;height:500px" src="https://commons.wikimedia.org/wiki/<?= urlencode($page) ?>?action=render"></iframe>
</div>
</div>
<div style="clear:both">&nbsp;</div>
<?php $num++; } ?>
<p><input type="submit" value="Pass files"></p>
</form>
<?php } ?>
</div>
</body>
</html>