<?php if(!defined('IN_UploadFile')) die(); ?>
<h2>Step 2: Add information</h2>
<p>Here you can add the licensing and information to your files</p>
<p>Uploading may take a while. Don't close this window or stop the process,
you will be redirected automatically when the upload process is done.</p>
<div>
<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>?upload">

<?php $num = 0; foreach($files as $item){
	if($num%2 == 0) $bg = 'DDD';
	else $bg = 'EEE';?><table style="width:900px;margin:0 auto;background:#<?= $bg ?>;">
 <tr>
  <td style="width:300px" rowspan="18"><img src="<?= $_SERVER['PHP_SELF'] ?>?img=<?= $item ?>&amp;width=300"></td>
 </tr>
 <tr>
  <td style="width:auto"><b>Filename:</b></td>
  <td style="width:100%"><input style="width:100%" type="text" value="<?= "$homepath$item" ?>" disabled=disabled></td>
 </tr>
 <tr>
  <td style="width:auto"><b>Pagename:</b></td>
  <td style="width:100%" ><input required style="width:100%" type="text" name="pagename[]" value="<?= basename($item) ?>"></td>
 </tr>
 
 <tr>
  <td colspan=2><b>Description:</b><br>
  <textarea required style="width:100%;height:6em;" name="description[]"></textarea>
  </td>
 <tr>
 
 <tr>
  <td style="width:auto"><b>Date:</td>
  <td><input required type="date" name="date[]"></td>
 <tr>
 
 <tr>
  <td style="width:auto"><b>Source:</td>
  <td><input required style="width:100%" type="text" name="source[]" value="{{own}}"></td>
 <tr>

 <tr>
  <td style="width:auto"><b>Author:</td>
  <td><input required style="width:100%" type="text" name="author[]" value="[[User:<?= $user ?>|<?= $alias ?>]]"></td>
 <tr>

 <tr>
  <td style="width:200px"><b>Optional:</td>
  <td><input style="width:100%" type="text" name="optional[]"></td>
 <tr>
 
 <tr>
  <td><b>License:</td>
  <td style="width:auto"><input required style="width:50%" name="license[]" list="licenses">
    <datalist id="licenses">
      <option data-value="0">Zero</option>
      <option data-value="1">One<option>
      <option data-value="2">Two</option>
    </datalist>
  </td>
 <tr>
 
 <tr>
  <td style="width:auto"><b>Categories:</td>
  <td><input style="width:100%" type="text" name="categories[]"></td>
 <tr>
 
 <tr>
  <td style="width:auto"><b>Summary:</td>
  <td><b><input style="width:100%" type="text" name="summary[]"></td>
 <tr>
</table>
<input style="width:100%" type="hidden" name="item[]" value="<?= "$item" ?>">
<?php $num++; } ?>
<div style="margin:auto;text-align:center"><input style="font-size:16pt" type="submit" value="Next &raquo;"></div>
</form>
</div>
