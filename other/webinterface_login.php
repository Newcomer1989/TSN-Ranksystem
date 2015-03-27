<?PHP
echo '<form name="form" method="post">
<table class="tablelogin">';
if (showerrlogin == 1) {
    echo '<tr><td colspan="2" class="center">' , $lang['errlogin'] , '<br><br></td></tr>';
}
echo '<tr><td class="center">' , $lang['user'] , '</td><td class="center"><input type="text" name="username"></td></tr>
<tr><td class="center">' , $lang['pass'] , '</td><td class="center"><input type="password" name="password"></td></tr>
<tr><td class="center" colspan="2"><input type="submit" name="abschicken" class="button" value="login" style="width:50px"></td></tr>
</table></form>
<script type="text/javascript">document.forms["form"].elements["username"].focus();</script>';
?>