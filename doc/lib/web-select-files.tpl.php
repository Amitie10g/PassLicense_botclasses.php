<?php if(!defined('IN_UploadFile')) die(); ?>
<h2>Step 1: Select files</h2>
<p style="font-style:italic"><b>Warning:</b> This file explorer acts just a local file explorer and
can access to any file in your filesystem beside your $homepath variable.</p>

<div style="width:600px;max-height:400px">
<h3>Current directory: <span style="background-color:#CCCCCC"><?= $homepath ?></span><?= $current_dir ?></h3>

<?php if($current_dir != '/'){ ?><span style="display:block;padding:10px 0"><a href="<?= $_SERVER['PHP_SELF'] ?>?dir=<?= dirname($current_dir) ?>"><img src="up.png" alt="..">..</a></span>

<?php } if(!empty($dirlist)) foreach($dirlist as $item){ ?>
<span style="display:block;padding:10px 0 10px 5px"><a href="<?= $_SERVER['PHP_SELF'] ?>?dir=<?= $current_dir ?><?= $item ?>"><img src="folder.png" style="padding:0 10px 0 0"><?= $item ?></a></span>

<?php } ?>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?set_info">

<?php if(!empty($filelist)){ ?><div style="display:block;width:100%">
<?php	foreach($filelist as $item){ ?>
 <div class="element"><label>
  <span class="checkbox"><input type="checkbox" name="filelist[]" value="<?= $current_dir ?><?= $item ?>"></span>
  <span class="thumb"><img style="height:50px" src="<?= $_SERVER['PHP_SELF'] ?>?img=<?= $current_dir ?><?= $item ?>"></span>
  <span class="item"><?= $item ?></span></label>
 </div>
<?php } ?></div><?php } ?>
<input style="font-size:16pt" type="submit" value="Next &raquo;">
</form>
</div>
