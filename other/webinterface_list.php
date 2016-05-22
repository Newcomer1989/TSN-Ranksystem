<?PHP
$configs = $mysqlcon->query("SELECT * FROM config");
$configs = $configs->fetch(PDO::FETCH_ASSOC);

echo '<table class="tabledefault">
	<tr><td class="right"><a href="webinterface.php?logout=true">', $lang['wilogout'], '</a></td></tr>
	<tr><td class="center size1">', $lang['wihl'], '</td></tr>
	<tr><td class="center">', sprintf($lang['wiversion'], $configs['currvers']), '</td></tr>
	<tr><td class="center"><div id="alert">', $alert, '</div></td></tr>
	</table>
	<table class="tablefunction">
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdheadlineimp size2">
	<a href="javascript:void(0)" title="" onclick="toggle(0);" style="display:block;">', $lang['wihlts'], '</a>
	</td></tr>
	<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="updatets" method="post">
	<input type="hidden" name="savesettings" value="true">
	<table class="tabledefault">
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3host'], '<span>', $lang['wits3hostdesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="tshost" value="', $configs['tshost'], '" class="width" tabindex="1"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3query'], '<span>', $lang['wits3querydesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="tsquery" value="', $configs['tsquery'], '" class="width" tabindex="2"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3voice'], '<span>', $lang['wits3voicedesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="tsvoice" value="', $configs['tsvoice'], '" class="width" tabindex="3"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3querusr'], '<span>', $lang['wits3querusrdesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="tsuser" value="', $configs['tsuser'], '" class="width" tabindex="4"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3querpw'], '<span>', $lang['wits3querpwdesc'], '</span></td>
	<td class="tdlefth"><input type="password" name="tspass" value="', $configs['tspass'], '" id="tspass" ondblclick="showpwd(\'tspass\')" onblur="hidepwd(\'tspass\')" class="width" tabindex="5"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3qnm'], '<span>', $lang['wits3qnmdesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="queryname" value="', $configs['queryname'], '" class="width" maxlength="30" tabindex="6"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3qnm2'], '<span>', $lang['wits3qnm2desc'], '</span></td>
	<td class="tdlefth"><input type="text" name="queryname2" value="', $configs['queryname2'], '" class="width" maxlength="30" tabindex="7"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3dch'], '<span>', $lang['wits3dchdesc'], '</span></td>
	<td class="tdlefth"><input type="number" name="defchid" value="', $configs['defchid'], '" class="width" tabindex="8"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wits3sm'], '<span>', $lang['wits3smdesc'], '</span><em class="elevated wncolor">&nbsp;new</em></td>
	<td class="tdlefth"><select name="slowmode" class="width" tabindex="9">';
	echo '<option value="0"'; if($configs['slowmode']=="0") echo "selected=selected"; echo '">Realtime (deactivated) [recommended]</option>';
	echo '<option value="200000"'; if($configs['slowmode']=="200000") echo "selected=selected"; echo '">Low delay (0,2s)</option>';
	echo '<option value="500000"'; if($configs['slowmode']=="500000") echo "selected=selected"; echo '">Middle delay (0,5s)</option>';
	echo '<option value="1000000"'; if($configs['slowmode']=="1000000") echo "selected=selected"; echo '">High delay (1s)</option>';
	echo '</select></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">',$lang['witime'],'<span>', $lang['witimedesc'], '</span><em class="elevated wncolor">&nbsp;new</em></td>
	<td class="tdlefth"><select name="timezone" class="width" tabindex="10">';
	$timezonearr = DateTimeZone::listIdentifiers();
	foreach ($timezonearr as $timez) {
		if ($timez == $timezone) {
			echo '<option value="'.$timezone,'" selected=selected>',$timezone,'</option>';
		} else {
			echo '<option value="',$timez,'">',$timez,'</option>';
		}
	}
	echo '</select></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="updatets" value="', $lang['wisvconf'], '" tabindex="11"></td></tr>
	</table></form>
	</div>
	</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdheadlineimp size2">
	<a href="javascript:void(0)" title="" onclick="toggle(1);" style="display:block;">', $lang['wihldb'], '</a>
	</td></tr>
	<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="updatetdbsettings" method="post">
	<input type="hidden" name="savesettings" value="true">
	<table class="tabledefault">
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">',$lang['isntwidbtype'],'<span>', $lang['isntwidbtypedesc'], '</span></td>
	<td class="tdlefth"><select name="dbtype" class="width" tabindex="1">';
	echo '<option value="cubrid"'; if($db['type']=="cubrid") echo "selected=selected"; echo '">cubrid - Cubrid</option>';
	echo '<option value="dblib"'; if($db['type']=="dblib") echo "selected=selected"; echo '">dblib - FreeTDS / Microsoft SQL Server / Sybase</option>';
	echo '<option value="firebird"'; if($db['type']=="firebird") echo "selected=selected"; echo '">firebird - Firebird/Interbase 6</option>';
	echo '<option value="ibm"'; if($db['type']=="ibm") echo "selected=selected"; echo '">ibm - IBM DB2</option>';
	echo '<option value="informix"'; if($db['type']=="informix") echo "selected=selected"; echo '">informix - IBM Informix Dynamic Server</option>';
	echo '<option value="mysql"'; if($db['type']=="mysql") echo "selected=selected"; echo '">mysql - MySQL 3.x/4.x/5.x [recommend]</option>';
	echo '<option value="oci"'; if($db['type']=="oci") echo "selected=selected"; echo '">oci - Oracle Call Interface</option>';
	echo '<option value="odbc"'; if($db['type']=="odbc") echo "selected=selected"; echo '">odbc - ODBC v3 (IBM DB2, unixODBC und win32 ODBC)</option>';
	echo '<option value="pgsql"'; if($db['type']=="pgsql") echo "selected=selected"; echo '">pgsql - PostgreSQL</option>';
	echo '<option value="sqlite"'; if($db['type']=="sqlite") echo "selected=selected"; echo '">sqlite - SQLite 3 und SQLite 2</option>';
	echo '<option value="sqlsrv"'; if($db['type']=="sqlsrv") echo "selected=selected"; echo '">sqlsrv - Microsoft SQL Server / SQL Azure</option>';
	echo '<option value="4d"'; if($db['type']=="4d") echo "selected=selected"; echo '">4d - 4D</option>';
	echo '</select></td></tr>
	<tr><td class="tdrighth tooltip">'.$lang['isntwidbhost'].'<span>', $lang['isntwidbhostdesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="dbhost" value="', $db['host'], '" class="width" tabindex="2"></td></tr>
	<tr><td class="tdrighth tooltip">'.$lang['isntwidbusr'].'<span>', $lang['isntwidbusrdesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="dbuser" value="', $db['user'], '" class="width" tabindex="3"></td></tr>
	<tr><td class="tdrighth tooltip">'.$lang['isntwidbpass'].'<span>', $lang['isntwidbpassdesc'], '</span></td>
	<td class="tdlefth"><input type="password" name="dbpass" value="', $db['pass'], '" id="dbpass" ondblclick="showpwd(\'dbpass\')" onblur="hidepwd(\'dbpass\')" class="width" tabindex="4"></td></tr>
	<tr><td class="tdrighth tooltip">'.$lang['isntwidbname'].'<span>', $lang['isntwidbnamedesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="dbname" value="', $db['dbname'], '" class="width" tabindex="5"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="updatetdbsettings" value="', $lang['wisvconf'], '" tabindex="6"></td></tr>
	</table></form>
	</div>
	</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdheadlineimp size2">
	<a href="javascript:void(0)" title="" onclick="toggle(2);" style="display:block;">', $lang['wihlcfg'], '</a>
	</td></tr>
	<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="updatecore" method="post">
	<input type="hidden" name="savesettings" value="true">
	<table class="tabledefault">
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wigrptime'], '<span>', $lang['wigrptimedesc'], '</span></td>
	<td class="tdlefth"><textarea rows="2" cols="30" name="grouptime" class="width" tabindex="1">', $configs['grouptime'], '</textarea></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wisupidle'], '<span>', $lang['wisupidledesc'], '</span></td>';
