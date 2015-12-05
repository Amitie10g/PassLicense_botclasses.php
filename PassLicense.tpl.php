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
  
  <h1>PassLicense (botclasses.php)</h1>
<div>

<?php if(isset($_SESSION['result'])){ ?><div style="margin:10px">
<?php	$result = $_SESSION['result'];
	foreach($result as $key=>$item){
		if($num%2 == 0)	$bg = 'DDD';
		else $bg = 'EEE'; ?>
<div style="background:#<?= $bg ?>;margin:auto;padding:5px">
<?php if($item['edit']['result'] == 'Success') { ?><a href="<?= $site_url ?><?= $item['edit']['title'] ?>"><b><?= $item['edit']['title'] ?>:</b> Success</a><?php }else{ ?><b><?= $key ?>:</b> Error<?php } ?>
<label class="collapse" for="<?= $key ?>_details">[Details]</label>
<input id="<?= $key ?>_details" type="checkbox">
<div class="upload_details" id="<?= $key ?>_details"> 
<pre>
<?= var_dump($item); ?>
</pre>
</div>
</div>
<?php $num++; } ?>
</div>
<?php } ?>

<form method="get" action="<?= $_SERVER['PHP_SELF'] ?>">
<b>Enter a category:</b> <input style="width:300px" type="text" name="category" value="<?= $category ?>" list="categories" onchange="this.orm.submit();">
<datalist id="categories">
	<?php foreach($categories_review as $cat){ ?>
	<option value="<?= $cat ?>">
<?php } ?></datalist>
<input type="submit">
</form>
<?php if(!empty($_GET['category'])) { ?>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?pass">
<?php
		$num = 0;
		foreach($categories as $page){
			$page = str_replace(' ','_',$page);
			if($num%2 == 0)
			$bg = 'DDD';
			else $bg = 'EEE';
		
			$thumburl = $wiki->getThumbURL($page,300);
			$content = $wiki->query("?action=parse&format=php&prop=text%7Cwikitext&disabletoc=&mobileformat=&noimages=&page=".urlencode($page));
			$text = str_replace('<a','<a target="'.urlencode($page).'"',$content['parse']['text']['*']);
			$wikitext = $content['parse']['wikitext']['*'];
			$templates = $wiki->getTemplates($wikitext,$licenses);
		
?><div style="background:#<?= $bg ?>;margin:auto;padding:5px">
<input style="float:left !important" type="checkbox" name="pagename[]" value="<?= urlencode($page) ?>" />
<label style="float:left;font-weight:bold" class="collapse" for="<?= urlencode($page) ?>_details"><?= $page ?></label>
<input id="<?= urlencode($page) ?>_details" type="checkbox" />
<div class="upload_details" id="<?= urlencode($page) ?>_details"> 
<div style="margin:20px">
	<div style="position:absolute;vertical-align:middle">
		<h3 style="margin:0 0 10px 0">Select and replace tags (up to three)</h3>
		<div style="display:table-row">
			<span style="display:table-cell">Replace:&nbsp;</span>
			<span style="display:table-cell"><input style="width:300px" type="text" name="replace_1[<?= urlencode($page) ?>]" novalidate list="tags_<?= urlencode($page) ?>"></span>
			<datalist id="tags_<?= urlencode($page) ?>">
<?php foreach($templates as $template){ ?>
				<option value="<?= $template ?>">;
<?php } ?>			</datalist>
		</div>
		<div style="display:table-row">
			<span style="display:table-cell">With:&nbsp;</span>
			<span style="display:table-cell"><input style="width:300px" type="text" name="with_1[<?= urlencode($page) ?>]" novalidate list="licenses_passed"></span>
			<datalist id="licenses_passed">
<?php foreach($licenses_passed as $license){ ?>
				<option value="<?= $license ?>">
<?php } ?>			</datalist>
		</div>
		<div>&nbsp;</div>
		<div style="display:table-row">
			<span style="display:table-cell">Replace:&nbsp;</span>
			<span style="display:table-cell"><input style="width:300px" type="text" name="replace_2[<?= urlencode($page) ?>]" novalidate list="tags_<?= urlencode($page) ?>"></span>
			<datalist id="tags_<?= urlencode($page) ?>">
<?php foreach($templates as $template){ ?>
				<option value="<?= $template ?>">;
<?php } ?>			</datalist>
		</div>
		<div style="display:table-row">
			<span style="display:table-cell">With:&nbsp;</span>
			<span style="display:table-cell"><input style="width:300px" type="text" name="with_2[<?= urlencode($page) ?>]" novalidate list="licenses_passed"></span>
			<datalist id="licenses_passed">
<?php foreach($licenses_passed as $license){ ?>
				<option value="<?= $license ?>">
<?php } ?>			</datalist>
		</div>
		<div>&nbsp;</div>
		<div style="display:table-row">
			<span style="display:table-cell">Replace:&nbsp;</span>
			<span style="display:table-cell"><input style="width:300px" type="text" name="replace_3[<?= urlencode($page) ?>]" novalidate list="tags_<?= urlencode($page) ?>"></span>
			<datalist id="tags_<?= urlencode($page) ?>">
<?php foreach($templates as $template){ ?>
				<option value="<?= $template ?>">;
<?php } ?>			</datalist>
		</div>
		<div style="display:table-row">
			<span style="display:table-cell">With:&nbsp;</span>
			<span style="display:table-cell"><input style="width:300px" type="text" name="with_3[<?= urlencode($page) ?>]" novalidate list="licenses_passed"></span>
			<datalist id="licenses_passed">
<?php foreach($licenses_passed as $license){ ?>
				<option value="<?= $license ?>">
<?php } ?>			</datalist>
		</div>
	</div>
	<div style="text-align:center;min-height:300px">
		<a href="<?= $site_url ?><?= urlencode($page) ?>">
		<img src="<?= $thumburl ?>"></a>
	</div>
	<div style="clear:both">&nbsp;</div>
	<div style="border:2px #000 dotted;width:49%;height:450px;float:left;overflow:auto"><?= $text ?></div>
	<iframe name="<?= urlencode($page) ?>" style="display:inline-table;border:2px #000 dotted;width:49%;height:450px;float:right"></iframe>
</div>
</div>
<div style="clear:both;font-size:0">&nbsp;</div>
</div>
<?php $num++; } if(!empty($categories)){ ?>
<p><input type="hidden" name="category" value="<?= $_GET['category'] ?>"><input type="submit" value="Pass files"></p>
</form>
<?php }else{ ?><p style="font-style:italic">No files in this category</p><?php } } ?>
</div>
</body>
</html>