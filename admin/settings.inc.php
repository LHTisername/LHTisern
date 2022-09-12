<?php

/*
        [DISCUZ!] admin/settings.inc.php - generalize settings of board
        This is NOT a freeware, use is subject to license terms

        Version: 4.0.0
        Author: Crossday (info@discuz.net)
        Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
        Last Modified: 2005-4-9 10:03
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

$query = $db->query("SELECT * FROM {$tablepre}settings");
while($setting = $db->fetch_array($query)) {
	$settings[$setting['variable']] = $setting['value'];
}

if(!submitcheck('settingsubmit')) {

	$stylelist = "<select name=\"settingsnew[styleid]\">\n";
	$query = $db->query("SELECT styleid, name FROM {$tablepre}styles");
	while($style = $db->fetch_array($query)) {
	        $selected = $style['styleid'] == $settings['styleid'] ? 'selected="selected"' : NULL;
	        $stylelist .= "<option value=\"$style[styleid]\" $selected>$style[name]</option>\n";
	}
	$stylelist .= '</select>';

	$checkrf = array($settings['regverify'] => 'checked');
	$checkarchiver = array($settings['archiverstatus'] => 'checked');
	$checkonline = array($settings['whosonlinestatus'] => 'checked');
	$checkstatusby = array($settings['userstatusby'] => 'checked');
	$checkattach = array($settings['attachsave'] => 'checked');
	$checkreport = array($settings['reportpost'] => 'checked');
	$checkfastpost = array($settings['fastpost'] => 'checked');
	$checkqihoo = array($settings['qihoostatus'] => 'checked');
	$checktimeformat = array($settings['timeformat'] == 'H:i' ? 24 : 12 => 'checked');
	$checkmoddisplay = array($settings['moddisplay'] => 'checked');
	$checkwapcharset = array($settings['wapcharset'] => 'checked');
	$checkwm = array($settings['watermarkstatus'] => 'checked');

	$checksc = array();
	$settings['seccodestatus'] = sprintf('%05b', $settings['seccodestatus']);
	for($i = 1; $i <= 5; $i++) {
		$checksc[$i] = $settings['seccodestatus'][5 - $i] ? 'checked' : '';
	}

	switch($settings['searchbox']) {
		case 1: $checksearchbox = array('forum' => 'checked'); break;
		case 2: $checksearchbox = array('thread' => 'checked'); break;
		case 3: $checksearchbox = array('forum' => 'checked', 'thread' => 'checked'); break;
		default: $checksearchbox = array(); break;
	}

	$settings['dateformat'] = str_replace('n', 'mm', $settings['dateformat']);
	$settings['dateformat'] = str_replace('j', 'dd', $settings['dateformat']);
	$settings['dateformat'] = str_replace('y', 'yy', $settings['dateformat']);
	$settings['dateformat'] = str_replace('Y', 'yyyy', $settings['dateformat']);

	$settings['wapdateformat'] = str_replace('n', 'mm', $settings['wapdateformat']);
	$settings['wapdateformat'] = str_replace('j', 'dd', $settings['wapdateformat']);
	$settings['wapdateformat'] = str_replace('y', 'yy', $settings['wapdateformat']);
	$settings['wapdateformat'] = str_replace('Y', 'yyyy', $settings['wapdateformat']);

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['settings_tips']?>
</td></tr></table>
<br>

<form method="post" name="settings" action="admincp.php?action=settings&edit=yes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?
	showtype('settings_general', 'top');
	showsetting('settings_bbname', 'settingsnew[bbname]', $settings['bbname'], 'text');
	showsetting('settings_sitename', 'settingsnew[sitename]', $settings['sitename'], 'text');
	showsetting('settings_siteurl', 'settingsnew[siteurl]', $settings['siteurl'], 'text');
	showsetting('settings_bbclosed', 'settingsnew[bbclosed]', $settings['bbclosed'], 'radio');
	showsetting('settings_closedreason', 'settingsnew[closedreason]', $settings['closedreason'], 'textarea');

	showtype('settings_access');
	showsetting('settings_regstatus', 'settingsnew[regstatus]', $settings['regstatus'], 'radio');
	showsetting('settings_censoruser', 'settingsnew[censoruser]', $settings['censoruser'], 'textarea');
	showsetting('settings_doublee', 'settingsnew[doublee]', $settings['doublee'], 'radio');
	showsetting('settings_regverify', '', '', '<input type="radio" name="settingsnew[regverify]" value="0" '.$checkrf[0].'> '.$lang['none'].'<br><input type="radio" name="settingsnew[regverify]" value="1" '.$checkrf[1].'> '.$lang['settings_regverify_email'].'<br><input type="radio" name="settingsnew[regverify]" value="2" '.$checkrf[2].'> '.$lang['settings_regverify_manual']);
	showsetting('settings_censoremail', 'settingsnew[censoremail]', $settings['censoremail'], 'textarea');
	showsetting('settings_hideprivate', 'settingsnew[hideprivate]', $settings['hideprivate'], 'radio');
	showsetting('settings_regctrl', 'settingsnew[regctrl]', $settings['regctrl'], 'text');
	showsetting('settings_ipregctrl', 'settingsnew[ipregctrl]', $settings['ipregctrl'], 'textarea');
	showsetting('settings_ipaccess', 'settingsnew[ipaccess]', $settings['ipaccess'], 'textarea');
	showsetting('settings_adminipaccess', 'settingsnew[adminipaccess]', $settings['adminipaccess'], 'textarea');
	showsetting('settings_newbiespan', 'settingsnew[newbiespan]', $settings['newbiespan'], 'text');
	showsetting('settings_welcomemsg', 'settingsnew[welcomemsg]', $settings['welcomemsg'], 'radio');
	showsetting('settings_welcomemsgtxt', 'settingsnew[welcomemsgtxt]', $settings['welcomemsgtxt'], 'textarea');
	showsetting('settings_bbrules', 'settingsnew[bbrules]', $settings['bbrules'], 'radio');
	showsetting('settings_bbrulestxt', 'settingsnew[bbrulestxt]', $settings['bbrulestxt'], 'textarea');

	showtype('settings_styles');
	showsetting('settings_styleid', '', '', $stylelist);
	showsetting('settings_tpp', 'settingsnew[topicperpage]', $settings['topicperpage'], 'text');
	showsetting('settings_ppp', 'settingsnew[postperpage]', $settings['postperpage'], 'text');
	showsetting('settings_mpp', 'settingsnew[memberperpage]', $settings['memberperpage'], 'text');
	showsetting('settings_hottopic', 'settingsnew[hottopic]', $settings['hottopic'], 'text');
	showsetting('settings_starthreshold', 'settingsnew[starthreshold]', $settings['starthreshold'], 'text');
	showsetting('settings_visitedforums', 'settingsnew[visitedforums]', $settings['visitedforums'], 'text');
	showsetting('settings_maxsigrows', 'settingsnew[maxsigrows]', $settings['maxsigrows'], 'text');
	showsetting('settings_moddisplay', '', '', '<input type="radio" name="settingsnew[moddisplay]" value="flat" '.$checkmoddisplay['flat'].'> '.$lang['settings_moddisplay_flat'].' &nbsp; <input type="radio" name="settingsnew[moddisplay]" value="selectbox" '.$checkmoddisplay['selectbox'].'> '.$lang['settings_moddisplay_selectbox']);
	showsetting('settings_subforumsindex', 'settingsnew[subforumsindex]', $settings['subforumsindex'], 'radio');
	showsetting('settings_stylejump', 'settingsnew[stylejump]', $settings['stylejump'], 'radio');
	showsetting('settings_fastpost', 'settingsnew[fastpost]', $settings['fastpost'], 'radio');

	showtype('settings_seo');
	showsetting('settings_archiverstatus', '', '', '<input type="radio" name="settingsnew[archiverstatus]" value="0" '.$checkarchiver[0].'> '.$lang['settings_archiverstatus_none'].'<br><input type="radio" name="settingsnew[archiverstatus]" value="1" '.$checkarchiver[1].'> '.$lang['settings_archiverstatus_full'].'<br><input type="radio" name="settingsnew[archiverstatus]" value="2" '.$checkarchiver[2].'> '.$lang['settings_archiverstatus_searchengine'].'<br><input type="radio" name="settingsnew[archiverstatus]" value="3" '.$checkarchiver[3].'> '.$lang['settings_archiverstatus_browser']);
	showsetting('settings_seotitle', 'settingsnew[seotitle]', $settings['seotitle'], 'text');
	showsetting('settings_seokeywords', 'settingsnew[seokeywords]', $settings['seokeywords'], 'text');
	showsetting('settings_seodescription', 'settingsnew[seodescription]', $settings['seodescription'], 'text');
	showsetting('settings_seohead', 'settingsnew[seohead]', $settings['seohead'], 'textarea');

	showtype('settings_functions');
	showsetting('settings_gzipcompress', 'settingsnew[gzipcompress]', $settings['gzipcompress'], 'radio');
	showsetting('settings_delayviewcount', 'settingsnew[delayviewcount]', $settings['delayviewcount'], 'radio');
	showsetting('settings_statstatus', 'settingsnew[statstatus]', $settings['statstatus'], 'radio');
	showsetting('settings_globalstick', 'settingsnew[globalstick]', $settings['globalstick'], 'radio');
	showsetting('settings_rssstatus', 'settingsnew[rssstatus]', $settings['rssstatus'], 'radio');
	showsetting('settings_rssttl', 'settingsnew[rssttl]', $settings['rssttl'], 'text');
	showsetting('settings_nocacheheaders', 'settingsnew[nocacheheaders]', $settings['nocacheheaders'], 'radio');
	showsetting('settings_fullmytopics', 'settingsnew[fullmytopics]', $settings['fullmytopics'], 'radio');
	showsetting('settings_qihoostatus', '', '', '<input type="radio" name="settingsnew[qihoostatus]" value="0" '.$checkqihoo[0].'> '.$lang['settings_qihoostatus_none'].'<br><input type="radio" name="settingsnew[qihoostatus]" value="1" '.$checkqihoo[1].'> '.$lang['settings_qihoostatus_enable'].'<br><input type="radio" name="settingsnew[qihoostatus]" value="2" '.$checkqihoo[2].'> '.$lang['settings_qihoostatus_default']);
	showsetting('settings_searchbox', '', '', '<input type="checkbox" name="settingsnew[searchbox][forum]" value="1" '.$checksearchbox['forum'].'> '.$lang['settings_searchbox_forum'].'<br><input type="checkbox" name="settingsnew[searchbox][thread]" value="1" '.$checksearchbox['thread'].'> '.$lang['settings_searchbox_thread']);
	showsetting('settings_debug', 'settingsnew[debug]', $settings['debug'], 'radio');
	showsetting('settings_whosonline', '', '', '<input type="radio" name="settingsnew[whosonlinestatus]" value="0" '.$checkonline[0].'> '.$lang['settings_display_none'].'<br><input type="radio" name="settingsnew[whosonlinestatus]" value="1" '.$checkonline[1].'> '.$lang['settings_whosonline_index'].'<br><input type="radio" name="settingsnew[whosonlinestatus]" value="2" '.$checkonline[2].'> '.$lang['settings_whosonline_forum'].'<br><input type="radio" name="settingsnew[whosonlinestatus]" value="3" '.$checkonline[3].'> '.$lang['settings_whosonline_both']);
	showsetting('settings_vtonlinestatus', 'settingsnew[vtonlinestatus]', $settings['vtonlinestatus'], 'radio');
	showsetting('settings_userstatusby', '', '', '<input type="radio" name="settingsnew[userstatusby]" value="0" '.$checkstatusby[0].'> '.$lang['settings_display_none'].'<br><input type="radio" name="settingsnew[userstatusby]" value="1" '.$checkstatusby[1].'> '.$lang['usergroup'].'<br><input type="radio" name="settingsnew[userstatusby]" value="2" '.$checkstatusby[2].'> '.$lang['rank']);
	showsetting('settings_forumjump', 'settingsnew[forumjump]', $settings['forumjump'], 'radio');
	showsetting('settings_dotfolders', 'settingsnew[dotfolders]', $settings['dotfolders'], 'radio');
	showsetting('settings_statscachelife', 'settingsnew[statscachelife]', $settings['statscachelife'], 'text');
	showsetting('settings_pvfrequence', 'settingsnew[pvfrequence]', $settings['pvfrequence'], 'text');
	showsetting('settings_oltimespan', 'settingsnew[oltimespan]', $settings['oltimespan'], 'text');

	showtype('settings_credits');
	echo '<tr><td colspan="2" bgcolor="'.ALTBG1.'"><table cellspacing="'.INNERBORDERWIDTH.'" cellpadding="'.TABLESPACE.'" width="100%" align="center" class="tableborder">'.
		'<tr class="header"><td colspan="7">'.$lang['settings_credits_extended'].'</td></tr>'.
		'<tr align="center" class="category"><td>'.$lang['credits_id'].'</td><td>'.$lang['credits_title'].'</td><td>'.$lang['credits_unit'].'</td><td>'.$lang['settings_credits_ratio'].'</td><td>'.$lang['settings_credits_init'].'</td><td>'.$lang['settings_credits_available'].'</td><td>'.$lang['settings_credits_show_in_thread'].'</td></tr>';
	$settings['extcredits'] = unserialize($settings['extcredits']);
	$settings['initcredits'] = explode(',', $settings['initcredits']);
	for($i = 1; $i <= 8; $i++) {
		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\">extcredits$i</td>".
			"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"8\" name=\"settingsnew[extcredits][$i][title]\" value=\"{$settings['extcredits'][$i]['title']}\"></td>".
			"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"5\" name=\"settingsnew[extcredits][$i][unit]\" value=\"{$settings['extcredits'][$i]['unit']}\"></td>".
			"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"3\" name=\"settingsnew[extcredits][$i][ratio]\" value=\"".(float)$settings['extcredits'][$i]['ratio']."\"></td>".
			"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"3\" name=\"settingsnew[initcredits][$i]\" value=\"".intval($settings['initcredits'][$i])."\"></td>".
			"<td bgcolor=\"".ALTBG2."\"><input type=\"checkbox\" name=\"settingsnew[extcredits][$i][available]\" value=\"1\" ".($settings['extcredits'][$i]['available'] ? 'checked' : '')." onclick=\"findobj('policy$i').disabled=!this.checked\"></td>".
			"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"settingsnew[extcredits][$i][showinthread]\" value=\"1\" ".($settings['extcredits'][$i]['showinthread'] ? 'checked' : '')."></td></tr>";
	}
	echo '<tr><td class="altbg1" colspan="7">'.$lang['settings_credits_extended_comment'].'</td></tr>'.
		'</table></td></tr>';

	echo '<tr><td colspan="2" bgcolor="'.ALTBG1.'"><table cellspacing="'.INNERBORDERWIDTH.'" cellpadding="'.TABLESPACE.'" width="100%" align="center" class="tableborder">'.
		'<tr class="header"><td colspan="9">'.$lang['settings_credits_policy'].'</td></tr>'.
		'<tr align="center" class="category"><td>'.$lang['credits_id'].'</td><td>'.$lang['settings_credits_policy_post'].'</td><td>'.$lang['settings_credits_policy_reply'].'</td><td>'.$lang['settings_credits_policy_digest'].'</td><td>'.$lang['settings_credits_policy_post_attach'].'</td><td>'.$lang['settings_credits_policy_get_attach'].'</td><td>'.$lang['settings_credits_policy_send_pm'].'</td><td>'.$lang['settings_credits_policy_search'].'</td><td>'.$lang['settings_credits_lowerlimit'].'</td></tr>';
	$settings['creditspolicy'] = unserialize($settings['creditspolicy']);
	for($i = 1; $i <= 8; $i++) {
		echo "<tr align=\"center\" id=\"policy$i\" ".(isset($extcredits[$i]) ? '' : 'disabled')."><td bgcolor=\"".ALTBG1."\">extcredits$i</td>".
			"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"2\" name=\"settingsnew[creditspolicy][post][$i]\" value=\"".intval($settings['creditspolicy']['post'][$i])."\"></td>".
			"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\" name=\"settingsnew[creditspolicy][reply][$i]\" value=\"".intval($settings['creditspolicy']['reply'][$i])."\"></td>".
			"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"2\" name=\"settingsnew[creditspolicy][digest][$i]\" value=\"".intval($settings['creditspolicy']['digest'][$i])."\"></td>".
			"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\" name=\"settingsnew[creditspolicy][postattach][$i]\" value=\"".intval($settings['creditspolicy']['postattach'][$i])."\"></td>".
			"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"2\" name=\"settingsnew[creditspolicy][getattach][$i]\" value=\"".intval($settings['creditspolicy']['getattach'][$i])."\"></td>".
			"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\" name=\"settingsnew[creditspolicy][pm][$i]\" value=\"".intval($settings['creditspolicy']['pm'][$i])."\"></td>".
			"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"2\" name=\"settingsnew[creditspolicy][search][$i]\" value=\"".intval($settings['creditspolicy']['search'][$i])."\"></td>".
			"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\" name=\"settingsnew[extcredits][$i][lowerlimit]\" value=\"".intval($settings['extcredits'][$i]['lowerlimit'])."\"></td></tr>";
	}
	echo '<tr><td colspan="9" class="singleborder">&nbsp;</td></tr>'.
		'<tr><td class="altbg1" align="center">'.$lang['settings_credits_policy_post'].'</td><td class="altbg2" colspan="8">'.$lang['settings_credits_policy_post_comment'].'</td></tr>'.
		'<tr><td class="altbg1" align="center">'.$lang['settings_credits_policy_reply'].'</td><td class="altbg2" colspan="8">'.$lang['settings_credits_policy_reply_comment'].'</td></tr>'.
		'<tr><td class="altbg1" align="center">'.$lang['settings_credits_policy_digest'].'</td><td class="altbg2" colspan="8">'.$lang['settings_credits_policy_digest_comment'].'</td></tr>'.
		'<tr><td class="altbg1" align="center">'.$lang['settings_credits_policy_post_attach'].'</td><td class="altbg2" colspan="8">'.$lang['settings_credits_policy_post_attach_comment'].'</td></tr>'.
		'<tr><td class="altbg1" align="center">'.$lang['settings_credits_policy_get_attach'].'</td><td class="altbg2" colspan="8">'.$lang['settings_credits_policy_get_attach_comment'].'</td></tr>'.
		'<tr><td class="altbg1" align="center">'.$lang['settings_credits_policy_send_pm'].'</td><td class="altbg2" colspan="8">'.$lang['settings_credits_policy_send_pm_comment'].'</td></tr>'.
		'<tr><td class="altbg1" align="center">'.$lang['settings_credits_policy_search'].'</td><td class="altbg2" colspan="8">'.$lang['settings_credits_policy_search_comment'].'</td></tr>'.
		'<tr><td class="altbg1" align="center">'.$lang['settings_credits_lowerlimit'].'</td><td class="altbg2" colspan="8">'.$lang['settings_credits_lowerlimit_comment'].'</td></tr>'.
		'<tr><td class="altbg1" colspan="9">'.$lang['settings_credits_policy_comment'].'</td></tr>'.
		'</table></td></tr>';
	showsetting('settings_creditsformula', 'settingsnew[creditsformula]', $settings['creditsformula'], 'textarea');

	$creditstrans = '';
	for($i = 0; $i <= 8; $i++) {
		$creditstrans .= '<option value="'.$i.'" '.($i == intval($settings['creditstrans']) ? 'selected' : '').'>'.($i ? 'extcredits'.$i : $lang['none']).'</option>';
	}
	showsetting('settings_creditstrans', '', '', '<select name="settingsnew[creditstrans]">'.$creditstrans.'</select>');
	showsetting('settings_creditstax', 'settingsnew[creditstax]', $settings['creditstax'], 'text');
	showsetting('settings_transfermincredits', 'settingsnew[transfermincredits]', $settings['transfermincredits'], 'text');
	showsetting('settings_exchangemincredits', 'settingsnew[exchangemincredits]', $settings['exchangemincredits'], 'text');
	showsetting('settings_maxincperthread', 'settingsnew[maxincperthread]', $settings['maxincperthread'], 'text');
	showsetting('settings_maxchargespan', 'settingsnew[maxchargespan]', $settings['maxchargespan'], 'text');

	showtype('settings_security');
	showsetting('settings_transsidstatus', 'settingsnew[transsidstatus]', $settings['transsidstatus'], 'radio');
	showsetting('settings_seccodestatus', '', '', '<input type="checkbox" name="settingsnew[seccodestatus][1]" value="1" '.$checksc[1].'> '.$lang['settings_seccodestatus_register'].'<br><input type="checkbox" name="settingsnew[seccodestatus][2]" value="1" '.$checksc[2].'> '.$lang['settings_seccodestatus_login'].'<br><input type="checkbox" name="settingsnew[seccodestatus][3]" value="1" '.$checksc[3].'> '.$lang['settings_seccodestatus_post'].'<br><input type="checkbox" name="settingsnew[seccodestatus][4]" value="1" '.$checksc[4].'> '.$lang['settings_seccodestatus_sendpm'].'<br><input type="checkbox" name="settingsnew[seccodestatus][5]" value="1" '.$checksc[5].'> '.$lang['settings_seccodestatus_profile']);
	showsetting('settings_maxonlines', 'settingsnew[maxonlines]', $settings['maxonlines'], 'text');
	showsetting('settings_loadctrl', 'settingsnew[loadctrl]', $settings['loadctrl'], 'text');
	showsetting('settings_floodctrl', 'settingsnew[floodctrl]', $settings['floodctrl'], 'text');
	showsetting('settings_searchctrl', 'settingsnew[searchctrl]', $settings['searchctrl'], 'text');
	showsetting('settings_regfloodctrl', 'settingsnew[regfloodctrl]', $settings['regfloodctrl'], 'text');
	showsetting('settings_maxsearchresults', 'settingsnew[maxsearchresults]', $settings['maxsearchresults'], 'text');
	showsetting('settings_maxsmilies', 'settingsnew[maxsmilies]', $settings['maxsmilies'], 'text');
	showsetting('settings_threadmaxpages', 'settingsnew[threadmaxpages]', $settings['threadmaxpages'], 'text');
	showsetting('settings_membermaxpages', 'settingsnew[membermaxpages]', $settings['membermaxpages'], 'text');

	showtype('settings_periods');
	showsetting('settings_visitbanperiods', 'settingsnew[visitbanperiods]', $settings['visitbanperiods'], 'textarea');
	showsetting('settings_postbanperiods', 'settingsnew[postbanperiods]', $settings['postbanperiods'], 'textarea');
	showsetting('settings_postmodperiods', 'settingsnew[postmodperiods]', $settings['postmodperiods'], 'textarea');
	showsetting('settings_searchbanperiods', 'settingsnew[searchbanperiods]', $settings['searchbanperiods'], 'textarea');

	showtype('settings_permissions');
	showsetting('settings_memliststatus', 'settingsnew[memliststatus]', $settings['memliststatus'], 'radio');
	showsetting('settings_modratelimit', 'settingsnew[modratelimit]', $settings['modratelimit'], 'radio');
	showsetting('settings_dupkarmarate', 'settingsnew[dupkarmarate]', $settings['dupkarmarate'], 'radio');
	showsetting('settings_reportpost', '', '', '<input type="radio" name="settingsnew[reportpost]" value="0" '.$checkreport[0].'> '.$lang['settings_reportpost_none'].'<br><input type="radio" name="settingsnew[reportpost]" value="1" '.$checkreport[1].'> '.$lang['settings_reportpost_level_1'].'<br><input type="radio" name="settingsnew[reportpost]" value="2" '.$checkreport[2].'> '.$lang['settings_reportpost_level_2'].'<br><input type="radio" name="settingsnew[reportpost]" value="3" '.$checkreport[3].'> '.$lang['settings_reportpost_level_3']);
	showsetting('settings_minpostsize', 'settingsnew[minpostsize]', $settings['minpostsize'], 'text');
	showsetting('settings_maxpostsize', 'settingsnew[maxpostsize]', $settings['maxpostsize'], 'text');
	showsetting('settings_maxavatarsize', 'settingsnew[maxavatarsize]', $settings['maxavatarsize'], 'text');
	showsetting('settings_maxavatarpixel', 'settingsnew[maxavatarpixel]', $settings['maxavatarpixel'], 'text');
	showsetting('settings_maxpolloptions', 'settingsnew[maxpolloptions]', $settings['maxpolloptions'], 'text');

	showtype('settings_attachments');
	showsetting('settings_attachimgpost', 'settingsnew[attachimgpost]', $settings['attachimgpost'], 'radio');
	showsetting('settings_attachrefcheck', 'settingsnew[attachrefcheck]', $settings['attachrefcheck'], 'radio');
	showsetting('settings_attachsave', '', '', '<input type="radio" name="settingsnew[attachsave]" value="0" '.$checkattach[0].'> '.$lang['settings_attachsave_default'].'<br><input type="radio" name="settingsnew[attachsave]" value="1" '.$checkattach[1].'> '.$lang['settings_attachsave_forum'].'<br><input type="radio" name="settingsnew[attachsave]" value="2" '.$checkattach[2].'> '.$lang['settings_attachsave_type'].'<br><input type="radio" name="settingsnew[attachsave]" value="3" '.$checkattach[3].'> '.$lang['settings_attachsave_month'].'<br><input type="radio" name="settingsnew[attachsave]" value="4" '.$checkattach[4].'> '.$lang['settings_attachsave_day']);
	showsetting('settings_watermarkstatus', '', '', '<table cellspacing="'.INNERBORDERWIDTH.'" cellpadding="'.TABLESPACE.'" class="tableborder"><tr class="category"><td colspan="3"><input type="radio" name="settingsnew[watermarkstatus]" value="0" '.$checkwm[0].'>'.$lang['settings_watermarkstatus_none'].'</td></tr><tr align="center" class="altbg2"><td><input type="radio" name="settingsnew[watermarkstatus]" value="1" '.$checkwm[1].'> #1</td><td><input type="radio" name="settingsnew[watermarkstatus]" value="2" '.$checkwm[2].'> #2</td><td><input type="radio" name="settingsnew[watermarkstatus]" value="3" '.$checkwm[3].'> #3</td></tr><tr align="center" class="altbg2"><td><input type="radio" name="settingsnew[watermarkstatus]" value="4" '.$checkwm[4].'> #4</td><td><input type="radio" name="settingsnew[watermarkstatus]" value="5" '.$checkwm[5].'> #5</td><td><input type="radio" name="settingsnew[watermarkstatus]" value="6" '.$checkwm[6].'> #6</td></tr><tr align="center" class="altbg2"><td><input type="radio" name="settingsnew[watermarkstatus]" value="7" '.$checkwm[7].'> #7</td><td><input type="radio" name="settingsnew[watermarkstatus]" value="8" '.$checkwm[8].'> #8</td><td><input type="radio" name="settingsnew[watermarkstatus]" value="9" '.$checkwm[9].'> #9</td></tr></table>');
	showsetting('settings_watermarktrans', 'settingsnew[watermarktrans]', $settings['watermarktrans'], 'text');

	showtype('settings_javascript');
	showsetting('settings_jsstatus', 'settingsnew[jsstatus]', $settings['jsstatus'], 'radio');
	showsetting('settings_jscachelife', 'settingsnew[jscachelife]', $settings['jscachelife'], 'text');
	showsetting('settings_jsrefdomains', 'settingsnew[jsrefdomains]', $settings['jsrefdomains'], 'textarea');

	showtype('settings_wap');
	showsetting('settings_wapstatus', 'settingsnew[wapstatus]', $settings['wapstatus'], 'radio');
	showsetting('settings_wapcharset', '', '', '<input type="radio" name="settingsnew[wapcharset]" value="1" '.$checkwapcharset[1].'> UTF-8 <input type="radio" name="settingsnew[wapcharset]" value="2" '.$checkwapcharset[2].'> UNICODE');
	showsetting('settings_waptpp', 'settingsnew[waptpp]', $settings['waptpp'], 'text');
	showsetting('settings_wapppp', 'settingsnew[wapppp]', $settings['wapppp'], 'text');
	showsetting('settings_wapdateformat', 'settingsnew[wapdateformat]', $settings['wapdateformat'], 'text');
	showsetting('settings_wapmps', 'settingsnew[wapmps]', $settings['wapmps'], 'text');

	showtype('settings_misc');
	showsetting('settings_timeformat', '', '', '<input type="radio" name="settingsnew[timeformat]" value="24" '.$checktimeformat[24].'> 24 Hour <input type="radio" name="settingsnew[timeformat]" value="12" '.$checktimeformat[12].'> 12 Hour</td>');
	showsetting('settings_dateformat', 'settingsnew[dateformat]', $settings['dateformat'], 'text');
	showsetting('settings_timeoffset', 'settingsnew[timeoffset]', $settings['timeoffset'], 'text');
	showsetting('settings_maxthreadads', 'settingsnew[maxthreadads]', $settings['maxthreadads'], 'text');
	showsetting('settings_karmaratelimit', 'settingsnew[karmaratelimit]', $settings['karmaratelimit'], 'text');
	showsetting('settings_losslessdel', 'settingsnew[losslessdel]', $settings['losslessdel'], 'text');
	showsetting('settings_edittimelimit', 'settingsnew[edittimelimit]', $settings['edittimelimit'], 'text');
	showsetting('settings_editby', 'settingsnew[editedby]', $settings['editedby'], 'radio');
	showsetting('settings_bannedmessages', 'settingsnew[bannedmessages]', $settings['bannedmessages'], 'radio');
	showsetting('settings_bbinsert', 'settingsnew[bbinsert]', $settings['bbinsert'], 'radio');
	showsetting('settings_smileyinsert', 'settingsnew[smileyinsert]', $settings['smileyinsert'], 'radio');
	showsetting('settings_smcols', 'settingsnew[smcols]', $settings['smcols'], 'text');
	showsetting('settings_modreasons', 'settingsnew[modreasons]', $settings['modreasons'], 'textarea');
	showtype('', 'bottom');

?>
<br><br><center><input type="submit" name="settingsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

} else {

	if(!function_exists('ob_gzhandler') && $settingsnew['gzipcompress']) {
		cpmsg('settings_gzip_invalid');
	}

	if($settingsnew['maxonlines'] > 65535 || !is_numeric($settingsnew['maxonlines'])) {
		cpmsg('settings_maxonlines_invalid');
	}

	if(!preg_match("/^([\+\-\*\/\.\d\(\)]|((extcredits[1-8]|digestposts|posts|pageviews|oltime)([\+\-\*\/\(\)]|$)+))+$/", $settingsnew['creditsformula'])
		|| !is_null(@eval(preg_replace("/(digestposts|posts|pageviews|oltime|extcredits[1-8])/", "\$\\1", $settingsnew['creditsformula']).';'))) {
		cpmsg('settings_creditsformula_invalid');
	}

	if($settingsnew['ipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['ipaccess']))) {
		if(!ipaccess($onlineip, $settingsnew['ipaccess'])) {
			cpmsg('settings_ipaccess_invalid');
		}
	}

	if($settingsnew['adminipaccess'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['adminipaccess']))) {
		if(!ipaccess($onlineip, $settingsnew['adminipaccess'])) {
			cpmsg('settings_adminipaccess_invalid');
		}
	}

	$settingsnew['bbname'] = dhtmlspecialchars($settingsnew['bbname']);
	//$settingsnew['welcomemsgtxt'] = dhtmlspecialchars($settingsnew['welcomemsgtxt']);

	$settingsnew['censoruser'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingsnew['censoruser']));
	$settingsnew['censoremail'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingsnew['censoremail']));
	$settingsnew['ipregctrl'] = trim(preg_replace("/\s*(\r\n|\n\r|\n|\r)\s*/", "\r\n", $settingsnew['ipregctrl']));
	$settingsnew['jsrefdomains'] = trim(preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n", $settingsnew['jsrefdomains']));

	$settingsnew['timeformat'] = $settingsnew['timeformat'] == '24' ? 'H:i' : 'h:i A';
	$settingsnew['dateformat'] = str_replace('mm', 'n', $settingsnew['dateformat']);
	$settingsnew['dateformat'] = str_replace('dd', 'j', $settingsnew['dateformat']);
	$settingsnew['dateformat'] = str_replace('yyyy', 'Y', $settingsnew['dateformat']);
	$settingsnew['dateformat'] = str_replace('yy', 'y', $settingsnew['dateformat']);

	$settingsnew['wapdateformat'] = str_replace('mm', 'n', $settingsnew['wapdateformat']);
	$settingsnew['wapdateformat'] = str_replace('dd', 'j', $settingsnew['wapdateformat']);
	$settingsnew['wapdateformat'] = str_replace('yyyy', 'Y', $settingsnew['wapdateformat']);
	$settingsnew['wapdateformat'] = str_replace('yy', 'y', $settingsnew['wapdateformat']);

	$settingsnew['searchbox'] = bindec(intval($settingsnew['searchbox']['thread']).intval($settingsnew['searchbox']['forum']));

	$settingsnew['seccodestatus'] = bindec(intval($settingsnew['seccodestatus'][5]).intval($settingsnew['seccodestatus'][4]).
		intval($settingsnew['seccodestatus'][3]).intval($settingsnew['seccodestatus'][2]).intval($settingsnew['seccodestatus'][1]));

	$extcreditsarray = array();
	if(is_array($settingsnew['extcredits'])) {
		foreach($settingsnew['extcredits'] as $key => $value) {
			if($value['available'] && !$value['title']) {
				cpmsg('settings_credits_title_invalid');
			}
			$extcreditsarray[$key] = array
				(
				'title'	=> dhtmlspecialchars(stripslashes($value['title'])),
				'unit' => dhtmlspecialchars(stripslashes($value['unit'])),
				'ratio' => ($value['ratio'] > 0 ? (float)$value['ratio'] : 0),
				'available' => $value['available'],
				'lowerlimit' => intval($value['lowerlimit']),
				'showinthread' => $value['showinthread']
				);
			$settingsnew['initcredits'][$key] = intval($settingsnew['initcredits'][$key]);
		}
	}

	if(is_array($settingsnew['creditspolicy'])) {
		foreach($settingsnew['creditspolicy'] as $key => $value) {
			for($i = 1; $i <= 8; $i++) {
				if(empty($value[$i])) {
					unset($settingsnew['creditspolicy'][$key][$i]);
				} else {
					$value[$i] = $value[$i] > 99 ? 99 : ($value[$i] < -99 ? -99 : $value[$i]);
					$settingsnew['creditspolicy'][$key][$i] = intval($value[$i]);
				}
			}
		}
	} else {
		$settingsnew['creditspolicy'] = array();
	}

	if($settingsnew['creditstrans'] && empty($settingsnew['extcredits'][$settingsnew['creditstrans']]['available'])) {
		cpmsg('settings_creditstrans_invalid');
	}
	$settingsnew['creditspolicy'] = addslashes(serialize($settingsnew['creditspolicy']));

	$settingsnew['creditsformulaexp'] = $settingsnew['creditsformula'];
	foreach(array('digestposts', 'posts', 'oltime', 'pageviews', 'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8') as $var) {
		if($extcreditsarray[$creditsid = preg_replace("/^extcredits(\d{1})$/", "\\1", $var)]['available']) {
			$replacement = $extcreditsarray[$creditsid]['title'];
		} else {
			$replacement = $lang['settings_creditsformula_'.$var];
		}
		$settingsnew['creditsformulaexp'] = str_replace($var, '<u>'.$replacement.'</u>', $settingsnew['creditsformulaexp']);
	}
	$settingsnew['creditsformulaexp'] = addslashes('<u>'.$lang['settings_creditsformula_credits'].'</u>='.$settingsnew['creditsformulaexp']);

	$initformula = str_replace('posts', '0', $settingsnew['creditsformula']);
	for($i = 1; $i <= 8; $i++) {
		$initformula = str_replace('extcredits'.$i, $settingsnew['initcredits'][$i], $initformula);
	}
	eval("\$initcredits = round($initformula);");

	$settingsnew['extcredits'] = addslashes(serialize($extcreditsarray));
	$settingsnew['initcredits'] = $initcredits.','.implode(',', $settingsnew['initcredits']);
	if($settingsnew['creditstax'] < 0 || $settingsnew['creditstax'] >= 1) {
		$settingsnew['creditstax'] = 0;
	}

	foreach(array('visitbanperiods', 'postbanperiods', 'postmodperiods', 'searchbanperiods') as $periods) {
		$periodarray = array();
		foreach(explode("\n", $settingsnew[$periods]) as $period) {
			if(preg_match("/^\d{1,2}\:\d{2}\-\d{1,2}\:\d{2}$/", $period = trim($period))) {
				$periodarray[] = $period;
			}
		}
		$settingsnew[$periods] = implode("\r\n", $periodarray);
	}

	foreach($settingsnew as $key => $val) {
		if(isset($settings[$key]) && $settings[$key] != $val) {
			$$key = $val;

			if(in_array($key, array('newbiespan', 'topicperpage', 'postperpage', 'memberperpage', 'hottopic', 'starthreshold',
				'visitedforums', 'maxsigrows', 'timeoffset', 'statscachelife', 'pvfrequence', 'oltimespan', 'seccodestatus',
				'maxprice', 'rssttl', 'maxonlines', 'loadctrl', 'floodctrl', 'regctrl', 'regfloodctrl', 'searchctrl',
				'extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7',
				'extcredits8', 'transfermincredits', 'exchangemincredits', 'maxincperthread', 'maxchargespan', 'maxsearchresults',
				'maxsmilies', 'threadmaxpages', 'membermaxpages', 'maxpostsize', 'minpostsize', 'maxavatarsize', 'maxavatarpixel',
				'maxpolloptions', 'maxthreadads', 'karmaratelimit', 'losslessdel', 'edittimelimit', 'smcols', 'watermarktrans',
				'jscachelife', 'waptpp', 'wapppp', 'wapmps'))) {
				$val = (float)$val;
			}
			if($key == 'userstatusby') {
				updatecache('usergroups');
			}

			$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('$key', '$val')");
		}
	}
	$db->query("ALTER TABLE {$tablepre}sessions MAX_ROWS=$settingsnew[maxonlines]");
	if ($settingsnew['maxonlines'] < $settings['maxonlines']) {
		$db->query("DELETE FROM $table_sessions");
	}

	if($settingsnew['globalstick']) {
		updatecache('globalstick');
	}

	updatecache('settings');
	cpmsg('settings_update_succeed');
}

?>