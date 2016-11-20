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
		background: #<?= $color_body_bg ?>;
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
		padding:5px;
		font-size:10pt;
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
	.img_bg{
		background:#fff url("img_bg.png") repeat;
	}
	
    </style>
    
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <meta charset=utf-8 />
</head>
<body>
  <h1>PassLicense (botclasses.php)</h1>
<div>

<?php if(isset($_SESSION['result'])){ ?><div style="margin-bottom:10px">
<?php	$result = $_SESSION['result'];
	foreach($result as $key=>$item){
		if($num%2 == 0)	$bg = $color_details_1;
		else $bg = $color_details_2; ?>
<div style="background:#<?= $bg ?>;margin:auto;padding:5px">
<?php if($item['edit']['result'] == 'Success') { ?><a href="<?= $wiki->site_url ?><?= $item['edit']['title'] ?>"><b><?= $item['edit']['title'] ?>:</b> Success</a><?php }else{ ?><b><?= $key ?>:</b> Error<?php } ?>
<label class="collapse" for="<?= $key ?>_details">[Details]</label>
<input id="<?= $key ?>_details" type="checkbox">
<div class="upload_details" id="<?= $key ?>_details"> 
<pre>
<?= var_dump($item); ?>
</pre>
</div>
</div>
<?php $num++;  } ?>
</div>
<?php } ?>

