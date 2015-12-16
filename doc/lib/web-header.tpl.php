<?php if(!defined('IN_UploadFile')) die(); ?>
<html>
  <head>
    <title>UploadFile (botclasses.php) by Davod</title>
    <style>
	body{
		font-size:<?= $font_size ?>;
		font-family: Roboto, Arial sans-serif;
	}
	.checkbox, .thumb, .item, .thumb2, .col1, .col2{
		vertical-align:middle;
		display:table-cell;
		border:5px #fff solid;
	}
	.element{
		display:block;
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