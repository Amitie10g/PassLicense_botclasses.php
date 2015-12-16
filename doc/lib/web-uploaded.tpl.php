<?php if(!defined('IN_UploadFile')) die(); ?>
<h2>Step 3: Upload results</h2>
<div>
<?php $num = 0; foreach($result as $key=>$item){ if($num%2 == 0) $bg = 'DDD'; else $bg = 'EEE'; ?>
<div style="background:#<?= $bg ?>;margin:auto">
<?php if($item['upload']['result'] == 'Success') { ?><a href="<?= $site_url ?>/wiki/File:<?= $key ?>"><b><?= $key ?>:</b> Success</a><?php }else{ ?><b><?= $key ?>:</b> Error<?php } ?>
<label class="collapse" for="<?= $key ?>_details">[Details]</label>
<input id="<?= $key ?>_details" type="checkbox">
<div class="upload_details" id="<?= $key ?>_details"> 
<pre>
<?= var_dump($item); ?>
</pre>
</div>
</div>
<?php $num++; } ?>
<p><a href="<?= $_SERVER['PHP_SELF'] ?>">Return to home</a></p>
</div>
