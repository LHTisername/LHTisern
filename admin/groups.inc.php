<?php

/*
	[DISCUZ!] admin/usergroups.inc.php - config of members and usergroups
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-4-1 12:50
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'admingroups') {

	$actionarray = array('settings', 'passport', 'siteengine', 'shopex', 'forumadd', 'forumsedit', 'forumsmerge',
		'forumdetail', 'forumdelete', 'moderators', 'threadtypes', 'memberadd', 'members', 'membersmerge',
		'editgroups', 'access', 'editcredits', 'editmedals', 'memberprofile', 'profilefields', 'ipban', 'usergroups',
		'admingroups', 'ranks', 'announcements', 'styles', 'templates', 'tpladd', 'tpledit', 'modmembers',
		'modthreads', 'modreplies', 'recyclebin', 'alipay', 'orders', 'forumlinks', 'onlinelist', 'medals',
		'censor', 'discuzcodes', 'smilies', 'attachtypes', 'creditslog', 'adv', 'advadd', 'advedit', 'export', 'import',
		'runquery', 'optimize', 'attachments', 'counter', 'threads', 'prune', 'pmprune', 'newsletter',
		'updatecache', 'jswizard', 'fileperms', 'illegallog', 'ratelog', 'modslog', 'medalslog', 'banlog', 'cplog',
		'errorlog', 'avatarshow_config', 'avatarshow_register');

	if(!submitcheck('groupsubmit')) {

		$query = $db->query("SELECT a.*, aa.disabledactions, u.radminid, u.grouptitle FROM {$tablepre}admingroups a
			LEFT JOIN {$tablepre}usergroups u ON u.groupid=a.admingid
			LEFT JOIN {$tablepre}adminactions aa ON aa.admingid=a.admingid
			WHERE a.admingid<>'1' ORDER BY u.radminid");

		while($group = $db->fetch_array($query)) {

?>
<br><br><form method="post" action="admincp.php?action=admingroups&edit=<?=$group['admingid']?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

			if($group['radminid'] == 1) {

				$group['disabledactions'] = $group['disabledactions'] ? unserialize($group['disabledactions']) : array();

				showtype($lang['admingroups_edit'].' - '.$group['grouptitle'], 'top');

				foreach($actionarray as $actionstr) {
					showsetting('admingroups_edit_action_'.$actionstr, 'disabledactionnew['.$actionstr.']', !in_array($actionstr, $group['disabledactions']), 'radio');
				}

				showtype('', 'bottom');

				echo "<br><center><input type=\"submit\" name=\"groupsubmit\" value=\"$lang[submit]\"><center></form>";

			} else {

				$checkstick = array($group['allowstickthread'] => 'checked');

				showtype($lang['admingroups_edit'].' - '.$group['grouptitle'], 'top');
				showsetting('admingroups_edit_edit_post', 'alloweditpostnew', $group['alloweditpost'], 'radio');
				showsetting('admingroups_edit_edit_poll', 'alloweditpollnew', $group['alloweditpoll'], 'radio');
				showsetting('admingroups_edit_stick_thread', '', '', '<input type="radio" name="allowstickthreadnew" value="0" '.$checkstick[0].'> '.$lang['admingroups_edit_stick_thread_none'].'<br><input type="radio" name="allowstickthreadnew" value="1" '.$checkstick[1].'> '.$lang['admingroups_edit_stick_thread_1'].'<br><input type="radio" name="allowstickthreadnew" value="2" '.$checkstick[2].'> '.$lang['admingroups_edit_stick_thread_2'].'<br><input type="radio" name="allowstickthreadnew" value="3" '.$checkstick[3].'> '.$lang['admingroups_edit_stick_thread_3'].'');
				showsetting('admingroups_edit_mod_post', 'allowmodpostnew', $group['allowmodpost'], 'radio');
				showsetting('admingroups_edit_del_post', 'allowdelpostnew', $group['allowdelpost'], 'radio');
				showsetting('admingroups_edit_mass_prune', 'allowmassprunenew', $group['allowmassprune'], 'radio');
				showsetting('admingroups_edit_refund', 'allowrefundnew', $group['allowrefund'], 'radio');
				showsetting('admingroups_edit_censor_word', 'allowcensorwordnew', $group['allowcensorword'], 'radio');
				showsetting('admingroups_edit_view_ip', 'allowviewipnew', $group['allowviewip'], 'radio');
				showsetting('admingroups_edit_ban_ip', 'allowbanipnew', $group['allowbanip'], 'radio');
				showsetting('admingroups_edit_edit_user', 'alloweditusernew', $group['allowedituser'], 'radio');
				showsetting('admingroups_edit_ban_user', 'allowbanusernew', $group['allowbanuser'], 'radio');
				showsetting('admingroups_edit_mod_user', 'allowmodusernew', $group['allowmoduser'], 'radio');
				showsetting('admingroups_edit_post_announce', 'allowpostannouncenew', $group['allowpostannounce'], 'radio');
				showsetting('admingroups_edit_view_log', 'allowviewlognew', $group['allowviewlog'], 'radio');
				showsetting('admingroups_edit_disable_postctrl', 'disablepostctrlnew', $group['disablepostctrl'], 'radio');
				showtype('', 'bottom');

				echo "<br><center><input type=\"submit\" name=\"groupsubmit\" value=\"$lang[submit]\"><center></form>";

			}

		}

	} else {

		$query = $db->query("SELECT groupid, radminid FROM {$tablepre}usergroups WHERE groupid='$edit'");
		if(!$group = $db->fetch_array($query)) {
			cpmsg('undefined_action');
		}

		if($group['radminid'] == 1) {

			$dactionarray = array();
			if(is_array($disabledactionnew)) {
				foreach($disabledactionnew as $key => $value) {
					if(in_array($key, $actionarray) && !$value) {
						$dactionarray[] = $key;
					}
				}
			}

			$db->query("REPLACE INTO {$tablepre}adminactions (admingid, disabledactions)
				VALUES ('$group[groupid]', '".addslashes(serialize($dactionarray))."')");
						
		} else {

			$db->query("UPDATE {$tablepre}admingroups SET alloweditpost='$alloweditpostnew', alloweditpoll='$alloweditpollnew',
				allowstickthread='$allowstickthreadnew', allowmodpost='$allowmodpostnew', allowdelpost='$allowdelpostnew',
				allowmassprune='$allowmassprunenew', allowrefund='$allowrefundnew', allowcensorword='$allowcensorwordnew',
				allowviewip='$allowviewipnew', allowbanip='$allowbanipnew', allowedituser='$alloweditusernew', allowbanuser='$allowbanusernew',
				allowmoduser='$allowmodusernew', allowpostannounce='$allowpostannouncenew', allowviewlog='$allowviewlognew',
				disablepostctrl='$disablepostctrlnew' WHERE admingid='$group[groupid]' AND admingid<>'1'");

		}

		updatecache('usergroups');
		updatecache('admingroups');
		cpmsg('admingroups_edit_succeed', 'admincp.php?action=admingroups');

	}

} elseif($action == 'usergroups') {

	if(!submitcheck('groupsubmit')) {

		if(!$edit) {
			$sgroups = $smembers = array(); $sgroupids = '0';
			$membergroup = $specialgroup = $sysgroup = '';
			$sgroupids = 0;
			$query = $db->query("SELECT groupid, type, grouptitle, creditshigher, creditslower, stars, color, groupavatar FROM {$tablepre}usergroups ORDER BY creditshigher");
			while($group = $db->fetch_array($query)) {
				if($group['type'] == 'member') {
					$membergroup .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\"></td>\n".
						"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"12\" name=\"groupnew[$group[groupid]][grouptitle]\" value=\"$group[grouptitle]\"></td>\n".
						"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"6\" name=\"groupnew[$group[groupid]][creditshigher]\" value=\"$group[creditshigher]\">\n".
						"<td bgcolor=\"".ALTBG2."\">$group[creditslower]\n".
						"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\"name=\"groupnew[$group[groupid]][stars]\" value=\"$group[stars]\"></td>\n".
						"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"6\"name=\"groupnew[$group[groupid]][color]\" value=\"$group[color]\"></td>\n".
						"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"12\" name=\"groupnew[$group[groupid]][groupavatar]\" value=\"$group[groupavatar]\"></td>".
						"<td bgcolor=\"".ALTBG2."\" nowrap><a href=\"admincp.php?action=usergroups&edit=$group[groupid]\">[$lang[detail]]</a></td></tr>\n";
				} elseif($group['type'] == 'system') {
					$sysgroup .= "<tr align=\"center\">\n".
						"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\"></td>\n".
						"<td bgcolor=\"".ALTBG1."\">".$lang['usergroups_system_'.$group['groupid']]."</td>\n".
						"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\"></td>\n".
						"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\"></td>\n".
						"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"12\" name=\"group_avatar[$group[groupid]]\" value=\"$group[groupavatar]\"></td>\n".
						"<td bgcolor=\"".ALTBG1."\" nowrap><a href=\"admincp.php?action=usergroups&edit=$group[groupid]\">[$lang[detail]]</a></td></tr>\n";
				} elseif($group['type'] == 'special') {
					$sgroups[] = $group;
					$sgroupids .= ','.$group['groupid'];
				}
			}

			$query = $db->query("SELECT uid, username, groupid FROM {$tablepre}members WHERE groupid IN ($sgroupids)");
			while($member = $db->fetch_array($query)) {
				$smembers[$member['groupid']][] = '<a href="viewpro.php?uid='.$member['uid'].'" target="_blank">'.$member['username'].'</a>';
			}

			foreach($sgroups as $group) {
				if(is_array($smembers[$group['groupid']])) {
					$num = count($smembers[$group['groupid']]);
					$specifiedusers = $num <= 200 ? implode(', ', $smembers[$group['groupid']]) :
						implode(', ', array_slice($smembers[$group['groupid']], 0, 200)).$lang['usergroups_specified_members_leaved_out'];

					unset($smembers[$group['groupid']]);
				} else {
					$specifiedusers = '';
					$num = 0;
				}

				$specialgroup .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[$group[groupid]]\" value=\"$group[groupid]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"12\" name=\"group_title[$group[groupid]]\" value=\"$group[grouptitle]\"></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><span class=\"smalltxt\">$specifiedusers</span></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><span class=\"smalltxt\">$num</span></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\"name=\"group_stars[$group[groupid]]\" value=\"$group[stars]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"6\"name=\"group_color[$group[groupid]]\" value=\"$group[color]\"></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"12\" name=\"group_avatar[$group[groupid]]\" value=\"$group[groupavatar]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\" nowrap><a href=\"admincp.php?action=usergroups&edit=$group[groupid]\">[$lang[detail]]</a></td></tr>\n";
			}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['usergroups_tips']?>
</td></tr></table>

<form method="post" action="admincp.php?action=usergroups&type=member">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td colspan="8"><?=$lang['usergroups_member']?> - <?=$lang['usergroups_detail']?></td></tr>
<tr class="category" align="center"><td width="48"><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['usergroups_title']?></td><td><?=$lang['members_creditshigher']?></td><td><?=$lang['members_creditslower']?></td><td><?=$lang['usergroups_stars']?></td><td><?=$lang['usergroups_color']?></td><td><?=$lang['usergroups_avatar']?></td><td><?=$lang['edit']?></td></tr>
<?=$membergroup?>
<tr><td colspan="8" class="singleborder">&nbsp;</td></tr>
<tr align="center" bgcolor="<?=ALTBG1?>"><td><?=$lang['add_new']?></td>
<td><input type="text" size="12" name="groupnew[0][grouptitle]"></td>
<td><input type="text" size="6" name="groupnew[0][creditshigher]"></td>
<td>&nbsp;</td>
<td><input type="text" size="2" name="groupnew[0][stars]"></td>
<td><input type="text" size="6" name="groupnew[0][color]"></td>
<td><input type="text" size="12" name="groupnew[0][groupavatar]"></td>
<td>&nbsp;</td>
</tr></table><br><center><?=$warning?>
<input type="submit" name="groupsubmit" value="<?=$lang['submit']?>">&nbsp;
</form><br><br>

<form method="post" action="admincp.php?action=usergroups&type=special">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td colspan="8"><?=$lang['usergroups_special']?> - <?=$lang['usergroups_detail']?></td></tr>
<tr class="category" align="center"><td width="48"><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td nowrap><?=$lang['usergroups_title']?></td><td><?=$lang['usergroups_specified_members']?></td><td nowrap><?=$lang['members']?><td nowrap><?=$lang['usergroups_stars']?></td><td nowrap><?=$lang['usergroups_color']?></td><td nowrap><?=$lang['usergroups_avatar']?></td><td nowrap><?=$lang['edit']?></td></tr>
<?=$specialgroup?>
<tr><td colspan="8" class="singleborder">&nbsp;</td></tr>
<tr align="center" bgcolor="<?=ALTBG1?>"><td><?=$lang['add_new']?></td>
<td><input type="text" size="12" name="grouptitlenew"></td>
<td>&nbsp;</td><td>&nbsp;</td>
<td><input type="text" size="2" name="starsnew"></td>
<td><input type="text" size="6" name="colornew"></td>
<td><input type="text" size="12" name="groupavatarnew"></td>
<td>&nbsp;</td>
</tr></table><br><center>
<input type="submit" name="groupsubmit" value="<?=$lang['submit']?>"></center></form><br><br>

<form method="post" action="admincp.php?action=usergroups&type=system">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['usergroups_system']?> - <?=$lang['usergroups_detail']?></td></tr>
<tr class="category" align="center">
<td><?=$lang['usergroups_title']?></td><td><?=$lang['usergroups_status']?></td><td><?=$lang['usergroups_stars']?></td><td><?=$lang['usergroups_color']?><td><?=$lang['usergroups_avatar']?></td><td><?=$lang['edit']?></td></tr>
<?=$sysgroup?>
</table><br><center>
<input type="submit" name="groupsubmit" value="<?=$lang['submit']?>"></center></form>
<?

		} else {

			$query = $db->query("SELECT * FROM {$tablepre}usergroups WHERE groupid='$edit'");
			$group = $db->fetch_array($query);

			if(!submitcheck('detailsubmit')) {
				$checksearch = array($group['allowsearch'] => 'checked');
				$checkavatar = array($group['allowavatar'] => 'checked');
				$checkreasonpm = array($group['reasonpm'] => 'checked');
				$checkdirectpost = array($group['allowdirectpost'] => 'checked');

				echo "<form method=\"post\" action=\"admincp.php?action=usergroups&edit=$edit&formhash=".FORMHASH."\">\n";

				showtype('usergroups_edit', 'top');
				showsetting('usergroups_edit_title', 'grouptitlenew', $group['grouptitle'], 'text');
				if($group['type'] == 'special') {
					$selectra = array($group['radminid'] => 'selected="selected"');
					showsetting('usergroups_edit_radminid', '', '', "<select name=\"radminidnew\"><option value=\"0\" $selectra[0]>$lang[none]</option><option value=\"1\" $selectra[1]>$lang[usergroups_system_1]</option><option value=\"2\" $selectra[2]>$lang[usergroups_system_2]</option><option value=\"3\" $selectra[3]>$lang[usergroups_system_3]</option>");

					showtype('usergroups_edit_system');
					echo '<tr><td colspan="2" class="altbg2">'.$lang['usergroups_edit_system_comment'].'</td></tr>';
					if($group['system'] == 'private') {
						$system = array('public' => 0, 'dailyprice' => 0, 'minspan' => 0);
					} else {
						$system = array('public' => 1, 'dailyprice' => 0, 'minspan' => 0);
						list($system['dailyprice'], $system['minspan']) = explode("\t", $group['system']);
					}
					showsetting('usergroups_edit_system_public', 'system_publicnew', $system['public'], 'radio');
					showsetting('usergroups_edit_system_dailyprice', 'system_dailypricenew', $system['dailyprice'], 'text');
					showsetting('usergroups_edit_system_minspan', 'system_minspannew', $system['minspan'], 'text');
				}

				showtype('usergroups_edit_basic');
				if($group['groupid'] == 7) {
					echo '<input type="hidden" name="allowvisitnew" value="1">';
				} else {
					showsetting('usergroups_edit_visit', 'allowvisitnew', $group['allowvisit'], 'radio');
				}
				showsetting('usergroups_edit_read_access', 'readaccessnew', $group['readaccess'], 'text');
				showsetting('usergroups_edit_view_profile', 'allowviewpronew', $group['allowviewpro'], 'radio');
				showsetting('usergroups_edit_view_stats', 'allowviewstatsnew', $group['allowviewstats'], 'radio');
				showsetting('usergroups_edit_invisible', 'allowinvisiblenew', $group['allowinvisible'], 'radio');
				showsetting('usergroups_edit_multigroups', 'allowmultigroupsnew', $group['allowmultigroups'], 'radio');
				showsetting('usergroups_edit_allowtransfer', 'allowtransfernew', $group['allowtransfer'], 'radio');
				showsetting('usergroups_edit_search', '', '', "<input type=\"radio\" name=\"allowsearchnew\" value=\"0\" $checksearch[0]> $lang[usergroups_edit_search_disable]<br><input type=\"radio\" name=\"allowsearchnew\" value=\"1\" $checksearch[1]> $lang[usergroups_edit_search_thread]<br><input type=\"radio\" name=\"allowsearchnew\" value=\"2\" $checksearch[2]> $lang[usergroups_edit_search_post]");
				showsetting('usergroups_edit_avatar', '', '', "<input type=\"radio\" name=\"allowavatarnew\" value=\"0\" $checkavatar[0]> $lang[usergroups_edit_avatar_disable]<br><input type=\"radio\" name=\"allowavatarnew\" value=\"1\" $checkavatar[1]> $lang[usergroups_edit_avatar_board]<br><input type=\"radio\" name=\"allowavatarnew\" value=\"2\" $checkavatar[2]> $lang[usergroups_edit_avatar_custom]<br><input type=\"radio\" name=\"allowavatarnew\" value=\"3\" $checkavatar[3]> $lang[usergroups_edit_avatar_upload]");
				showsetting('usergroups_edit_reasonpm', '', '', "<input type=\"radio\" name=\"reasonpmnew\" value=\"0\" $checkreasonpm[0]> $lang[usergroups_edit_reasonpm_none]<br><input type=\"radio\" name=\"reasonpmnew\" value=\"1\" $checkreasonpm[1]> $lang[usergroups_edit_reasonpm_reason]<br><input type=\"radio\" name=\"reasonpmnew\" value=\"2\" $checkreasonpm[2]> $lang[usergroups_edit_reasonpm_pm]<br><input type=\"radio\" name=\"reasonpmnew\" value=\"3\" $checkreasonpm[3]> $lang[usergroups_edit_reasonpm_both]");
				showsetting('usergroups_edit_blog', 'allowuseblognew', $group['allowuseblog'], 'radio');
				showsetting('usergroups_edit_nickname', 'allownicknamenew', $group['allownickname'], 'radio');
				showsetting('usergroups_edit_cstatus', 'allowcstatusnew', $group['allowcstatus'], 'radio');
				showsetting('usergroups_edit_disable_periodctrl', 'disableperiodctrlnew', $group['disableperiodctrl'], 'radio');
				showsetting('usergroups_edit_max_pm_num', 'maxpmnumnew', $group['maxpmnum'], 'text');

				showtype('usergroups_edit_thread');
				showsetting('usergroups_edit_post', 'allowpostnew', $group['allowpost'], 'radio');
				showsetting('usergroups_edit_reply', 'allowreplynew', $group['allowreply'], 'radio');
				showsetting('usergroups_edit_post_poll', 'allowpostpollnew', $group['allowpostpoll'], 'radio');
				showsetting('usergroups_edit_vote', 'allowvotenew', $group['allowvote'], 'radio');
				showsetting('usergroups_edit_direct_post', '', '', "<input type=\"radio\" name=\"allowdirectpostnew\" value=\"0\" $checkdirectpost[0]> $lang[usergroups_edit_direct_post_none]<br><input type=\"radio\" name=\"allowdirectpostnew\" value=\"1\" $checkdirectpost[1]> $lang[usergroups_edit_direct_post_reply]<br><input type=\"radio\" name=\"allowdirectpostnew\" value=\"2\" $checkdirectpost[2]> $lang[usergroups_edit_direct_post_thread]<br><input type=\"radio\" name=\"allowdirectpostnew\" value=\"3\" $checkdirectpost[3]> $lang[usergroups_edit_direct_post_all]");
				showsetting('usergroups_edit_set_read_perm', 'allowsetreadpermnew', $group['allowsetreadperm'], 'radio');
				showsetting('usergroups_edit_maxprice', 'maxpricenew', $group['maxprice'], 'text');
				showsetting('usergroups_edit_hide_code', 'allowhidecodenew', $group['allowhidecode'], 'radio');
				showsetting('usergroups_edit_html', 'allowhtmlnew', $group['allowhtml'], 'radio');
				showsetting('usergroups_edit_custom_bbcode', 'allowcusbbcodenew', $group['allowcusbbcode'], 'radio');
				showsetting('usergroups_edit_sig_bbcode', 'allowsigbbcodenew', $group['allowsigbbcode'], 'radio');
				showsetting('usergroups_edit_sig_img_code', 'allowsigimgcodenew', $group['allowsigimgcode'], 'radio');
				showsetting('usergroups_edit_max_sig_size', 'maxsigsizenew', $group['maxsigsize'], 'text');

				showtype('usergroups_edit_attachment');
				showsetting('usergroups_edit_get_attach', 'allowgetattachnew', $group['allowgetattach'], 'radio');
				showsetting('usergroups_edit_post_attach', 'allowpostattachnew', $group['allowpostattach'], 'radio');
				showsetting('usergroups_edit_set_attach_perm', 'allowsetattachpermnew', $group['allowsetattachperm'], 'radio');
				showsetting('usergroups_edit_max_attach_size', 'maxattachsizenew', $group['maxattachsize'], 'text');
				showsetting('usergroups_edit_max_size_per_day', 'maxsizeperdaynew', $group['maxsizeperday'], 'text');
				showsetting('usergroups_edit_attach_ext', 'attachextensionsnew', $group['attachextensions'], 'text');

				showtype('usergroups_edit_credits');
				$raterangearray = array();
				foreach(explode("\n", $group['raterange']) as $range) {
					$range = explode("\t", $range);
					$raterangearray[$range[0]] = array('min' => $range[1], 'max' => $range[2], 'mrpd' => $range[3]);
				}
				echo '<tr><td colspan="2" bgcolor="'.ALTBG1.'"><table cellspacing="'.INNERBORDERWIDTH.'" cellpadding="'.TABLESPACE.'" width="100%" align="center" class="tableborder">'.
					'<tr class="header"><td colspan="6">'.$lang['usergroups_edit_raterange'].'</td></tr>'.
					'<tr align="center" class="category"><td>&nbsp;</td><td>'.$lang['credits_id'].'</td><td>'.$lang['credits_title'].'</td><td>'.$lang['usergroups_edit_raterange_min'].'</td><td>'.$lang['usergroups_edit_raterange_max'].'</td><td>'.$lang['usergroups_edit_raterange_mrpd'].'</td></tr>';
				for($i = 1; $i <= 8; $i++) {
					echo '<tr align="center" '.(isset($extcredits[$i]) ? '' : 'disabled').'><td bgcolor="'.ALTBG1.'"><input type="checkbox" name="raterangenew['.$i.'][allowrate]" value="1" '.(empty($raterangearray[$i]) ? '' : 'checked').'></td>'.
						'<td bgcolor="'.ALTBG2.'">extcredits'.$i.'</td>'.
						'<td bgcolor="'.ALTBG1.'">'.$extcredits[$i]['title'].'</td>'.
						'<td bgcolor="'.ALTBG2.'"><input type="text" name="raterangenew['.$i.'][min]" size="3" value="'.$raterangearray[$i]['min'].'"></td>'.
						'<td bgcolor="'.ALTBG1.'"><input type="text" name="raterangenew['.$i.'][max]" size="3" value="'.$raterangearray[$i]['max'].'"></td>'.
						'<td bgcolor="'.ALTBG2.'"><input type="text" name="raterangenew['.$i.'][mrpd]" size="3" value="'.$raterangearray[$i]['mrpd'].'"></td></tr>';
				}
				echo '<tr><td colspan="6" bgcolor="'.ALTBG2.'">'.$lang['usergroups_edit_raterange_comment'].'</td></tr></table></td></tr>';
				showtype('', 'bottom');

				echo "<br><center><input type=\"submit\" name=\"detailsubmit\" value=\"$lang[submit]\"><center></form>";

			} else {

				$systemnew = 'private';

				if($group['type'] == 'special') {
					if($system_publicnew) {
						if($radminidnew) {
							cpmsg('usergroups_edit_public_invalid');
						} else {
							if($system_dailypricenew > 0) {
								if(!$creditstrans) {
									cpmsg('usergroups_edit_creditstrans_disabled');
								} else {
									$system_minspannew = $system_minspannew <= 0 ? 1 : $system_minspannew;
									$systemnew = intval($system_dailypricenew)."\t".intval($system_minspannew);
								}
							} else {
								$systemnew = "0\t0";
							}
						}
					}
					if(in_array($radminidnew, array(1, 2, 3))) {
						$query = $db->query("SELECT admingid FROM {$tablepre}admingroups WHERE admingid='$group[groupid]'");
						if(!$db->num_rows($query)) {
							if($radminidnew == 1) {
								$db->query("REPLACE INTO {$tablepre}admingroups (admingid, alloweditpost, alloweditpoll, allowstickthread, allowmodpost, allowdelpost, allowmassprune, allowcensorword, allowviewip, allowbanip, allowedituser, allowmoduser, allowbanuser, allowpostannounce, allowviewlog, disablepostctrl)
									VALUES ('$group[groupid]', 1, 1, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)");
								$db->query("REPLACE INTO {$tablepre}adminactions (admingid, disabledactions)
									VALUES ('$group[groupid]', '')");
							} else {
								$db->query("REPLACE INTO {$tablepre}admingroups (admingid)
									VALUES ('$group[groupid]')");
							}
						}
					} else {
						$radminidnew = 0;
						$db->query("DELETE FROM {$tablepre}admingroups WHERE admingid='$group[groupid]'");
					}
				} else {
					$radminidnew = $group['type'] == 'system' && in_array($group['groupid'], array(1, 2, 3)) ? $group['groupid'] : 0;
				}

				if(is_array($raterangenew)) {
					foreach($raterangenew as $id => $rate) {
						if($id >= 1 && $id <= 8 && $rate['allowrate']) {
							$rate['min'] = intval($rate['min'] < -999 ? -999 : $rate['min']);
							$rate['max'] = intval($rate['max'] > 999 ? 999 : $rate['max']);
							$rate['mrpd'] = intval($rate['mrpd'] > 99999 ? 99999 : $rate['mrpd']);
							if(!$rate['mrpd'] || $rate['max'] <= $rate['min'] || $rate['mrpd'] < max(abs($rate['min']), abs($rate['max']))) {
								cpmsg('usergroups_edit_rate_invalid');
							} else {
								$raterangenew[$id] = implode("\t", array($id, $rate['min'], $rate['max'], $rate['mrpd']));
							}							
						} else {
							unset($raterangenew[$id]);
						}
					}
				}
				$raterangenew = $raterangenew ? implode("\n", $raterangenew) : '';
				$maxpricenew = $maxpricenew < 0 ? 0 : intval($maxpricenew);

				$extensionarray = array();
				foreach(explode(',', $attachextensionsnew) as $extension) {
					if($extension = trim($extension)) {
						$extensionarray[] = $extension;
					}
				}
				$attachextensionsnew = implode(', ', $extensionarray);

				$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$grouptitlenew', radminid='$radminidnew', system='$systemnew', allowvisit='$allowvisitnew',
					readaccess='$readaccessnew', allowmultigroups='$allowmultigroupsnew', allowtransfer='$allowtransfernew', allowviewpro='$allowviewpronew',
					allowviewstats='$allowviewstatsnew', allowinvisible='$allowinvisiblenew', allowsearch='$allowsearchnew', allowavatar='$allowavatarnew',
					reasonpm='$reasonpmnew', allowuseblog='$allowuseblognew', allownickname='$allownicknamenew', allowcstatus='$allowcstatusnew',
					disableperiodctrl='$disableperiodctrlnew', maxpmnum='$maxpmnumnew', allowpost='$allowpostnew', allowreply='$allowreplynew',
					allowsetreadperm='$allowsetreadpermnew', maxprice='$maxpricenew', allowhidecode='$allowhidecodenew', allowhtml='$allowhtmlnew',
					allowpostpoll='$allowpostpollnew', allowdirectpost='$allowdirectpostnew', allowvote='$allowvotenew', allowcusbbcode='$allowcusbbcodenew',
					allowsigbbcode='$allowsigbbcodenew', allowsigimgcode='$allowsigimgcodenew', raterange='$raterangenew', maxsigsize='$maxsigsizenew',
					allowgetattach='$allowgetattachnew', allowpostattach='$allowpostattachnew', allowsetattachperm='$allowsetattachpermnew',
					maxattachsize='$maxattachsizenew', maxsizeperday='$maxsizeperdaynew', attachextensions='$attachextensionsnew' WHERE groupid='$edit'");

				if($allowinvisiblenew == 0 && $group['allowinvisible'] != $allowinvisiblenew) {
					$db->query("UPDATE {$tablepre}members SET invisible='0' WHERE groupid='$edit'");
				}

				if($group['type'] == 'special' && $radminidnew != $group['radminid']) {
					$db->query("UPDATE {$tablepre}members SET adminid='".($radminidnew ? $radminidnew : -1)."' WHERE groupid='$edit' AND adminid='$group[radminid]'");
				}

				updatecache('usergroups');
				cpmsg('usergroups_edit_succeed', 'admincp.php?action=usergroups');

			}

		}

	} else {

		if($type == 'member') {

			$orderarray = array();
			if(is_array($groupnew)) {
				foreach($groupnew as $id => $group) {
					if((is_array($delete) && in_array($id, $delete)) || ($id == 0 && (!$group['grouptitle'] || $group['creditshigher'] == ''))) {
						unset($groupnew[$id]);
					} else {
						$orderarray[$group['creditshigher']] = $id;
					}
				}
			}

			if(empty($orderarray[0]) || min(array_flip($orderarray)) >= 0) {
				cpmsg('usergroups_update_credits_invalid');
			}

			ksort($orderarray);
			$rangearray = array();
			$lowerlimit = array_keys($orderarray);
			for($i = 0; $i < count($lowerlimit); $i++) {
				$rangearray[$orderarray[$lowerlimit[$i]]] = array
					(
					'creditshigher' => isset($lowerlimit[$i - 1]) ? $lowerlimit[$i] : -999999999,
					'creditslower' => isset($lowerlimit[$i + 1]) ? $lowerlimit[$i + 1] : 9999999999
					);
			}

			foreach($groupnew as $id => $group) {
				$creditshighernew = $rangearray[$id]['creditshigher'];
				$creditslowernew = $rangearray[$id]['creditslower'];
				if($creditshighernew == $creditslowernew) {
					cpmsg('usergroups_update_credits_duplicate');
				}
				if($id) {
					$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group[grouptitle]', creditshigher='$creditshighernew', creditslower='$creditslowernew', stars='$group[stars]', color='$group[color]', groupavatar='$group[groupavatar]' WHERE groupid='$id' AND type='member'");
				} elseif($group['grouptitle'] && $group['creditshigher'] != '') {
					$db->query("INSERT INTO {$tablepre}usergroups (type, grouptitle, creditshigher, creditslower, stars, color, groupavatar, allowvisit, readaccess, allowpost, allowsigbbcode)
						VALUES ('member', '$group[grouptitle]', '$creditshighernew', '$creditslowernew', '$groupnew[stars]', '$groupnew[color]', '$groupnew[groupavatar]', '1', '1', '1', '1')");
				}
			}

			if(!empty($delete)) {
				$db->query("DELETE FROM {$tablepre}usergroups WHERE groupid IN ('".implode('\',\'', $delete)."') AND type='member'");
			}

		} elseif($type == 'special') {
			if($grouptitlenew) {
				$db->query("INSERT INTO {$tablepre}usergroups (type, grouptitle, stars, color, groupavatar, allowvisit, readaccess, allowpost, allowsigbbcode)
					VALUES ('special', '$grouptitlenew', '$starsnew', '$colornew', '$groupavatarnew', '1', '1', '1', '1')");
			}
			if(is_array($group_title)) {
				$ids = $comma = '';
				foreach($group_title as $id => $title) {
					if($delete[$id]) {
						$ids .= "$comma'$id'";
						$comma = ',';
					} else {
						$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group_title[$id]', stars='$group_stars[$id]', color='$group_color[$id]', groupavatar='$group_avatar[$id]' WHERE groupid='$id'");
					}
				}
			}
			if($ids) {
				$db->query("DELETE FROM {$tablepre}usergroups WHERE groupid IN ($ids) AND type='special'");
				$db->query("DELETE FROM {$tablepre}admingroups WHERE admingid IN ($ids)");
				$db->query("DELETE FROM {$tablepre}adminactions WHERE admingid IN ($ids)");
				$query = $db->query("SELECT groupid FROM {$tablepre}usergroups WHERE type='member' AND creditslower>'0' ORDER BY creditslower LIMIT 1");
				$db->query("UPDATE {$tablepre}members SET groupid='".$db->result($query, 0)."', adminid='0' WHERE groupid IN ($ids)", 'UNBUFFERED');
				//$db->query("UPDATE {$tablepre}members SET groupid='".$db->result($query, 0)."', adminid='0' WHERE groupid IN ($ids) AND adminid='-1'", 'UNBUFFERED');
				//$db->query("UPDATE {$tablepre}members SET groupid=adminid WHERE groupid IN ($ids) AND adminid IN ('1', '2', '3')", 'UNBUFFERED');
			}
		} elseif($type == 'system') {
			if(is_array($group_title)) {
				foreach($group_title as $id => $title) {
					$db->query("UPDATE {$tablepre}usergroups SET grouptitle='$group_title[$id]', stars='$group_stars[$id]', color='$group_color[$id]', groupavatar='$group_avatar[$id]' WHERE groupid='$id'");
				}
			}
		}

		updatecache('usergroups');
		cpmsg('usergroups_update_succeed', 'admincp.php?action=usergroups');
	}

} elseif($action == 'ranks') {

	if(!submitcheck('ranksubmit')) {

		$ranks = '';
		$query = $db->query("SELECT * FROM {$tablepre}ranks ORDER BY postshigher");
		while($rank = $db->fetch_array($query)) {
			$ranks .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[{$rank[rankid]}]\" value=\"$rank[rankid]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"12\" name=\"ranktitlenew[{$rank[rankid]}]\" value=\"$rank[ranktitle]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"6\" name=\"postshighernew[{$rank[rankid]}]\" value=\"$rank[postshigher]\">\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\"name=\"starsnew[{$rank[rankid]}]\" value=\"$rank[stars]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"6\"name=\"colornew[{$rank[rankid]}]\" value=\"$rank[color]\"></td>";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['ranks_tips']?>
</td></tr></table>

<form method="post" action="admincp.php?action=ranks">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header" align="center"><td width="48"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['ranks_title']?></td><td><?=$lang['ranks_postshigher']?></td><td><?=$lang['ranks_stars']?></td><td><?=$lang['ranks_color']?></td></tr>
<?=$ranks?>
<tr><td colspan="5" class="singleborder">&nbsp;</td></tr>
<tr align="center" bgcolor="<?=ALTBG1?>"><td><?=$lang['add_new']?></td>
<td><input type="text" size="12" name="newranktitle"></td>
<td><input type="text" size="6" name="newpostshigher"></td>
<td><input type="text" size="2" name="newstars"></td>
<td><input type="text" size="6" name="newcolor"></td>
</tr></table><br>
<center><input type="submit" name="ranksubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		if($delete) {
			$ids = implode('\',\'', $delete);
			$db->query("DELETE FROM {$tablepre}ranks WHERE rankid IN ('$ids')");
		}

		foreach($ranktitlenew as $id => $value) {
			$db->query("UPDATE {$tablepre}ranks SET ranktitle='$ranktitlenew[$id]', postshigher='$postshighernew[$id]', stars='$starsnew[$id]', color='$colornew[$id]' WHERE rankid='$id'");
		}

		if($newranktitle) {
			$db->query("INSERT INTO {$tablepre}ranks (ranktitle, postshigher, stars, color)
				VALUES ('$newranktitle', '$newpostshigher', '$newstars', '$newcolor')");
		}

		updatecache('ranks');
		cpmsg('ranks_succeed', 'admincp.php?action=ranks');
	}
}

?>