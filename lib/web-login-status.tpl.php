<?php if(!defined('IN_UploadFile')) die(); ?>
<p><b>Login status:</b> <?php if($login['login']['result'] == "Success") { ?>
Successfully logged in as <b><?= $login['login']['lgusername'] ?></b>
<?php }else{ ?>
Error when login to your Wiki. Check your credentials in UploadFile-web.php
<?php } ?></p>