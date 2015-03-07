<?PHP
$access=$mysqlcon->query("SELECT * FROM config");
$access=$access->fetch_row();
echo'
<table class="tabledefault">
<tr><td class="right" colspan="2"><a href="webinterface.php?logout=true">'.$lang['wilogout'].'</a></td></tr>
<tr><td class="center" colspan="4"><size1>'.$lang['wihl'].'<size1></td></tr>
<tr><td class="center" colspan="4">'.sprintf($lang['wiversion'],$access[16]).'</td></tr>
<tr><td class="center" colspan="4"><div id="alert"><sccolor>',$alert,'</sccolor></div></td></tr>
</table>

<table class="tablefunction">
<tr><td>&nbsp;</td></tr>
<tr><td class="tdheadline">
	<a href="javascript:void(0)" title="" onclick="toggle(0);" style="display:block;"><size2>'.$lang['wihlts'].'</size2></a>
</td></tr>
<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="updatets" method="post">
	<input type="hidden" name="savesettings" value="true">
	<table class="tabledefault">
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wits3host'].'<span>'.$lang['wits3hostdesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="tshost" value="',$access[2],'" tabindex="1"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wits3query'].'<span>'.$lang['wits3querydesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="tsquery" value="',$access[3],'" tabindex="2"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wits3voice'].'<span>'.$lang['wits3voicedesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="tsvoice" value="',$access[4],'" tabindex="3"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wits3querusr'].'<span>'.$lang['wits3querusrdesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="tsuser" value="',$access[5],'" tabindex="4"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wits3querpw'].'<span>'.$lang['wits3querpwdesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="tspass" value="',$access[6],'" tabindex="5"></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wits3qnm'].'<span>'.$lang['wits3qnmdesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="queryname" value="',$access[8],'" tabindex="6"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wits3qnm2'].'<span>'.$lang['wits3qnm2desc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="queryname2" value="',$access[9],'" tabindex="7"></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="updatets" value="'.$lang['wisvconf'].'" tabindex="8"></td></tr>
	</table></form>
	</div>
</td></tr>

<tr><td>&nbsp;</td></tr>
<tr><td class="tdheadline">
	<a href="javascript:void(0)" title="" onclick="toggle(1);" style="display:block;"><size2>'.$lang['wihlcfg'].'</size2></a>
