<?PHP
if ($cfg['stats_imprint_switch'] == "1") {
	if($cfg['stats_imprint_address_url'] != NULL)  {
		echo '<footer><span class="item-margin"><a href="',$cfg['stats_imprint_address_url'],'" target="_blank">',$lang['imprint'],'</span></a>';
	} else {
		echo '<footer><span class="item-margin"><a href="imprint.php">',$lang['imprint'],'</a></span>';
	}
	if($cfg['stats_imprint_privacypolicy_url'] != NULL)  {
		echo '<span class="footer-seperator"></span><span class="item-margin"><a href="',$cfg['stats_imprint_privacypolicy_url'],'" target="_blank">',$lang['privacy'],'</a></span>';
	} elseif($cfg['stats_imprint_privacypolicy'] != NULL)  {
		echo '<span class="footer-seperator"></span><span class="item-margin"><a href="privacy_policy.php">',$lang['privacy'],'</a></span>';
	}
	echo '</footer>';
}
?>