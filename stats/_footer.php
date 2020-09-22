<?PHP
if ($cfg['stats_imprint_switch'] == "1") {
	if($cfg['stats_imprint_address_url'] != NULL)  {
		echo '<footer><a href="',$cfg['stats_imprint_address_url'],'" target="_blank">',$lang['imprint'],'</a>';
	} else {
		echo '<footer><a href="imprint.php">',$lang['imprint'],'</a>';
	}
	if($cfg['stats_imprint_privacypolicy_url'] != NULL)  {
		echo '&nbsp;•&nbsp;<a href="',$cfg['stats_imprint_privacypolicy_url'],'" target="_blank">',$lang['privacy'],'</a>';
	} elseif($cfg['stats_imprint_privacypolicy'] != NULL)  {
		echo '&nbsp;•&nbsp;<a href="privacy_policy.php">',$lang['privacy'],'</a>';
	}
	echo '</footer>';
}
?>