</td></tr>
<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="updatecore" method="post">
	<input type="hidden" name="savesettings" value="true">
	<table class="tabledefault">
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wigrptime'].'<span>'.$lang['wigrptimedesc'].'</span></tooltip></td>
	<td class="tdlefth"><textarea rows="2" cols="30" name="grouptime" tabindex="1">',$access[10],'</textarea></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wisupidle'].'<span>'.$lang['wisupidledesc'].'</span></tooltip></td>';
	if($access[17]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch1" name="substridle" class="switch" checked tabindex="2">
	<label for="switch1">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch1" name="substridle" class="switch" tabindex="2">
	<label for="switch1">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wimsgusr'].'<span>'.$lang['wimsgusrdesc'].'</span></tooltip></td>';
	if($access[12]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch2" name="msgtouser" class="switch" checked tabindex="3">
	<label for="switch2">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch2" name="msgtouser" class="switch" tabindex="3">
	<label for="switch2">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wiexgrp'].'<span>'.$lang['wiexgrpdesc'].'</span></tooltip></td>
	<td class="tdlefth"><textarea rows="1" cols="30" name="exceptgroup" tabindex="4">',$access[19],'</textarea></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wiexuid'].'<span>'.$lang['wiexuiddesc'].'</span></tooltip></td>
	<td class="tdlefth"><textarea rows="2" cols="30" name="exceptuuid" tabindex="5">',$access[18],'</textarea></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wichdbid'].'<span>'.$lang['wichdbiddesc'].'</span></tooltip></td>';
	if($access[11]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch3" name="resetbydbchange" class="switch" checked tabindex="6">
	<label for="switch3">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch3" name="resetbydbchange" class="switch" tabindex="6">
	<label for="switch3">&nbsp;</label></div></td></tr>'; }
	echo '<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wiupcheck'].'<span>'.$lang['wiupcheckdesc'].'</span></tooltip></td>';
	if($access[13]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch4" name="upcheck" class="switch" checked tabindex="7">
	<label for="switch4">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch4" name="upcheck" class="switch" tabindex="7">
	<label for="switch4">&nbsp;</label></div></td></tr>'; }
	echo '<tr><td class="tdrighth"><tooltip>'.$lang['wiupuid'].'<span>'.$lang['wiupuiddesc'].'</span></tooltip></td>
	<td class="tdlefth"><textarea rows="2" cols="30" name="uniqueid" tabindex="8">',$access[14],'</textarea></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wiuptime'].'<span>'.$lang['wiuptimedesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="updateinfotime" value="',$access[15],'" tabindex="8"></td></tr>
	<tr><td class="center" colspan="2"></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="updatecore" value="'.$lang['wisvconf'].'" tabindex="10"></td></tr>
	</table></form>
	</div>
<tr><td class="center">

</td></tr>

<tr><td>&nbsp;</td></tr>
<tr><td class="tdheadline">
	<a href="javascript:void(0)" title="" onclick="toggle(2);" style="display:block;"><size2>'.$lang['wihlsty'].'</size2></a>
</td></tr>
<tr><td class="center">
	<div class="layers" style="display:none;">
	<table class="tabledefault">
	<form name="updatestyle" method="post">
	<input type="hidden" name="savesettings" value="true">
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wivlang'].'<span>'.sprintf($lang['wivlangdesc'],"<br>").'</span></tooltip></td>
	<td class="tdlefth"><select name="languagedb" tabindex="1">';
	echo ($language === 'en' ? '<option value="en" selected="selected">english</option>' : '<option value="en">english</option>');
	echo ($language === 'de' ? '<option value="de" selected="selected">german</option>' : '<option value="de">german</option>');
	echo ($language === 'ru' ? '<option value="ru" selected="selected">русский</option>' : '<option value="ru">русский</option>');
	echo'</select></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['widaform'].'<span>'.$lang['widaformdesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="dateformat" value="',$access[20],'" tabindex="2"></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wishexgrp'].'<span>'.$lang['wishexgrpdesc'].'</span></tooltip></td>';
	if($access[21]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch5" name="showexgrp" class="switch" checked tabindex="3">
	<label for="switch5">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch5" name="showexgrp" class="switch" tabindex="3">
	<label for="switch5">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wishexcld'].'<span>'.$lang['wishexclddesc'].'</span></tooltip></td>';
	if($access[22]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch6" name="showexcld" class="switch" checked tabindex="4">
	<label for="switch6">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch6" name="showexcld" class="switch" tabindex="4">
	<label for="switch6">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wishcolcld'].'<span>'.$lang['wishcolclddesc'].'</span></tooltip></td>';
	if($access[23]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch7" name="showcolcld" class="switch" checked tabindex="5">
	<label for="switch7">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch7" name="showcolcld" class="switch" tabindex="5">
	<label for="switch7">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wishcoluuid'].'<span>'.$lang['wishcoluuiddesc'].'</span></tooltip></td>';
	if($access[24]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch8" name="showcoluuid" class="switch" checked tabindex="6">
	<label for="switch8">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch8" name="showcoluuid" class="switch" tabindex="6">
	<label for="switch8">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wishcoldbid'].'<span>'.$lang['wishcoldbiddesc'].'</span></tooltip></td>';
	if($access[25]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch9" name="showcoldbid" class="switch" checked tabindex="7">
	<label for="switch9">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch9" name="showcoldbid" class="switch" tabindex="7">
	<label for="switch9">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wishcolot'].'<span>'.$lang['wishcolotdesc'].'</span></tooltip></td>';
	if($access[26]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch10" name="showcolot" class="switch" checked tabindex="8">
	<label for="switch10">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch10" name="showcolot" class="switch" tabindex="8">
	<label for="switch10">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wishcolit'].'<span>'.$lang['wishcolitdesc'].'</span></tooltip></td>';
	if($access[27]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch11" name="showcolit" class="switch" checked tabindex="9">
	<label for="switch11">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch11" name="showcolit" class="switch" tabindex="9">
	<label for="switch11">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wishcolat'].'<span>'.$lang['wishcolatdesc'].'</span></tooltip></td>';
	if($access[28]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch12" name="showcolat" class="switch" checked tabindex="10">
	<label for="switch12">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch12" name="showcolat" class="switch" tabindex="10">
	<label for="switch12">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wishcolnx'].'<span>'.$lang['wishcolnxdesc'].'</span></tooltip></td>';
	if($access[29]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch13" name="showcolnx" class="switch" checked tabindex="11">
	<label for="switch13">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch13" name="showcolnx" class="switch" tabindex="11">
	<label for="switch13">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td class="tdrighth"><tooltip>'.$lang['wishcolsg'].'<span>'.$lang['wishcolsgdesc'].'</span></tooltip></td>';
	if($access[30]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch14" name="showcolsg" class="switch" checked tabindex="12">
	<label for="switch14">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch14" name="showcolsg" class="switch" tabindex="12">
	<label for="switch14">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wibgco'].'<span>'.$lang['wibgcodesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="bgcolor" value="',$access[31],'" tabindex="13"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wihdco'].'<span>'.$lang['wihdcodesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="hdcolor" value="',$access[32],'" tabindex="14"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['witxco'].'<span>'.$lang['witxcodesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="txcolor" value="',$access[33],'" tabindex="15"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wihvco'].'<span>'.$lang['wihvcodesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="hvcolor" value="',$access[34],'" tabindex="16"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wiifco'].'<span>'.$lang['wiifcodesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="ifcolor" value="',$access[35],'" tabindex="17"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wiwnco'].'<span>'.$lang['wiwncodesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="wncolor" value="',$access[36],'" tabindex="18"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wiscco'].'<span>'.$lang['wisccodesc'].'</span></tooltip></td>
	<td class="tdlefth"><input type="text" name="sccolor" value="',$access[37],'" tabindex="19"></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wishgen'].'<span>'.$lang['wishgendesc'].'</span></tooltip></td>';
	if($access[38]==1)
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch15" name="showgen" class="switch" checked tabindex="20">
	<label for="switch15">&nbsp;</label></div></td></tr>'; } else
	{ echo'<td class="tdlefth"><div><input type="checkbox" id="switch15" name="showgen" class="switch" tabindex="20">
	<label for="switch15">&nbsp;</label></div></td></tr>'; }
	echo'<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="updatestyle" value="'.$lang['wisvconf'].'" tabindex="21"></td></tr>
	</table></form>
	</div>
</td></tr>

<tr><td>&nbsp;</td></tr>
<tr><td class="tdheadline">
		<a href="javascript:void(0)" title="" onclick="toggle(3);" style="display:block;"><size2>'.$lang['wihlcls'].'</size2></a>
</td></tr>
<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="selectivclients" method="post">
	<table class="tabledefault">
	<tr><td class="tdrighth"><p><tooltip>'.$lang['wiselcld'].'<span>'.$lang['wiselclddesc'].'</span></a></p></td><td class="tdlefth"><p><textarea rows="2" cols="30" id="clients" name="selectedclients" tabindex="1"></textarea></p><p><textarea style="display:none;" name="selecteduuids"></textarea></p></td></tr>
	<tr><td colspan="2"><b><i>and choose</i></b></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['widelcld'].'<span>'.$lang['widelclddesc'].'</span></tooltip></td><td class="tdlefth"><div><input type="checkbox" id="switch16" name="delclients" class="switch" tabindex="2">
	<label for="switch16">&nbsp;</label></div></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['widelsg'].'<span>'.$lang['widelsgdesc'].'</span></tooltip></td><td class="tdlefth"><div><input type="checkbox" id="switch17" name="delsrvgrp" class="switch" checked tabindex="3"><label for="switch17">&nbsp;</label></div></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="2"><b><i>or</i></b></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['setontime'].'<span>'.$lang['setontimedesc'].'</span></tooltip></td><td class="tdlefth"><input type="text" name="counttime" value="0" tabindex="4"></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" value="'.$lang['wiaction'].'" name="selectivclients" tabindex="5"></td></tr>
	</table></form>
	</div>
</td></tr>

<tr><td>&nbsp;</td></tr>
<tr><td class="tdheadline">
	<a href="javascript:void(0)" title="" onclick="toggle(4);" style="display:block;"><size2>'.$lang['wihlclg'].'</size2></a>
</td></tr>
<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="globalclients" method="post">
	<table class="tabledefault">
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['wideltime'].'<span>'.$lang['wideltimedesc'].'</span></tooltip></td><td class="tdlefth"><input type="text" name="cleantime" value="31536000" tabindex="1"></td></tr>
	<tr><td class="tdrighth"><tooltip>'.$lang['widelsg'].'<span>'.$lang['widelsgdesc'].'</span></tooltip></td><td class="tdlefth"><div><input type="checkbox" id="switch16" name="delsrvgrp" class="switch" checked tabindex="2">
	<label for="switch16">&nbsp;</label></div></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="globalclients" value="'.$lang['wiaction'].'" tabindex="3"></td></tr>
	</table></form>
	</div>
</td></tr>
</table>';
?>