if ($configs['substridle'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch201" name="substridle" class="switch" checked class="width" tabindex="2">
	<label for="switch201">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch201" name="substridle" class="switch" class="width" tabindex="2">
	<label for="switch201">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wimsgusr'], '<span>', $lang['wimsgusrdesc'], '</span></td>';
if ($configs['msgtouser'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch202" name="msgtouser" class="switch" checked class="width" tabindex="3">
	<label for="switch202">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch202" name="msgtouser" class="switch" class="width" tabindex="3">
	<label for="switch202">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wiexgrp'], '<span>', $lang['wiexgrpdesc'], '</span></td>
	<td class="tdlefth"><textarea rows="1" cols="30" name="exceptgroup" class="width" tabindex="4">', $configs['exceptgroup'], '</textarea></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wiexuid'], '<span>', $lang['wiexuiddesc'], '</span></td>
	<td class="tdlefth"><textarea rows="2" cols="30" name="exceptuuid" class="width" tabindex="5">', $configs['exceptuuid'], '</textarea></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wiboost'], '<span>', $lang['wiboostdesc'], '</span><em class="elevated wncolor">&nbsp;new</em></td>
	<td class="tdlefth"><textarea rows="2" cols="30" name="boost" class="width" tabindex="6">', $configs['boost'], '</textarea></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wichdbid'], '<span>', $lang['wichdbiddesc'], '</span></td>';
if ($configs['resetbydbchange'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch203" name="resetbydbchange" class="switch" checked class="width" tabindex="7">
	<label for="switch203">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch203" name="resetbydbchange" class="switch" class="width" tabindex="7">
	<label for="switch203">&nbsp;</label></div></td></tr>';
}
	echo '<tr><td class="tdrighth tooltip">', $lang['cleanc'], '<span>', $lang['cleancdesc'], '</span></td>';
if ($configs['cleanclients'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch204" name="cleanclients" class="switch" checked class="width" tabindex="8">
	<label for="switch204">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch204" name="cleanclients" class="switch" class="width" tabindex="8">
	<label for="switch204">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['cleanp'], '<span>', $lang['cleanpdesc'], '</span></td>
	<td class="tdlefth"><input type="number" name="cleanperiod" value="', $configs['cleanperiod'], '" class="width" tabindex="9"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">'.$lang['wilog'].'<span>', $lang['wilogdesc'], '</span><em class="elevated wncolor">&nbsp;new</em></td>
	<td class="tdlefth"><input type="text" name="logpath" value="', $configs['logpath'], '" class="width" tabindex="10"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wiupcheck'], '<span>', $lang['wiupcheckdesc'], '</span></td>';
if ($configs['upcheck'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch205" name="upcheck" class="switch" checked class="width" tabindex="11">
	<label for="switch205">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch205" name="upcheck" class="switch" class="width" tabindex="11">
	<label for="switch205">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wiupuid'], '<span>', $lang['wiupuiddesc'], '</span></td>
	<td class="tdlefth"><textarea rows="2" cols="30" name="uniqueid" class="width" tabindex="12">', $configs['uniqueid'], '</textarea></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wiuptime'], '<span>', $lang['wiuptimedesc'], '</span></td>
	<td class="tdlefth"><input type="number" min="1800" name="updateinfotime" value="', $configs['updateinfotime'], '" class="width" tabindex="13" ></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="updatecore" value="', $lang['wisvconf'], '" tabindex="14"></td></tr>
	</table></form>
	</div>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdheadlineimp size2">
	<a href="javascript:void(0)" title="" onclick="toggle(3);" style="display:block;">', $lang['wihlsty'], '</a>
	</td></tr>
	<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="updatestyle" method="post">
	<table class="tabledefault">
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wivlang'], '<span>', sprintf($lang['wivlangdesc'], "<br>"), '</span></td>
	<td class="tdlefth"><select name="languagedb" class="width" tabindex="1">';
echo ($language === 'de' ? '<option value="de" selected="selected">'.$lang['stnv0031'].'</option>' : '<option value="de">'.$lang['stnv0031'].'</option>');
echo ($language === 'en' ? '<option value="en" selected="selected">'.$lang['stnv0032'].'</option>' : '<option value="en">'.$lang['stnv0032'].'</option>');
echo ($language === 'it' ? '<option value="it" selected="selected">'.$lang['stnv0034'].'</option>' : '<option value="it">'.$lang['stnv0034'].'</option>');
echo ($language === 'ru' ? '<option value="ru" selected="selected">'.$lang['stnv0033'].'</option>' : '<option value="ru">'.$lang['stnv0033'].'</option>');
echo '</select></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['widaform'], '<span>', $lang['widaformdesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="dateformat" value="', $configs['dateformat'], '" class="width" tabindex="2"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wishexgrp'], '<span>', $lang['wishexgrpdesc'], '</span></td>';
if ($configs['showexgrp'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch301" name="showexgrp" class="switch" checked class="width" tabindex="3">
	<label for="switch301">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch301" name="showexgrp" class="switch" class="width" tabindex="3">
	<label for="switch301">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishexcld'], '<span>', $lang['wishexclddesc'], '</span></td>';
if ($configs['showexcld'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch302" name="showexcld" class="switch" checked class="width" tabindex="4">
	<label for="switch302">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch302" name="showexcld" class="switch" class="width" tabindex="4">
	<label for="switch302">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishhicld'], '<span>', $lang['wishhiclddesc'], '</span></td>';
if ($configs['showhighest'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch303" name="showhighest" class="switch" checked class="width" tabindex="5">
	<label for="switch303">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch303" name="showhighest" class="switch" class="width" tabindex="5">
	<label for="switch303">&nbsp;</label></div></td></tr>';
}
echo '<tr><td colspan="2">&nbsp;</td></tr>
	  <tr><td class="tdrighth tooltip">', $lang['wishcolrg'], '<span>', $lang['wishcolrgdesc'], '</span></td>';
if ($configs['showcolrg'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch304" name="showcolrg" class="switch" checked class="width" tabindex="6">
	<label for="switch304">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch304" name="showcolrg" class="switch" class="width" tabindex="6">
	<label for="switch304">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcolcld'], '<span>', $lang['wishcolclddesc'], '</span></td>';
if ($configs['showcolcld'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch305" name="showcolcld" class="switch" checked class="width" tabindex="7">
	<label for="switch305">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch305" name="showcolcld" class="switch" class="width" tabindex="7">
	<label for="switch305">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcoluuid'], '<span>', $lang['wishcoluuiddesc'], '</span></td>';
if ($configs['showcoluuid'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch306" name="showcoluuid" class="switch" checked class="width" tabindex="8">
	<label for="switch306">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch306" name="showcoluuid" class="switch" class="width" tabindex="8">
	<label for="switch306">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcoldbid'], '<span>', $lang['wishcoldbiddesc'], '</span></td>';
if ($configs['showcoldbid'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch307" name="showcoldbid" class="switch" checked class="width" tabindex="9">
	<label for="switch307">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch307" name="showcoldbid" class="switch" class="width" tabindex="9">
	<label for="switch307">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcolls'], '<span>', $lang['wishcollsdesc'], '</span></td>';
if ($configs['showcolls'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch308" name="showcolls" class="switch" checked class="width" tabindex="10">
	<label for="switch308">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch308" name="showcolls" class="switch" class="width" tabindex="10">
	<label for="switch308">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcolot'], '<span>', $lang['wishcolotdesc'], '</span></td>';
if ($configs['showcolot'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch309" name="showcolot" class="switch" checked class="width" tabindex="11">
	<label for="switch309">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch309" name="showcolot" class="switch" class="width" tabindex="11">
	<label for="switch309">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcolit'], '<span>', $lang['wishcolitdesc'], '</span></td>';
if ($configs['showcolit'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch310" name="showcolit" class="switch" checked class="width" tabindex="12">
	<label for="switch310">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch310" name="showcolit" class="switch" class="width" tabindex="12">
	<label for="switch310">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcolat'], '<span>', $lang['wishcolatdesc'], '</span></td>';
if ($configs['showcolat'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch311" name="showcolat" class="switch" checked class="width" tabindex="13">
	<label for="switch311">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch311" name="showcolat" class="switch" class="width" tabindex="13">
	<label for="switch311">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcolas'], '<span>', $lang['wishcolasdesc'], '</span></td>';
if ($configs['showcolas'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch312" name="showcolas" class="switch" checked class="width" tabindex="14">
	<label for="switch312">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch312" name="showcolas" class="switch" class="width" abindex="14">
	<label for="switch312">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcolnx'], '<span>', $lang['wishcolnxdesc'], '</span></td>';
if ($configs['showcolnx'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch313" name="showcolnx" class="switch" checked class="width" tabindex="15">
	<label for="switch313">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch313" name="showcolnx" class="switch" class="width" tabindex="15">
	<label for="switch313">&nbsp;</label></div></td></tr>';
}
echo '<tr><td class="tdrighth tooltip">', $lang['wishcolsg'], '<span>', $lang['wishcolsgdesc'], '</span><em class="elevated wncolor">&nbsp;new</em></td>';
if ($configs['showcolsg'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch314" name="showcolsg" class="switch" checked class="width" tabindex="16">
	<label for="switch314">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch314" name="showcolsg" class="switch" class="width" tabindex="16">
	<label for="switch314">&nbsp;</label></div></td></tr>';
}
echo '<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wibgco'], '<span>', $lang['wibgcodesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="bgcolor" value="', $configs['bgcolor'], '" class="width" tabindex="17"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wihdco'], '<span>', $lang['wihdcodesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="hdcolor" value="', $configs['hdcolor'], '" class="width" tabindex="18"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['witxco'], '<span>', $lang['witxcodesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="txcolor" value="', $configs['txcolor'], '" class="width" tabindex="19"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wihvco'], '<span>', $lang['wihvcodesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="hvcolor" value="', $configs['hvcolor'], '" class="width" tabindex="20"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wiifco'], '<span>', $lang['wiifcodesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="ifcolor" value="', $configs['ifcolor'], '" class="width" tabindex="21"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wiwnco'], '<span>', $lang['wiwncodesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="wncolor" value="', $configs['wncolor'], '" class="width" tabindex="22"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wiscco'], '<span>', $lang['wisccodesc'], '</span></td>
	<td class="tdlefth"><input type="text" name="sccolor" value="', $configs['sccolor'], '" class="width" tabindex="23"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wishgen'], '<span>', $lang['wishgendesc'], '</span></td>';
if ($configs['showgen'] == 1) {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch315" name="showgen" class="switch" checked class="width" tabindex="24">
	<label for="switch315">&nbsp;</label></div></td></tr>';
} else {
    echo '<td class="tdlefth"><div><input type="checkbox" id="switch315" name="showgen" class="switch" class="width" tabindex="25">
	<label for="switch315">&nbsp;</label></div></td></tr>';
}
echo '<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="updatestyle" value="', $lang['wisvconf'], '" tabindex="26"><input type="hidden" name="savesettings" value="true"></td></tr>
	</table></form>
	</div>
	</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdheadline size2">
	<a href="stats/list_rankup.php?admin=true" target="_blank" style="display:block;">', $lang['wihladm'], '</a>
	</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdheadline size2">
	<a href="javascript:void(0)" title="" onclick="toggle(4);" style="display:block;">', $lang['wihlcls'], '</a>
	</td></tr>
	<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="selectivclients" method="post">
	<table class="tabledefault">
	<tr><td class="tdrighth tooltip"><p>', $lang['wiselcld'], '<span>', $lang['wiselclddesc'], '</span></p></td><td class="tdlefth"><p><textarea rows="2" cols="30" id="clients" name="selectedclients" class="width" tabindex="1"></textarea></p><p><textarea class="opacity width" name="selecteduuids" rows="2" readonly></textarea></p></td></tr>
	<tr><td colspan="2"><b><i>and choose</i></b></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['widelcld'], '<span>', $lang['widelclddesc'], '</span></td><td class="tdlefth"><div><input type="checkbox" id="switch401" name="delclients" class="switch" class="width" tabindex="2">
	<label for="switch401">&nbsp;</label></div></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['widelsg'], '<span>', $lang['widelsgdesc'], '</span></td><td class="tdlefth"><div><input type="checkbox" id="switch402" name="delsrvgrp" class="switch" class="width" tabindex="3"><label for="switch402">&nbsp;</label></div></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2"><b><i>or</i></b></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['setontime'], '<span>', $lang['setontimedesc'], '</span></td><td class="tdlefth"><input type="text" name="counttime" value="0" class="width" tabindex="4"></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" value="', $lang['wiaction'], '" name="selectivclients" tabindex="5"></td></tr>
	</table></form>
	</div>
	</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td class="tdheadline size2">
	<a href="javascript:void(0)" title="" onclick="toggle(5);" style="display:block;">', $lang['wihlclg'], '</a>
	</td></tr>
	<tr><td class="center">
	<div class="layers" style="display:none;">
	<form name="globalclients" method="post">
	<table class="tabledefault">
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['wideltime'], '<span>', $lang['wideltimedesc'], '</span></td><td class="tdlefth"><input type="text" name="cleantime" value="31536000" class="width" tabindex="1"></td></tr>
	<tr><td class="tdrighth tooltip">', $lang['widelsg'], '<span>', $lang['widelsgdesc'], '</span></td><td class="tdlefth"><div><input type="checkbox" id="switch501" name="delsrvgrp" class="switch" class="width" tabindex="2">
	<label for="switch501">&nbsp;</label></div></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2"><b><i>or</i></b></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td class="tdrighth tooltip">', $lang['widelcldgrp'], '<span>', $lang['widelcldgrpdesc'], '</span></td><td class="tdlefth"><div><input type="checkbox" id="switch502" name="delcldgrps" class="switch" class="width" tabindex="3">
	<label for="switch502">&nbsp;</label></div></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td>&nbsp;</td><td class="tdlefth"><input type="submit" name="globalclients" value="', $lang['wiaction'], '" tabindex="4"></td></tr>
	</table></form>
	</div>
</td></tr>
</table>';
?>