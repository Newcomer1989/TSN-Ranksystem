<?php
	header('Content-type: text/css');
	require_once('config.php');
?>

body{font-family:Century Gothic,sans-serif;font-size:15px;color:<?=$txcolor?>;background-color:<?=$bgcolor?>;}

a:link{text-decoration:none;color:<?=$txcolor?>;}
a:visited{text-decoration:none;color:<?=$txcolor?>;}
a:hover{text-decoration:none;color:<?=$hvcolor?>;}

th{font-weight:bold;text-align:center;}

hdcolor{color:<?=$hdcolor?>;}
ifcolor{color:<?=$ifcolor?>;}
wncolor{color:<?=$wncolor?>;}
sccolor{color:<?=$sccolor?>;}

.tabledefault{width:95%;top:10;left:10;border:0;text-align:center;font-family:Verdana;font-size:10pt;margin:0 auto;}
.tablefunction{max-width:500px;width:100%;top:10;left:10;border:0;text-align:center;font-family:Verdana;font-size:10pt;margin:0 auto;}
.tablelogin{position:fixed;top:50%;left:50%;margin-top:-40px;margin-left:-150px;}
.tdleft{width:55%;text-align:left;}
.tdlefth{width:50%;text-align:left;vertical-align:top;}
.tdred{width:45%;text-align:right;color:red;}
.tdright{width:45%;text-align:right;}
.tdrighth{width:50%;text-align:right;vertical-align:top;}

.tdheadline{text-align:center;background-color:#0A1B2A;}
.tdheadline:hover{text-align:center;background-color:#0B243B;}

.center{text-align:center;}
.right{text-align:right;}

size1{font-size:24px;font-weight:bold;}
size2{font-size:16px;font-weight:bold;}

select{width:140px;}
input.switch:empty {margin-left:-9999px;}
input.switch:empty ~ label{position:relative;float:left;line-height:1.3em;text-indent:4em;margin:0.2em 0 0 90px;cursor:pointer;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;}
input.switch:empty ~ label:before, input.switch:empty ~ label:after{position:absolute;display:block;top:0;bottom:0;left:0;content:'';width:3em;background-color:#CC0000;border-radius:1em;-webkit-transition: all 50ms ease-in;transition: all 50ms ease-in;}
input.switch:empty ~ label:after{width:1.2em;top:0.1em;bottom:0.1em;margin-left:0.2em;background-color:#fff;border-radius:0.8em;}
input.switch:checked ~ label:before{background-color:green;}
input.switch:checked ~ label:after{margin-left:1.5em;}

tooltip {position:relative;display:inline;}
tooltip span{width:350px;position:absolute;color:#000;background:#CCCCCC;padding:10px 10px 10px 10px;line-height:16px;text-align:center;visibility:hidden;border-radius:5px;box-shadow:0px 1px 2px #0B243B;}
tooltip span:after{content:'';position:absolute;top:7px;left:100%;width:0;height:0;border-left:12px solid #CCCCCC;border-top:12px solid transparent;border-bottom:12px solid transparent;}
tooltip:hover span{visibility:visible;right:110%;margin-top:-10px;margin-right:15px;z-index:999;}
