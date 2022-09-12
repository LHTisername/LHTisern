<?php

/*
	[DISCUZ!] admin/avatarshow.inc.php - avatar show configuration
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2004/10/23 03:53
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'avatarshow_config') {

	if(!submitcheck('avatarsubmit')) {

		$settings = array();
		$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable IN ('avatarshowstatus', 'avatarshowpos', 'avatarshowdefault', 'avatarshowlink', 'avatarshowwidth', 'avatarshowheight')");
		while($setting = $db->fetch_array($query)) {
			$settings[$setting['variable']] = $setting['value'];
		}
		$checkstatus = array($settings['avatarshowstatus'] => 'checked');
		$checkpos = array($settings['avatarshowpos'] => 'checked');

?>
<form method="post" name="settings" action="admincp.php?action=avatarshow_config">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['avatarshow_tips']?>
</td></tr></table><br>
<?

		showtype('avatarshow', 'top');
		showsetting('avatarshow_status', '', '', '<input type="radio" name="settingsnew[avatarshowstatus]" value="1" '.$checkstatus[1].'> '.$lang['avatarshow_status_1'].'<br><input type="radio" name="settingsnew[avatarshowstatus]" value="2" '.$checkstatus[2].'> '.$lang['avatarshow_status_2'].'<br><input type="radio" name="settingsnew[avatarshowstatus]" value="0" '.$checkstatus[0].'> '.$lang['avatarshow_status_0']);
		showsetting('avatarshow_pos', '', '', '<input type="radio" name="settingsnew[avatarshowpos]" value="1" '.$checkpos[1].'> '.$lang['avatarshow_pos_1'].'<br><input type="radio" name="settingsnew[avatarshowpos]" value="2" '.$checkpos[2].'> '.$lang['avatarshow_pos_2'].'<br><input type="radio" name="settingsnew[avatarshowpos]" value="3" '.$checkpos[3].'> '.$lang['avatarshow_pos_3']);
		showsetting('avatarshow_default', 'settingsnew[avatarshowdefault]', $settings['avatarshowdefault'], 'radio');
		showsetting('avatarshow_link', 'settingsnew[avatarshowlink]', $settings['avatarshowlink'], 'radio');
		showsetting('avatarshow_width', 'settingsnew[avatarshowwidth]', $settings['avatarshowwidth'], 'text');
		showtype('', 'bottom');

		echo '<br><center><input type="submit" name="avatarsubmit" value="'.$lang['submit'].'"></form>';

	} else {

		$settingsnew['avatarshowheight'] = ceil(($settingsnew['avatarshowwidth'] * 260) / 170);
		if(is_array($settingsnew)) {
			foreach($settingsnew as $variable => $value) {
				$db->query("UPDATE {$tablepre}settings SET value='".intval($value)."' WHERE variable='$variable'");
			}
		}
		updatecache('settings');

		cpmsg('avatarshow_succeed');

	}

} elseif($action == 'avatarshow_register') {

	$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='avatarshow_lastreg'");
	$avatarshow_lastreg = $db->result($query, 0);
	if($avatarshow_lastreg && $timestamp - $avatarshow_lastreg < 72 * 3600) {
		cpmsg('avatarshow_register_duplicated');
	}

	if(!submitcheck('registersubmit')) {

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['avatarshow_register_tips']?>
</td></tr></table><br>
<?

		if($avatarshow_license == DISCUZ_AVATARSHOW) {
			echo '<form method="post" name="settings" action="admincp.php?action=avatarshow_register">'.
				'<input type="hidden" name="formhash" value="'.FORMHASH.'">';

			showtype('avatarshow_register', 'top');
			showsetting('avatarshow_register_bbname', 'avatarnew[bbname]', $bbname, 'text');
			showsetting('avatarshow_register_boardurl', 'avatarnew[boardurl]', $boardurl, 'text');
			showsetting('avatarshow_register_username', 'avatarnew[username]', '', 'text');
			showsetting('avatarshow_register_password', 'avatarnew[password]', '', 'password');
			showsetting('avatarshow_register_password_confirm', 'avatarnew[passwordconfirm]', '', 'password');
			showtype('', 'bottom');

			echo '<br><center><input type="submit" name="registersubmit" value="'.$lang['submit'].'"></form>';
		}

		echo '<br><br><form method="post" name="settings" action="admincp.php?action=avatarshow_register">'.
			'<input type="hidden" name="formhash" value="'.FORMHASH.'">';

		showtype('avatarshow_register_bind', 'top');
		showsetting('avatarshow_register_bind_license', 'avatarnew[license]', ($avatarshow_license == DISCUZ_AVATARSHOW ? '' : $avatarshow_license), 'text');
		showtype('', 'bottom');

		echo '<br><center><input type="submit" name="registersubmit" value="'.$lang['submit'].'"></form>';
		

	} else {

		if(isset($avatarnew['license'])) {

			if(strlen($avatarnew['license']) < 18 || !is_numeric($avatarnew['license'])) {
				cpmsg('avatarshow_register_license_invalid');
			}
			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('avatarshow_license', '$avatarnew[license]')");

			updatecache('settings');

			cpmsg('avatarshow_register_license_succeed');

		} else {

			if(empty($avatarnew['bbname']) || empty($avatarnew['boardurl']) || empty($avatarnew['username']) || empty($avatarnew['password'])) {
				cpmsg('avatarshow_register_required_info_invalid');
			} elseif(strlen($avatarnew['boardurl']) > 200) {
				cpmsg('avatarshow_register_url_invalid');
			} elseif($avatarnew['password'] != $avatarnew['passwordconfirm'] || strlen($avatarnew['password']) < 6 || strlen($avatarnew['password']) > 14) {
				cpmsg('avatarshow_register_password_invalid');
			} elseif(!preg_match("/^[a-z0-9\-\_\.]+$/i", $avatarnew['username']) || strlen($avatarnew['username']) < 3 || strlen($avatarnew['username']) > 20) {
				cpmsg('avatarshow_register_username_invalid');
			}

			$returnurl = $boardurl.'api/avatarreg.php';
			$key = md5(DISCUZ_AVATARSHOW.$returnurl.$avatarnew['bbname'].$avatarnew['boardurl'].$avatarnew['username'].$avatarnew['password'].'discuzjoy2005');

			cpmsg('avatarshow_register_forward', "http://www.joyinter.net/avatar/register.do?license=".DISCUZ_AVATARSHOW."&CNName=".rawurlencode($avatarnew['bbname'])."&url=".rawurlencode($avatarnew['boardurl'])."&userName=".rawurlencode($avatarnew['username'])."&password=".rawurlencode($avatarnew['password'])."&returnurl=".rawurlencode($returnurl));

		}
	}

}

?>