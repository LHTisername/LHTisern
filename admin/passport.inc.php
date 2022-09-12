<?php

/*
        [DISCUZ!] admin/passport.inc.php - Passport API configuration
        This is NOT a freeware, use is subject to license terms

        Version: 4.0.0
        Author: Crossday (info@discuz.net)
        Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
        Last Modified: 2005-5-19 10:03
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if(!in_array($action, array('passport', 'siteengine', 'shopex'))) {
	cpmsg('undefined_action');
}

$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable LIKE 'passport_%'");
while($setting = $db->fetch_array($query)) {
	$settings[$setting['variable']] = $setting['value'];
}

if($settings['passport_status'] && $settings['passport_status'] != $action) {
	cpmsg('passport_notmatch');
}

if(!submitcheck('ppsubmit')) {

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang[$action == 'passport' ? 'passport_tips' : 'passport_'.$action.'_tips']?>
</td></tr></table>

<form method="post" name="settings" action="admincp.php">
<input type="hidden" name="action" value="<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

	$passportcredits = '<select name="settingsnew[passport_extcredits]">';
	for($i = 0; $i <= 8; $i++) {
		$passportcredits .= '<option value="'.$i.'" '.($settings['passport_extcredits'] == $i ? 'selected' : '').'>'.($i ? 'extcredits'.$i.(!empty($extcredits[$i]['title']) ? ' ('.$extcredits[$i]['title'].')' : '') : $lang['passport_extcredits_none']).'</option>';
	}
	$passportcredits .= '</select>';

	showtype('passport_settings', 'top');
	showsetting('passport_status', 'settingsnew[passport_status]', $settings['passport_status'] == $action, 'radio');
	showsetting('passport_url', 'settingsnew[passport_url]', $settings['passport_url'], 'text');
	showsetting('passport_key', 'settingsnew[passport_key]', $settings['passport_key'], 'text');
	showsetting('passport_expire', 'settingsnew[passport_expire]', $settings['passport_expire'], 'text');

	if($action == 'siteengine') {
		echo '<input type="hidden" name="settingsnew[passport_register_url]" value="register.php">'.
			'<input type="hidden" name="settingsnew[passport_login_url]" value="misc.php?action=login">'.
			'<input type="hidden" name="settingsnew[passport_logout_url]" value="index.php?action=logout">';
	} elseif($action == 'shopex') {
		echo '<input type="hidden" name="settingsnew[passport_register_url]" value="index.php?gOo=cmVnaXN0ZXJfMS5kd3Q=">'.
			'<input type="hidden" name="settingsnew[passport_login_url]" value="index.php?gOo=bG9naW4uZHd0">'.
			'<input type="hidden" name="settingsnew[passport_logout_url]" value="index.php?gOo=bG9nb3V0X2FjdC5kbw==">';
	} else {
		showsetting('passport_register_url', 'settingsnew[passport_register_url]', $settings['passport_register_url'], 'text');
		showsetting('passport_login_url', 'settingsnew[passport_login_url]', $settings['passport_login_url'], 'text');
		showsetting('passport_logout_url', 'settingsnew[passport_logout_url]', $settings['passport_logout_url'], 'text');
	}

	showsetting('passport_extcredits', '', '', $passportcredits);
	showtype('', 'bottom');

	echo '<br><center><input type="submit" name="ppsubmit" value="'.$lang['submit'].'"></center></form>';

} else {

	if($settingsnew['passport_status']) {

		if(!$settingsnew['passport_url'] || !$settingsnew['passport_register_url'] || !$settingsnew['passport_login_url'] || !$settingsnew['passport_logout_url']) {
			cpmsg('passport_url_invalid');
		} elseif(strlen($settingsnew['passport_key']) < 10) {
			cpmsg('passport_key_invalid');
		}

		$settingsnew['passport_status'] = $action;
		$settingsnew['passport_expire'] = $settingsnew['passport_expire'] < 0 ? 0 : $settingsnew['passport_expire'];
		if(substr(($settingsnew['passport_url'] = trim($settingsnew['passport_url'])), -1) != '/') {
			$settingsnew['passport_url'] = $settingsnew['passport_url'].'/';
		}

	} else {

		$settingsnew['passport_status']
			= $settingsnew['passport_register_url']
			= $settingsnew['passport_login_url']
			= $settingsnew['passport_logout_url']
			= '';

	}

	foreach($settingsnew as $key => $val) {
		if(isset($settings[$key]) && $settings[$key] != $val) {
			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('$key', '$val')");
		}
	}

	updatecache('settings');

	cpmsg('passport_update_succeed');

}

?>