<form method="get" action="<?= $_SERVER['PHP_SELF'] ?>">
<b>Enter a category:</b> <input style="width:300px" type="text" name="category" value="<?= $category ?>" list="categories" onchange="this.orm.submit();">
<datalist id="categories">
	<?php foreach($categories_review as $cat){ ?>
	<option value="<?= $cat ?>">
<?php } ?></datalist>
<input type="submit" value="&#8811;"><?php if(!empty($_GET['category'])){ ?>&nbsp;<a href="<?= $uri_s[1] ?>"><?= $uri_s[2] ?></a>&nbsp;|&nbsp;<a href="<?= $uri_s[0] ?>&clear_cache">Clear cache</a><?php } ?>
</form>
<?php if(!empty($_GET['category'])){ ?><form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?pass" enctype='multipart/form-data'>
<?php
	if(!empty($pages)){
		$num = 0;
		foreach($pages as $page){
			$page = str_replace(' ','_',$page);
			if($num%2 == 0)
			$bg = $color_details_1;
			else $bg = $color_details_2;
		
			$content = $wiki->GetPageContents($page,"text|wikitext|externallinks");
			
			$external_links = $content['parse']['externallinks'];
			
			$external_info = $wiki->getExternalInfo($external_links);
			$external_license = $external_info['license'];
			$allowed = $external_info['allowed'];
			
			if(!empty($external_info['date_taken'])) $date = $external_info['date_taken'];
			else $date =  $external_info['date_uploaded'];
			
			// Hide results from unallowed licenses, or show them by passing parameter
			if($allowed !== false || isset($_GET['show_blacklisted'])){
			
				$non_empty = true;

				$wikitext = $content['parse']['wikitext']['*'];
				$templates = $wiki->getTemplates($wikitext,$licenses);			
				$thumburl = $wiki->getThumbURL($page,null,190);
				$photo_url = $external_info['photourl'];
				$external_service = $external_info['service'];
				$thumburl_big = $wiki->getThumbURL($page,600);
				$external_thumburl = $external_info['thumburl'];
				$external_origurl = $external_info['originalimageurl'];
				$text = $content['parse']['text']['*'];

?><div style="background:#<?= $bg ?>;margin:auto">
<input style="float:left !important;margin:9px" type="checkbox" name="pagename[]" value="<?= urlencode($page) ?>" />
<label style="float:left;font-weight:bold;width:97%;height:20px;margin:5px auto" class="collapse" for="<?= urlencode($page) ?>_details"><?= $page ?></label>
<input id="<?= urlencode($page) ?>_details" type="checkbox" />
<div class="upload_details" id="<?= urlencode($page) ?>_details"> 
	<div style="text-align:center;min-height:200px;margin:auto;margin-bottom:-20px">
		<div style="display:inline-table"><?php if(!empty($thumburl)){ ?>
			<div class="img_bg">
				<a href="<?= $wiki->site_url ?><?= urlencode($page) ?>" target="_blank"><img src="<?= $thumburl ?>" style="max-width:600px;max-height:190px"></a>
			</div>
			<div style="position:relative;top:-25px;background-color: rgba(204, 238, 255, 0.5);padding:5px;font-weight:bold">
				<a href="<?= $thumburl_big ?>" target="<?= urlencode($page) ?>">Bigger</a>&nbsp;|&nbsp;
				<a target="_blank" href="https://www.google.com/searchbyimage?image_url=<?= $thumburl_big ?>">Google Image search</a>
			</div>
		<?php }else{ ?>
			<div class="img_bg">
				<a href="<?= $wiki->site_url ?><?= urlencode($page) ?>" target="_blank"><img src="no_thumbnail.png"></a>
			</div>
		<?php } ?>
		</div>
		<?php if(!empty($external_thumburl)){ ?><div style="display:inline-table;margin-bottom:-50px">
			<div class="img_bg">
				<a href="<?= $photo_url ?>" target="<?= urlencode($page) ?>"><img style="height:190px !important" src="<?= $external_thumburl ?>"/></a>
			</div>
			<div style="position:relative;top:-47px;background-color: rgba(204, 238, 255, 0.5)">
			<b>Picture found at <?= ucfirst($external_service) ?></b><br>
			<b>Date:</b> <?= $date ?>
			<?php if(!empty($external_license)){ ?><br>
			<b>License:</b> <?= $external_license ?><?php } ?></div>
		</div><?php } ?>
	</div>
	<div style="width:600px;margin:auto;text-align:left">
	<input style="margin:12px 0 12px 0" type="checkbox" name="reupload[<?= urlencode($page) ?>][reupload]" id="<?= urlencode($page) ?>_reupload" />
	
	<label style="font-weight:bold;height:20px;" for="<?= urlencode($page) ?>_reupload">Reupload?</label>
	<input style="width:500px" type="text" name="reupload[<?= urlencode($page) ?>][source]" value="<?= $external_origurl ?>">
	
	</div>

	<div style="padding:10px;vertical-align:middle;width:1220px;margin:auto">
		<div style="display:table-cell;padding:5px">
			<div style="display:table-row">
				<span style="display:table-cell">Replace:&nbsp;</span>
				<span style="display:table-cell"><input style="width:380px" type="text" name="replace_1[<?= urlencode($page) ?>]" novalidate list="tags_<?= urlencode($page) ?>"></span>
				<datalist id="tags_<?= urlencode($page) ?>">
	<?php foreach($templates as $template){ ?>
					<option value="<?= $template ?>">
	<?php } foreach($external_links as $link){ if(preg_match('/^((http|https){1}\:\/\/){1}[\p{L}\p{N}\.]+\//',$link) >= 1){ $link_e[] = $link ?>
					<option value="<?= $link ?>"><?php } } ?>
				</datalist>
	
			</div>
			<div style="display:table-row">
				<span style="display:table-cell">With:&nbsp;</span>
				<span style="display:table-cell"><input style="width:380px" type="text" name="with_1[<?= urlencode($page) ?>]" novalidate list="licenses_passed"></span>
				<datalist id="licenses_passed">
	<?php foreach($licenses_replace as $license){ ?>
					<option value="<?= str_replace('<site>',$link_e[0],$license) ?>">
	<?php } ?>
				</datalist>
			</div>
		</div>
		<div style="display:table-cell;padding:5px">
			<div style="display:table-row">
				<span style="display:table-cell"><input style="width:380px" type="text" name="replace_2[<?= urlencode($page) ?>]" novalidate list="tags_<?= urlencode($page) ?>"></span>
			</div>
			<div style="display:table-row">
				<span style="display:table-cell"><input style="width:380px" type="text" name="with_2[<?= urlencode($page) ?>]" novalidate list="licenses_passed"></span>
			</div>
		</div>
		<div style="display:table-cell;padding:5px">
			<div style="display:table-row">
				<span style="display:table-cell"><input style="width:380px" type="text" name="replace_3[<?= urlencode($page) ?>]" novalidate list="tags_<?= urlencode($page) ?>"></span>
			</div>
			<div style="display:table-row">
				<span style="display:table-cell"><input style="width:380px" type="text" name="with_3[<?= urlencode($page) ?>]" novalidate list="licenses_passed"></span>
			</div>
		</div>
	</div>

	<div style="border:2px #000 dotted;width:49%;height:450px;float:left;overflow:auto">
	<?= $text ?>
	</div>
	<iframe name="<?= urlencode($page) ?>" style="display:inline-table;border:2px #000 dotted;width:49%;height:450px;float:right"></iframe>
</div>
<div style="clear:both;font-size:0">&nbsp;</div>
</div>
<?php $num++; } } } if(!empty($non_empty)){ ?>
<p><?php if(isset($_GET['show_blacklisted'])) { ?><input type="hidden" name="blacklisted" value="1"><?php } ?>
<input type="hidden" name="category" value="<?= $_GET['category'] ?>">
<input type="submit" value="Pass files &#8811;"></p>
<?php }else{ ?><i>No files in this category</i><?php } } ?>
</form>
</div>
</body>
</html>
