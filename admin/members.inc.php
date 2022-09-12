<?php

/*
	[DISCUZ!] admin/members.inc.php - config of members and usergroups
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-4-5 11:19
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'memberadd') {

	if(!submitcheck('addsubmit')) {

		updatecache('settings');

?>
<br><form method="post" action="admincp.php?action=memberadd">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">

<tr><td class="header" colspan="2"><?=$lang['members_add']?></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_add_uid_range']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="uidlowerlimit" size="5"> - <input type="text" name="uidupperlimit" size="5"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['username']?>:</td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="newusername"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['password']?>:</td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="newpassword"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['email']?>:</td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="newemail"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_add_email_notify']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="checkbox" name="emailnotify" value="yes" checked></td></tr>

</table><br>
<center><input type="submit" name="addsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$newusername = trim($newusername);
		$newpassword = trim($newpassword);
		$newemail = trim($newemail);

		if(!$newusername || !$newpassword || !$newemail) {
			cpmsg('members_add_invalid');
		}

		$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$newusername'");
		if($db->num_rows($query)) {
			cpmsg('members_add_username_duplicate');
		}

		$uid = 0;
		$uidadd1 = $uidadd2 = '';
		if($uidupperlimit != '' && $uidlowerlimit != '') {
			$lastuid = 0;
			$query = $db->query("SELECT * FROM {$tablepre}members WHERE uid BETWEEN '$uidlowerlimit' AND '$uidupperlimit' ORDER BY uid");
			while($member = $db->fetch_array($query)) {
				if($lastuid && $member['uid'] - $lastuid > 1) {
					$uid = $lastuid + 1;
					break;
				}
				$lastuid = $member['uid'];
			}
			if($uid) {
				$uidadd1 = 'uid, ';
				$uidadd2 = $uid.', ';
			} else {
				cpmsg('members_add_uid_invalid');
			}
		}

		$query = $db->query("SELECT groupid FROM {$tablepre}usergroups WHERE type='member' AND creditshigher='0'");
		$newgroupid = $db->result($query, 0);

		$db->query("INSERT INTO {$tablepre}members ($uidadd1 username, password, secques, gender, adminid, groupid, regip, regdate, lastvisit, lastactivity, posts, credits, email, bday, sigstatus, tpp, ppp, styleid, dateformat, timeformat, showemail, newsletter, invisible, timeoffset)
			VALUES ($uidadd2 '$newusername', '".md5($newpassword)."', '', '0', '0', '$newgroupid', 'Manual Acting', '$timestamp', '$timestamp', '$timestamp', '0', '0', '$newemail', '0000-00-00', '0', '0', '0', '0', '{$_DCACHE[settings][dateformat]}', '{$_DCACHE[settings][timeformat]}', '1', '1', '0', '{$_DCACHE[settings][timeoffset]}')");
		$uid = $db->insert_id();

		$db->query("REPLACE INTO {$tablepre}memberfields (uid) VALUES ('$uid')");

		if($emailnotify == 'yes') {
			sendmail($newemail, 'add_member_subject', 'add_member_message');
		}

		updatecache('settings');
		cpmsg('members_add_succeed');
	}

} elseif($action == 'members') {

	if(!submitcheck('searchsubmit', 1) && !submitcheck('deletesubmit') && !submitcheck('editsubmit')) {

		$adminselect = $groupselect = $extgroupselect = '';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE groupid NOT IN ('6', '7') ORDER BY (creditshigher<>'0' || creditslower<>'0'), creditslower");
		while($group = $db->fetch_array($query)) {
			$select = "<option value=\"$group[groupid]\">$group[grouptitle]</option>\n";
			$group['groupid'] <= 3 ? $adminselect .= $select : $groupselect .= $select;
			$extgroupselect .= $select;
		}

		$searchcredits = '';
		foreach($extcredits as $id => $credit) {
			$searchcredits .= "<tr><td class=\"altbg1\">$credit[title] $lang[members_search_lower]:</td>\n".
				"<td align=\"right\" class=\"altbg2\"><input type=\"text\" name=\"lower[extcredits$id]\" size=\"40\"></td></tr>\n".
				"<tr><td class=\"altbg1\">$credit[title] $lang[members_search_higher]:</td>\n".
				"<td align=\"right\" class=\"altbg2\"><input type=\"text\" name=\"higher[extcredits$id]\" size=\"40\"></td></tr>\n";
		}

?>
<br><form method="post" action="admincp.php?action=members">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">

<tr><td class="header" colspan="2"><?=$lang['members_search']?></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['admingroup']?>:</td>
<td align="right" bgcolor="<?=ALTBG2?>">
<select name="admingroupid">
<option value=""><?=$lang['members_search_all_groups']?></option>
<?=$adminselect?>
</select></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_group']?></td>
<td align="right" bgcolor="<?=ALTBG2?>">
<select name="usergroupid">
<option value=""><?=$lang['members_search_all_groups']?></option>
<?=$groupselect?>
</select></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_extgroup']?></td>
<td align="right" bgcolor="<?=ALTBG2?>">
<select name="extusergroupid">
<option value=""><?=$lang['members_search_all_groups']?></option>
<?=$extgroupselect?>
</select></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_user']?></td>
<td align="right" bgcolor="<?=ALTBG2?>">
<?=$lang['case_insensitive']?> <input type="checkbox" name="cins" value="1">
<br><input type="text" name="username" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_email']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="srchemail" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_regdatebefore']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="regdatebefore" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_regdateafter']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="regdateafter" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['credits']?> <?=$lang['members_search_lower']?>:</td>
<td align="right" class="altbg2"><input type="text" name="lower[credits]" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['credits']?> <?=$lang['members_search_higher']?>:</td>
<td align="right" class="altbg2"><input type="text" name="higher[credits]" size="40"></td></tr>

<?=$searchcredits?>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_postslower']?>:</td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="postslower" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_postshigher']?>:</td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="postshigher" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_awaydays']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="awaydays" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_regip']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="regip" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['members_search_lastip']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="lastip" size="40"></td></tr>

</table><br><center>
<input type="submit" name="searchsubmit" value="<?=$lang['members_search']?>"> &nbsp; 
<input type="submit" name="deletesubmit" value="<?=$lang['members_delete']?>"></center></form>
<?

	} elseif(submitcheck('searchsubmit', 1) || submitcheck('deletesubmit')) {

		$memberperpage = 100;

		$page = !ispage($page) ? 1 : $page;
		$start_limit = ($page - 1) * $memberperpage;

		$conditions = '1';
		$conditions .= $username != '' ? " AND ".($cins ? '' : 'BINARY')." username LIKE '".str_replace('*', '%', $username)."'" : '';
		$conditions .= $srchemail != '' ? " AND email LIKE '".str_replace('*', '%', $srchemail)."'" : '';
		$conditions .= $admingroupid != '' ? " AND adminid='$admingroupid'" : '';
		$conditions .= $usergroupid != '' ? " AND groupid='$usergroupid'" : '';
		$conditions .= $extusergroupid != '' ? " AND extgroupids REGEXP '(^|\t)$extusergroupid(\t|\$)'" : '';

		$conditions .= $regdatebefore != '' ? " AND regdate<'".strtotime($regdatebefore)."'" : '';
		$conditions .= $regdateafter != '' ? " AND regdate>'".strtotime($regdateafter)."'" : '';
   
		if(is_array($higher)) {
			foreach($higher as $credit => $value) {
				if($value != '') {
					$conditions .= " AND $credit>'$value'";
				}
			}
		}
		if(is_array($higher)) {
			foreach($lower as $credit => $value) {
				if($value != '') {
					$conditions .= " AND $credit<'$value'";
				}
			}
		}

		$conditions .= $postshigher != '' ? " AND posts>'$postshigher'" : '';
		$conditions .= $postslower != '' ? " AND posts<'$postslower'" : '';

		$conditions .= $awaydays != '' ? " AND lastactivity<'".($timestamp - $awaydays * 86400)."'" : '';
		$conditions .= $regip != '' ? " AND regip LIKE '$regip%'" : '';
		$conditions .= $lastip != '' ? " AND lastip LIKE '$lastip%'" : '';

		if(!$conditions && !submitcheck('deletesubmit')) {
			cpmsg('members_search_invalid');
		}

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}members WHERE $conditions");
		$membernum = $db->result($query, 0);

		if(submitcheck('searchsubmit', 1)) {

			$urladd = '';
			foreach(array('lower', 'higher') as $key) {
				if(is_array($$key)) {
					foreach($$key as $column => $value) {
						$urladd .= '&'.$key.'['.$column.']='.rawurlencode($value);
					}
				}
			}

			$multipage = multi($membernum, $memberperpage, $page, "admincp.php?action=members$urladd&searchsubmit=yes&cins=$cins&username=$username&srchemail=$srchemail&admingroupid=$admingroupid&usergroupid=$usergroupid&extusergroupid=$extusergroupid&regdatebefore=$regdatebefore&regdateafter=$regdateafter&creditshigher=$creditshigher&creditslower=$creditslower&postshigher=$postshigher&postslower=$postslower&awaydays=$awaydays&regip=$regip&lastip=$lastip");

			$usergroups = array();
			$query = $db->query("SELECT groupid, type, grouptitle FROM {$tablepre}usergroups");
			while($group = $db->fetch_array($query)) {
				switch($group['type']) {
					case 'system': $group['grouptitle'] = '<b>'.$group['grouptitle'].'</b>'; break;
					case 'special': $group['grouptitle'] = '<i>'.$group['grouptitle'].'</i>'; break;
				}
				$usergroups[$group['groupid']] = $group;
			}

			$altbg1 = 'altbg'.((count($extcredits) + 3) % 2 ? '2' : '1');
			$altbg2 = $altbg1 == 'altbg1' ? 'altbg2' : 'altbg1';

			$creditscolumns = '';
			foreach($extcredits as $id => $credit) {
				$creditscolumns .= "<td>$credit[title]</td>";
			}

			$query = $db->query("SELECT uid, username, adminid, groupid, credits, extcredits1, extcredits2,
				extcredits3, extcredits4, extcredits5, extcredits6, extcredits7, extcredits8, posts
				FROM {$tablepre}members WHERE $conditions LIMIT $start_limit, $memberperpage");

			while($member = $db->fetch_array($query)) {
				$members .= "<tr align=\"center\" class=\"smalltxt\"><td class=\"altbg1\"><input type=\"checkbox\" name=\"delete[]\" value=\"$member[uid]\"></td>\n".
					"<td class=\"altbg2\"><a href=\"viewpro.php?uid=$member[uid]\" target=\"_blank\">$member[username]</a></td>\n".
					"<td class=\"altbg1\">$member[credits]</td>\n";
				$thisbg = '';
				foreach($extcredits as $id => $credit) {
					$thisbg = isset($thisbg) && $thisbg == 'altbg2' ? 'altbg1' : 'altbg2';
					$members .= "<td class=\"$thisbg\">".$member['extcredits'.$id]."</td>\n";
				}
				$members .=
"<td class=\"$altbg1\">$member[posts]</td>\n".
					"<td class=\"$altbg2\">{$usergroups[$member[adminid]][grouptitle]}</td>\n".
					"<td class=\"$altbg1\">{$usergroups[$member[groupid]][grouptitle]}</td>\n".
					"<td class=\"$altbg2\"><a href=\"admincp.php?action=editgroups&uid=$member[uid]\">[$lang[usergroup]]</a> ".
					"<a href=\"admincp.php?action=access&uid=$member[uid]\">[$lang[access]]</a> ".
					($extcredits ? "<a href=\"admincp.php?action=editcredits&uid=$member[uid]\">[$lang[credits]]</a> " : "<span disabled>[$lang[edit]]</span> ").
					"<a href=\"admincp.php?action=editmedals&uid=$member[uid]\">[$lang[medals]]</a> ".
					"<a href=\"admincp.php?action=memberprofile&uid=$member[uid]\">[$lang[detail]]</a></td></tr>\n";
			}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['members_tips']?>
</td></tr></table>

<form method="post" action="admincp.php?action=members">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="95%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr align="center" class="header">
<td width="48"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form, 'delete')"><?=$lang['del']?></td>
<td><?=$lang['username']?></td><td><?=$lang['credits']?></td><?=$creditscolumns?><td><?=$lang['posts']?></td><td><?=$lang['admingroup']?><td><?=$lang['usergroup']?></td><td><?=$lang['edit']?></td></tr>
<?=$members?>
</table>

<table cellspacing="0" cellpadding="0" border="0" width="95%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table><br><center>
<input type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

		} elseif(submitcheck('deletesubmit')) {
			if(!$confirmed) {
				$extra = '';
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE $conditions");
				while($member = $db->fetch_array($query)) {
					$extra .= '<input type="hidden" name="uidarray[]" value="'.$member['uid'].'">';
				}
				cpmsg('members_delete_confirm', "admincp.php?action=members&deletesubmit=yes", 'form', $extra);
			} else {
				$uids = is_array($uidarray) ? '\''.implode('\', \'', $uidarray).'\'' : '0';
				$query = $db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
				$numdeleted = $db->affected_rows();

				$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}buddys WHERE uid IN ($uids) OR buddyid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}favorites WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}pms WHERE msgfromid IN ($uids) OR msgtoid IN ($uids)", 'UNBUFFERED');
				$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($uids)", 'UNBUFFERED');
        
				updatecache('settings');
				cpmsg('members_delete_succeed');
			}
		}

	} elseif(submitcheck('editsubmit')) {
		if(is_array($delete)) {
			$uids = '\''.implode('\', \'', $delete).'\'';
			$db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
			$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($uids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}buddys WHERE uid IN ($uids) OR buddyid IN ($uids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}favorites WHERE uid IN ($uids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($uids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}pms WHERE msgfromid IN ($uids) OR msgtoid IN ($uids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($uids)", 'UNBUFFERED');

			updatecache('settings');
		}

		cpmsg('members_edit_succeed');
	}

} elseif($action == 'membersmerge') {

	if(!submitcheck('mergesubmit')) {

?>
<br><br><br><br><br>
<form method="post" action="admincp.php?action=membersmerge">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['members_merge']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="40%"><?=$lang['members_merge_source']?> 1:</td>
<td bgcolor="<?=ALTBG2?>" width="60%"><input type="text" name="source[]" size="20"></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="40%"><?=$lang['members_merge_source']?> 2:</td>
<td bgcolor="<?=ALTBG2?>" width="60%"><input type="text" name="source[]" size="20"></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="40%"><?=$lang['members_merge_source']?> 3:</td>
<td bgcolor="<?=ALTBG2?>" width="60%"><input type="text" name="source[]" size="20"></td></tr>
<tr><td colspan="2" class="singleborder">&nbsp;</td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="40%"><?=$lang['members_merge_target']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="60%"><input type="text" name="target" size="20"></td></tr>
</table><br><center><input type="submit" name="mergesubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		$suids = $susernames = $comma = '';
		$tuid = $tusername = '';
		$sourcemember = $targetmember = '';

		if(is_array($source)) {
			$query = $db->query("SELECT uid, username FROM {$tablepre}members WHERE username IN ('".implode('\',\'', $source)."') AND username<>''");
			while($member = $db->fetch_array($query)) {
				$suids .= $comma.$member['uid'];
				$susernames .= $comma.'\''.addslashes($member['username']).'\'';
				$sourcemember .= $comma.$member['username'];
				$comma = ', ';
			}
		}

		$query = $db->query("SELECT uid, username FROM {$tablepre}members WHERE username='$target'");
		if(!($member = $db->fetch_array($query)) || !$suids) {
			cpmsg('members_merge_invalid');
		}
		$tuid = $member['uid'];
		$tusername = addslashes($member['username']);
		$targetmember = $member['username'];

		if(!$confirmed) {

			$extra = '<input type="hidden" name="target" value="'.dhtmlspecialchars($target).'">';
			foreach($source as $username) {
				$extra .= '<input type="hidden" name="source[]" value="'.dhtmlspecialchars($username).'">';
			}

			cpmsg('members_merge_confirm', "admincp.php?action=membersmerge&mergesubmit=yes", 'form', $extra);

		} else {

			$db->query("DELETE FROM {$tablepre}access WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}adminnotes SET admin='$tusername' WHERE admin IN ($susernames)");
			$db->query("UPDATE {$tablepre}adminsessions SET uid='$tuid' WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}announcements SET author='$tusername' WHERE author IN ($susernames)");
			$db->query("UPDATE {$tablepre}banned SET admin='$tusername' WHERE admin IN ($susernames)");
			$db->query("DELETE FROM {$tablepre}blogcaches WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}buddys WHERE uid IN ($suids) OR buddyid IN ($suids)");
			$db->query("UPDATE {$tablepre}favorites SET uid='$tuid' WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}pms SET msgfromid='$tuid', msgfrom='$tusername' WHERE msgfromid IN ($suids)");
			$db->query("UPDATE {$tablepre}pms SET msgtoid='$tuid' WHERE msgtoid IN ($suids)");
			$db->query("UPDATE {$tablepre}posts SET author='$tusername', authorid='$tuid' WHERE authorid IN ($suids)");
			$db->query("UPDATE {$tablepre}ratelog SET uid='$tuid', username='$tusername' WHERE uid IN ($suids)");
			$db->query("DELETE FROM {$tablepre}subscriptions WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}threads SET author='$tusername', authorid='$tuid' WHERE authorid IN ($suids)");
			$db->query("UPDATE {$tablepre}threads SET lastposter='$tusername' WHERE lastposter IN ($susernames)");
			$db->query("UPDATE {$tablepre}threadsmod SET uid='$tuid', username='$tusername' WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}validating SET uid='$tuid' WHERE uid IN ($suids)");
			$db->query("UPDATE {$tablepre}validating SET admin='$tusername' WHERE admin IN ($susernames)");
			$db->query("DELETE FROM {$tablepre}onlinetime WHERE uid IN ($suids)");
			
			$query = $db->query("SELECT SUM(credits) AS credits, SUM(extcredits1) AS extcredits1,
				SUM(extcredits2) AS extcredits2, SUM(extcredits3) AS extcredits3,
				SUM(extcredits4) AS extcredits4, SUM(extcredits5) AS extcredits5,
				SUM(extcredits6) AS extcredits6, SUM(extcredits7) AS extcredits7,
				SUM(extcredits8) AS extcredits8, SUM(posts) AS posts,
				SUM(digestposts) AS digestposts, SUM(pageviews) AS pageviews,
				SUM(oltime) AS oltime
				FROM {$tablepre}members WHERE uid IN ($suids)");

			$member = $db->fetch_array($query);
			$db->query("UPDATE {$tablepre}members SET credits=credits+$member[credits],
				extcredits1=extcredits1+$member[extcredits1], extcredits2=extcredits2+$member[extcredits2],
				extcredits3=extcredits3+$member[extcredits3], extcredits4=extcredits4+$member[extcredits4],
				extcredits5=extcredits5+$member[extcredits5], extcredits6=extcredits6+$member[extcredits6],
				extcredits7=extcredits7+$member[extcredits7], extcredits8=extcredits8+$member[extcredits8],
				posts=posts+$member[posts], digestposts=digestposts+$member[digestposts],
				pageviews=pageviews+$member[pageviews], oltime=oltime+$member[oltime]
				WHERE uid='$tuid'");
			$db->query("DELETE FROM {$tablepre}members WHERE uid IN ($suids)");

			updatecache('settings');

			cpmsg('members_merge_succeed');

		}

	}

} elseif($action == 'editgroups') {

	$query = $db->query("SELECT m.uid, m.username, m.adminid, m.groupid, m.groupexpiry, m.extgroupids, m.credits,
		mf.groupterms, u.type AS grouptype, u.grouptitle
		FROM {$tablepre}members m
		INNER JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
		WHERE m.uid='$uid'");

	if(!$member = $db->fetch_array($query)) {
		cpmsg('members_edit_nonexistence');
	}

	if(!submitcheck('editsubmit')) {

		$checkadminid = array(($member['adminid'] >= 0 ? $member['adminid'] : 0) => 'checked');

		$member['groupterms'] = unserialize($member['groupterms']);

		if($member['groupterms']['main']) {
			$expirydate = gmdate('Y-n-j', $member['groupterms']['main']['time'] + $timeoffset * 3600);
			$expirydays = ceil(($member['groupterms']['main']['time'] - $timestamp) / 86400);
			$selecteaid = array($member['groupterms']['main']['adminid'] => 'selected');
			$selectegid = array($member['groupterms']['main']['groupid'] => 'selected');
		} else {
			$expirydate = $expirydays = '';
			$selecteaid = array($member['adminid'] => 'selected');
			$selectegid = array(($member['grouptype'] == 'member' ? 0 : $member['groupid']) => 'selected');
		}

		$class = 'altbg1';
		$extgroupcount = 0;
		$extgroups = $expgroups = $curtype = $thisbg = '';
		$extgrouparray = explode("\t", $member['extgroupids']);
		$groups = array('system' => '', 'special' => '', 'member' => '');
		$group = array('groupid' => 0, 'radminid' => 0, 'type' => '', 'grouptitle' => $lang['usergroups_system_0'], 'creditshigher' => 0, 'creditslower' => '0');
		$query = $db->query("SELECT groupid, radminid, type, grouptitle, creditshigher, creditslower
			FROM {$tablepre}usergroups WHERE groupid NOT IN ('6', '7') ORDER BY creditshigher, groupid");
		do {
			if($group['groupid'] && !in_array($group['groupid'], array(4, 5, 6, 7, 8)) && ($group['type'] == 'system' || $group['type'] == 'special')) {
				$extgroups .= ($extgroupcount++ % 2 == 0 ? '</tr><tr>' : '').
					'<td class="altbg2"><input type="checkbox" name="extgroupidsnew[]" value="'.$group['groupid'].'" '.(in_array($group['groupid'], $extgrouparray) ? 'checked' : '').'> '.$group['grouptitle'].'</td><td align="center" class="altbg2"><input type="text" size="8" name="extgroupexpirynew['.$group['groupid'].']" value="'.(in_array($group['groupid'], $extgrouparray) && !empty($member['groupterms']['ext'][$group['groupid']]) ? gmdate('Y-n-j', $member['groupterms']['ext'][$group['groupid']] + $timeoffset * 3600) : '').'"></td>';
			}
			if($group['groupid'] && $group['type'] == 'member' && !($member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) && $member['groupid'] != $group['groupid']) {
				continue;
			}

			$expgroups .= '<option name="expgroupidnew" value="'.$group['groupid'].'" '.$selectegid[$group['groupid']].'>'.$group['grouptitle'].'</option>';

			if($group['groupid'] != 0) {
				$thisbg = $curtype == $group['type'] && $thisbg == ALTBG2 ? ALTBG1 : ALTBG2;
				$curtype = $group['type'];
				$groups[$group['type']] .= '<tr><td bgcolor="'.$thisbg.'"><input type="radio" name="groupidnew" value="'.$group['groupid'].'" '.($member['groupid'] == $group['groupid'] ? 'checked' : '').'> '.$group['grouptitle'].'</td></tr>';
			}
		} while($group = $db->fetch_array($query));

		$extgroups .= $extgroupcount++ % 2 != 0 ? '<td colspan="2" class="altbg2">&nbsp;</tr><tr>' : '';

		if(!$groups['member']) {
			$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE type='member' AND creditshigher>='0' ORDER BY creditshigher LIMIT 1");
			$group = $db->fetch_array($query);
			$groups['member'] = '<tr><td bgcolor="'.ALTBG1.'"><input type="radio" name="groupidnew" value="'.$group['groupid'].'"> '.$group['grouptitle'].'</td></tr>';
		}

?>
<form method="post" action="admincp.php?action=editgroups&uid=<?=$member['uid']?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['members_edit_groups']?> - <?=$member['username']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><table cellspacing="0" cellpadding="10" width="100%" align="center">
<tr><td width="22%" valign="top">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%" align="center" class="tableborder">
<tr><td class="header"><?=$lang['usergroup']?></td></tr>
<tr><td class="singleborder">&nbsp;</td></tr>
<tr><td class="category"><span class="bold"><?=$lang['usergroups_system']?></span></td></tr>
<?=$groups['system']?>
<tr><td class="singleborder">&nbsp;</td></tr>
<tr><td class="category"><span class="bold"><?=$lang['usergroups_special']?></span></td></tr>
<?=$groups['special']?>
<tr><td class="singleborder">&nbsp;</td></tr>
<tr><td class="category"><span class="bold"><?=$lang['usergroups_member']?></span></td></tr>
<?=$groups['member']?>
</table></td><td width="78%" align="right" valign="top">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%" align="center" class="tableborder">
<tr><td class="header"><?=$lang['members_edit_groups_related_adminid']?></td></tr>
<tr><td bgcolor="<?=ALTBG2?>"><input type="radio" name="adminidnew" value="0" <?=$checkadminid[0]?>> <?=$lang['usergroups_system_0']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>"><input type="radio" name="adminidnew" value="1" <?=$checkadminid[1]?>> <?=$lang['usergroups_system_1']?></td></tr>
<tr><td bgcolor="<?=ALTBG2?>"><input type="radio" name="adminidnew" value="2" <?=$checkadminid[2]?>> <?=$lang['usergroups_system_2']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>"><input type="radio" name="adminidnew" value="3" <?=$checkadminid[3]?>> <?=$lang['usergroups_system_3']?></td></tr>
</table><br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%" align="center" class="tableborder">
<tr><td colspan="4" class="header"><?=$lang['members_edit_groups_extended']?></td></tr>
<tr align="center" class="category"><td width="30%"><?=$lang['usergroup']?></td><td width="20%"><?=$lang['validity']?></td><td width="30%"><?=$lang['usergroup']?></td><td width="20%"><?=$lang['validity']?></td></tr>
<?=$extgroups?>
<tr><td colspan="4" class="altbg2"><?=$lang['members_edit_groups_extended_comment']?></td></tr>
</table><br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%" align="center" class="tableborder">
<tr><td colspan="2" class="header"><?=$lang['validity']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>" width="60%"><b><?=$lang['members_edit_groups_validity']?></b><br><span class="smalltxt"><?=$lang['members_edit_groups_validity_comment']?></span></td>
<td bgcolor="<?=ALTBG2?>" width="40%">
<input type="radio" name="expirytype" value="date" checked> <input type="text" name="expirydatenew" value="<?=$expirydate?>" size="15"> <?=$lang['members_edit_groups_validity_date']?><br>
<input type="radio" name="expirytype" value="days"> <input type="text" name="expirydaysnew" value="<?=$expirydays?>" size="15"> <?=$lang['members_edit_groups_validity_days']?><br></td></tr>
<tr><td bgcolor="<?=ALTBG1?>" width="60%"><b><?=$lang['members_edit_groups_orig_groupid']?></b></td><td bgcolor="<?=ALTBG2?>" width="40%">
<select name="expadminidnew">
<option value="0" <?=$selecteaid[0]?>><?=$lang['usergroups_system_0']?></option>
<option value="1" <?=$selecteaid[1]?>><?=$lang['usergroups_system_1']?></option>
<option value="2" <?=$selecteaid[2]?>><?=$lang['usergroups_system_2']?></option>
<option value="3" <?=$selecteaid[3]?>><?=$lang['usergroups_system_3']?></option>
</select></td></tr>
<tr><td bgcolor="<?=ALTBG1?>" width="60%"><b><?=$lang['members_edit_groups_orig_adminid']?></b></td><td bgcolor="<?=ALTBG2?>" width="40%">
<select name="expgroupidnew"><?=$expgroups?></select></td></tr>
</table><br>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%" align="center" class="tableborder">
<tr><td colspan="2" class="header"><?=$lang['members_edit_reason']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>" width="60%"><b><?=$lang['members_edit_groups_ban_reason']?></b><br><span class="smalltxt"><?=$lang['members_edit_groups_ban_reason_comment']?></span></td>
<td bgcolor="<?=ALTBG2?>" width="40%"><textarea name="reason" rows="5" cols="30"></textarea></td></tr>
</table><br><br><center><input type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</td></tr></table>
</td></tr>
</table>
<?

	} else {

		$query = $db->query("SELECT groupid, radminid, type FROM {$tablepre}usergroups WHERE groupid='$groupidnew'");
		if(!$group = $db->fetch_array($query)) {
			cpmsg('undefined_action');
		}

		if(strlen(is_array($extgroupidsnew) ? implode("\t", $extgroupidsnew) : '') > 60) {
			cpmsg('members_edit_groups_toomany');
		}

		switch($group['type']) {
			case 'member':
				$groupidnew = in_array($adminidnew, array(1, 2, 3)) ? $adminidnew : $groupidnew;
				break;
			case 'special':
				if($group['radminid']) {
					$adminidnew = $group['radminid'];
				} elseif(!in_array($adminidnew, array(1, 2, 3))) {
					$adminidnew = -1;
				}
				break;
			case 'system':
				$adminidnew = in_array($groupidnew, array(1, 2, 3)) ? $groupidnew : -1;
				break;
		}

		$groupterms = array();
		if($expirytype == 'date' && $expirydatenew) {
			$maingroupexpirynew = strtotime($expirydatenew) - date('Z') + $timeoffset * 3600;
		} elseif($expirytype == 'days' && $expirydaysnew) {
			$maingroupexpirynew = $timestamp + $expirydaysnew * 86400;
		} else {
			$maingroupexpirynew = 0;
		}

		if($maingroupexpirynew) {

			$query = $db->query("SELECT groupid, radminid, type FROM {$tablepre}usergroups WHERE groupid='$expgroupidnew'");
			if(!$group = $db->fetch_array($query)) {
				$expgroupidnew = in_array($expadminidnew, array(1, 2, 3)) ? $expadminidnew : $expgroupidnew;
			} else {
				switch($group['type']) {
					case 'special':
						if($group['radminid']) {
							$expadminidnew = $group['radminid'];
						} elseif(!in_array($expadminidnew, array(1, 2, 3))) {
							$expadminidnew = -1;
						}
						break;
					case 'system':
						$expadminidnew = in_array($expgroupidnew, array(1, 2, 3)) ? $expgroupidnew : -1;
						break;
				}
			}

			if($expgroupidnew == $groupidnew) {
				cpmsg('members_edit_groups_illegal');
			} elseif($maingroupexpirynew > $timestamp) {
				if($expgroupidnew || $expadminidnew) {
					$groupterms['main'] = array('time' => $maingroupexpirynew, 'adminid' => $expadminidnew, 'groupid' => $expgroupidnew);
				} else {
					$groupterms['main'] = array('time' => $maingroupexpirynew);
				}
				$groupterms['ext'][$groupidnew] = $maingroupexpirynew;
			}

		}

		if(is_array($extgroupexpirynew)) {
			foreach($extgroupexpirynew as $extgroupid => $expiry) {
				if(is_array($extgroupidsnew) && in_array($extgroupid, $extgroupidsnew) && !isset($groupterms['ext'][$extgroupid]) && $expiry && ($expiry = strtotime($expiry) - date('Z') + $timeoffset * 3600) > $timestamp) {
					$groupterms['ext'][$extgroupid] = $expiry;
				}
			}
		}

		$grouptermsnew = addslashes(serialize($groupterms));
		$groupexpirynew = groupexpiry($groupterms);
		$extgroupidsnew = $extgroupidsnew && is_array($extgroupidsnew) ? implode("\t", $extgroupidsnew) : '';

		$db->query("UPDATE {$tablepre}members SET groupid='$groupidnew', adminid='$adminidnew', extgroupids='$extgroupidsnew', groupexpiry='$groupexpirynew' WHERE uid='$member[uid]'");
		$db->query("UPDATE {$tablepre}memberfields SET groupterms='$grouptermsnew' WHERE uid='$member[uid]'");

		if($groupidnew != $member['groupid'] && (in_array($groupidnew, array(4, 5)) || in_array($member['groupid'], array(4, 5)))) {
			banlog($member['username'], $member['groupid'], $groupidnew, $groupexpirynew, $reason);
		}

		cpmsg('members_edit_groups_succeed', "admincp.php?action=editgroups&uid=$member[uid]");

	}

} elseif($action == 'editcredits' && $uid && $extcredits) {

	$query = $db->query("SELECT username, extcredits1, extcredits2, extcredits3, extcredits4, extcredits5, extcredits6, extcredits7, extcredits8 FROM {$tablepre}members WHERE uid='$uid'");
	if(!$member = $db->fetch_array($query)) {
		cpmsg('members_edit_nonexistence');
	}

	if(!submitcheck('creditsubmit')) {

		$count = 0;
		$width = round(100 / count($extcredits)).'%';
		$creditscols = $creditsvalue = '';
		foreach($extcredits as $id => $extcredit) {
			$creditscols .= "<td width=\"$width\">$extcredit[title]</td>";
			$creditsvalue .= '<td class="altbg'.($count++ %2 + 1).'"><input type="text" size="3" name="extcreditsnew['.$id.']" value="'.$member['extcredits'.$id].'"> '.$extcredit['unit'].'</td>';
		}

?>
<form method="post" action="admincp.php?action=editcredits&uid=<?=$uid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="70%" align="center" class="tableborder">
<tr class="header"><td colspan="8"><?=$lang['members_edit_credits']?> - <?=$member['username']?></td></tr>
<tr class="category" align="center"><?=$creditscols?></tr>
<tr align="center"><?=$creditsvalue?></tr>
</table><br>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="70%" align="center" class="tableborder">
<tr><td colspan="2" class="header"><?=$lang['members_edit_reason']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>" width="60%"><b><?=$lang['members_edit_credits_reason']?></b><br><span class="smalltxt"><?=$lang['members_edit_credits_reason_comment']?></span></td>
<td bgcolor="<?=ALTBG2?>" width="40%"><textarea name="reason" rows="5" cols="30"></textarea></td></tr>
</table><br><center>
<input type="submit" name="creditsubmit" value="<?=$lang['submit']?>">
</center></form>
<?

	} else {

		$diffarray = array();
		$sql = $comma = '';
		if(is_array($extcreditsnew)) {
			foreach($extcreditsnew as $id => $value) {
				if($member['extcredits'.$id] != ($value = intval($value))) {
					$diffarray[$id] = $value - $member['extcredits'.$id];
					$sql .= $comma."extcredits$id='$value'";
					$comma = ', ';
				}
			}
		}

		if($diffarray) {
			if(empty($reason)) {
				cpmsg('members_edit_reason_invalid');
			}

			@$fp = fopen(DISCUZ_ROOT.'./forumdata/ratelog.php', 'a');
			@flock($fp, 2);
			foreach($diffarray as $id => $diff) {
				@fwrite($fp, "$timestamp\t".dhtmlspecialchars($discuz_userss)."\t$adminid\t".dhtmlspecialchars($member['username'])."\t$id\t$diff\t0\t\t$reason\n");
			}
			$db->query("UPDATE {$tablepre}members SET $sql WHERE uid='$uid'");
			@fclose($fp);
		}

		cpmsg('members_edit_credits_succeed', "admincp.php?action=editcredits&uid=$uid");

	}

} elseif($action == 'editmedals' && $uid) {

	$query = $db->query("SELECT m.uid, m.username, mf.medals
		FROM {$tablepre}memberfields mf, {$tablepre}members m
		WHERE mf.uid='$uid' AND m.uid=mf.uid");

	if(!$member = $db->fetch_array($query)) {
		cpmsg('members_edit_nonexistence');
	}

	if(!submitcheck('medalsubmit')) {

		$medals = '';
		$membermedals = explode("\t", $member['medals']);
		$query = $db->query("SELECT * FROM {$tablepre}medals WHERE available='1'");
		while($medal = $db->fetch_array($query)) {
			$medals .= "<tr align=\"center\">\n".
				"<td bgcolor=\"".ALTBG1."\"><img src=\"images/common/$medal[image]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\">$medal[name]</td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"medals[$medal[medalid]]\" value=\"1\" ".(in_array($medal['medalid'], $membermedals) ? 'checked' : '')."></td></td>\n";
		}

		if(!$medals) {
			cpmsg('members_edit_medals_nonexistence');
		}

?>
<form method="post" action="admincp.php?action=editmedals&uid=<?=$uid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="70%" align="center" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['medals_edit']?> - <?=$member['username']?></td></tr>
<tr class="category" align="center"><td><?=$lang['medals_image']?></td><td><?=$lang['name']?></td><td><?=$lang['medals_grant']?></td></tr>
<?=$medals?>
</table><br>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="70%" align="center" class="tableborder">
<tr><td colspan="2" class="header"><?=$lang['members_edit_reason']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>" width="60%"><b><?=$lang['members_edit_medals_reason']?></b><br><span class="smalltxt"><?=$lang['members_edit_medals_reason_comment']?></span></td>
<td bgcolor="<?=ALTBG2?>" width="40%"><textarea name="reason" rows="5" cols="30"></textarea></td></tr>
</table><br><center>
<input type="submit" name="medalsubmit" value="<?=$lang['submit']?>">
</center></form>
<?

	} else {

		$medalsarray = array();
		if(is_array($medals)) {
			foreach($medals as $medalid => $newgranted) {
				if($newgranted) {
					$medalsarray[] = intval($medalid);
				}
			}
		}

		$medalsnew = implode("\t", $medalsarray);
		$reason = preg_replace("/(\r\n|\r|\n)/", '<br />', dhtmlspecialchars(trim($reason)));

		if($member['medals'] != $medalsnew) {
			if(empty($reason)) {
				cpmsg('members_edit_reason_invalid');
			} else {
				$db->query("UPDATE {$tablepre}memberfields SET medals='$medalsnew' WHERE uid='$uid'");
			}
		}

		$origmedalsarray = explode("\t", $member['medals']);

		@$fp = fopen(DISCUZ_ROOT.'./forumdata/medalslog.php', 'a');
		@flock($fp, 2);

		foreach(array_unique(array_merge($origmedalsarray, $medalsarray)) as $medalid) {
			if($medalid) {
				$orig = in_array($medalid, $origmedalsarray);
				$new = in_array($medalid, $medalsarray);
				if($orig != $new) {
					if($orig && !$new) {
						$medalaction = 'revoke';
					} elseif(!$orig && $new) {
						$medalaction = 'grant';
					}
					@fwrite($fp, "$timestamp\t".dhtmlspecialchars($discuz_userss)."\t$onlineip\t".dhtmlspecialchars($member['username'])."\t$medalid\t$medalaction\t$reason\n");
				}
			}
		}

		@fclose($fp);

		cpmsg('members_edit_medals_succeed', "admincp.php?action=editmedals&uid=$uid");

	}

} elseif($action == 'editmember') {

	if(empty($uid) && empty($username)) {

?>
<br><br><form method="post" action="admincp.php?action=editmember">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="70%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['members_edit']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td><?=$lang['username']?>:</td><td><input type="text" name="username"></td></tr>
</table><br><center>
<input type="submit" name="membersubmit" value="<?=$lang['submit']?>">
</center></form><br>
<?

	} else {

		$query = $db->query("SELECT m.*, mf.*, u.type AS grouptype, u.allowsigbbcode, u.allowsigimgcode FROM {$tablepre}members m
			LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
			LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
			WHERE ".($uid ? "m.uid='$uid'" : "m.username='$username'"));

		if(!$member = $db->fetch_array($query)) {
			cpmsg('members_edit_nonexistence');
		} elseif(($member['grouptype'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || $member['grouptype'] == 'special') {
			cpmsg('members_edit_illegal');
		}

		$member['groupterms'] = unserialize($member['groupterms']);

		if(!submitcheck('editsubmit')) {

			if($member['groupid'] == 4) {
				$check['post'] = 'checked';
			} elseif($member['groupid'] == 5) {
				$check['visit'] = 'checked';
			} else {
				$check['none'] = 'checked';
			}

			echo "<br><form method=\"post\" action=\"admincp.php?action=editmember&uid=$member[uid]&formhash=".FORMHASH."\">";

			if($allowbanuser) {
				$member['banexpiry'] = !empty($member['groupterms']['main']['time']) && ($member['groupid'] == 4 || $member['groupid'] == 5) ? gmdate('Y-n-j', $member['groupterms']['main']['time'] + $timeoffset * 3600) : '';
				showtype("$lang[members_edit_ban_user] - $member[username]", 'top');
				showsetting('members_edit_ban', '', '', '<input type="radio" name="bannew" value="" '.$check['none'].'> '.$lang['members_edit_ban_none'].'<br><input type="radio" name="bannew" value="post" '.$check['post'].'> '.$lang['members_edit_ban_post'].'<br><input type="radio" name="bannew" value="visit" '.$check['visit'].'> '.$lang['members_edit_ban_visit']);
				showsetting('members_edit_ban_validity', 'banexpirynew', $member['banexpiry'], 'text');
				showsetting('members_edit_ban_reason', 'reason', '', 'textarea');
				showtype('', 'bottom');
			}
			if($allowedituser) {
				showtype($lang['members_edit'], ($allowbanuser ? '' : 'top'));
				showsetting('members_edit_location', 'locationnew', $member['location'], 'text');
				showsetting('members_edit_bio', 'bionew', $member['bio'], 'textarea');
				showsetting('members_edit_signature', 'signaturenew', $member['signature'], 'textarea');
				showtype('', 'bottom');
			}

			echo '<br><br><center><input type="submit" name="editsubmit" value="'.$lang['submit'].'"></center></form>';

		} else {

			$sql = 'uid=uid';

			if($allowbanuser) {
					
				$reason = trim($reason);
				if(!$reason && ($reasonpm == 1 || $reasonpm == 3)) {
					cpmsg('members_edit_reason_invalid');
				}

				if($bannew == 'post' || $bannew == 'visit') {
					$groupidnew = $bannew == 'post' ? 4 : 5;
					$banexpirynew = intval(@strtotime($banexpirynew) - $timeoffset * 8 + date('Z'));
					$banexpirynew = $banexpirynew > $timestamp ? $banexpirynew : 0;
					if($banexpirynew) {
						$member['groupterms']['main'] = array('time' => $banexpirynew, 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
						$member['groupterms']['ext'][$groupidnew] = $banexpirynew;
						$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
					}
					$adminidnew = -1;
				} elseif($member['groupid'] == 4 || $member['groupid'] == 5) {
					if(!empty($member['groupterms']['main']['groupid'])) {
						$groupidnew = $member['groupterms']['main']['groupid'];
						$adminidnew = $member['groupterms']['main']['adminid'];
						unset($member['groupterms']['main']);
						unset($member['groupterms']['ext'][$member['groupid']]);
						$sql .= ', groupexpiry=\''.groupexpiry($member['groupterms']).'\'';
					} else {
						$query = $db->query("SELECT groupid FROM {$tablepre}usergroups WHERE type='member' AND creditshigher<='$member[credits]' AND creditslower>'$member[credits]'");
						$groupidnew = $db->result($query, 0);
						$adminidnew = 0;
					}
				} else {
					$groupidnew = $member['groupid'];
					$adminidnew = $member['adminid'];
				}
				$sql .= ", adminid='$adminidnew', groupid='$groupidnew'";
			}

			if($allowedituser) {
				require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

				$sightmlnew = addslashes(discuzcode(stripslashes($signaturenew), 1, 0, 0, 0, $member['allowsigbbcode'], $member['allowsigimgcode'], 0));
				$locationnew = dhtmlspecialchars($locationnew);

				$sql .= ', sigstatus=\''.($signaturenew ? 1 : 0).'\'';
				$db->query("UPDATE {$tablepre}memberfields SET location='$locationnew', bio='$bionew', signature='$signaturenew', sightml='$sightmlnew' WHERE uid='$member[uid]'");
			}

			$db->query("UPDATE {$tablepre}members SET $sql WHERE uid='$member[uid]'");
			if($allowbanuser && ($db->affected_rows($query))) {
				banlog($member['username'], $member['groupid'], $groupidnew, $banexpirynew, $reason);
			}

			$db->query("UPDATE {$tablepre}memberfields SET groupterms='".($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '')."' WHERE uid='$member[uid]'");

			cpmsg('members_edit_succeed', 'admincp.php?action=editmember');

		}

	}

} elseif($action == 'access') {

	$query = $db->query("SELECT username FROM {$tablepre}members WHERE uid='$uid'");
	if(!$member = $db->fetch_array($query)) {
		cpmsg('undefined_action');
	}

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

	if(!submitcheck('accesssubmit')) {

		$accessmasks = array();
		$query = $db->query("SELECT * FROM {$tablepre}access WHERE uid='$uid'");
		while($access = $db->fetch_array($query)) {
			$accessmasks[$access['fid']] = $access;
		}

		$members = '';
		foreach($_DCACHE['forums'] as $fid => $forum) {
			if($forum['type'] != 'group') {
				if(isset($accessmasks[$fid])) {
					$check = array(	'default'	=> '',
							'view'		=> ($accessmasks[$fid]['allowview'] ? 'checked' : ''),
							'post'		=> ($accessmasks[$fid]['allowpost'] ? 'checked' : ''),
							'reply'		=> ($accessmasks[$fid]['allowreply'] ? 'checked' : ''),
							'getattach'	=> ($accessmasks[$fid]['allowgetattach'] ? 'checked' : ''),
							'postattach'	=> ($accessmasks[$fid]['allowpostattach'] ? 'checked' : ''));
				} else {
					$check = array(	'default'	=> 'checked',
							'view'		=> '',
							'post'		=> '',
							'reply'		=> '',
							'getattach'	=> '',
							'postattach'	=> '');
				}

				$members .= "<tr><td bgcolor=\"".ALTBG1."\" width=\"22%\"><a href=\"admincp.php?action=forumdetail&fid=$forum[fid]\">$forum[name]</a></td>".
						"<td bgcolor=\"".ALTBG2."\" width=\"13%\" align=\"center\"><input type=\"checkbox\" name=\"defaultnew[$fid]\" value=\"1\" $check[default]></td>\n".
						"<td bgcolor=\"".ALTBG1."\" width=\"13%\" align=\"center\"><input type=\"checkbox\" name=\"allowviewnew[$fid]\" value=\"1\" $check[view]></td>\n".
						"<td bgcolor=\"".ALTBG2."\" width=\"13%\" align=\"center\"><input type=\"checkbox\" name=\"allowpostnew[$fid]\" value=\"1\" $check[post]></td>\n".
						"<td bgcolor=\"".ALTBG1."\" width=\"13%\" align=\"center\"><input type=\"checkbox\" name=\"allowreplynew[$fid]\" value=\"1\" $check[reply]></td>\n".
						"<td bgcolor=\"".ALTBG2."\" width=\"13%\" align=\"center\"><input type=\"checkbox\" name=\"allowgetattachnew[$fid]\" value=\"1\" $check[getattach]></td>\n".
						"<td bgcolor=\"".ALTBG1."\" width=\"13%\" align=\"center\"><input type=\"checkbox\" name=\"allowpostattachnew[$fid]\" value=\"1\" $check[postattach]></td></tr>";
			}
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['access_tips']?>
</td></tr></table>

<form method="post" action="admincp.php?action=access&uid=<?=$uid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="7"><?=$lang['access_edit']?> - <?=$member['username']?></td></tr>
<tr class="category" align="center">
<td><?=$lang['forum']?></td><td><?=$lang['access_default']?></td><td><?=$lang['access_view']?></td><td><?=$lang['access_post']?></td><td><?=$lang['access_reply']?></td><td><?=$lang['access_getattach']?></td><td><?=$lang['access_postattach']?></td></tr>
<?=$members?>
</table><br>
<center><input type="submit" name="accesssubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		$accessarray = array();
		if(is_array($_DCACHE['forums'])) {
			foreach($_DCACHE['forums'] as $fid => $forum) {
				if($forum['type'] != 'group') {
					if(!$defaultnew[$fid] && ($allowviewnew[$fid] || $allowpostnew[$fid] || $allowreplynew[$fid] || $allowgetattachnew[$fid] || $allowpostattachnew[$fid])) {
						$accessarray[$fid] = "'$allowviewnew[$fid]', '$allowpostnew[$fid]', '$allowreplynew[$fid]', '$allowgetattachnew[$fid]', '$allowpostattachnew[$fid]'";
					}
				}
			}
		}

		$db->query("DELETE FROM {$tablepre}access WHERE uid='$uid'");
		$db->query("UPDATE {$tablepre}members SET accessmasks='".($accessarray ? 1 : 0)."' WHERE uid='$uid'");

		foreach($accessarray as $fid => $access) {
			$db->query("INSERT INTO {$tablepre}access (uid, fid, allowview, allowpost, allowreply, allowgetattach, allowpostattach)
					VALUES ('$uid', '$fid', $access)");
		}

		updatecache('forums');

		cpmsg('access_succeed');

	}

} elseif($action == 'memberprofile') {

	$query = $db->query("SELECT m.*, mf.*, o.*, u.type, u.allowsigbbcode, u.allowsigimgcode FROM {$tablepre}members m
		LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
		LEFT JOIN {$tablepre}onlinetime o ON o.uid=m.uid
		LEFT JOIN {$tablepre}usergroups u ON u.groupid=m.groupid
		WHERE m.uid='$uid'");

	if(!$member = $db->fetch_array($query)) {
		cpmsg('undefined_action');
	}

	require_once DISCUZ_ROOT.'./forumdata/cache/cache_profilefields.php';
	$fields = array_merge($_DCACHE['fields_required'], $_DCACHE['fields_optional']);

	if(!submitcheck('editsubmit')) {

		$styleselect = "<select name=\"styleidnew\">\n<option value=\"\">$lang[use_default]</option>";
		$query = $db->query("SELECT styleid, name FROM {$tablepre}styles");
		while($style = $db->fetch_array($query)) {
			$styleselect .= "<option value=\"$style[styleid]\" ".($style['styleid'] == $member['styleid'] ? 'selected="selected"' : '').">$style[name]</option>\n";
		}
		$styleselect .= '</select>';

		$tfcheck = array($member['timeformat'] => 'checked');
		$gendercheck = array($member['gender'] => 'checked');
		$pscheck = array($member['pmsound'] => 'checked');

		$member['dateformat'] = str_replace('n', 'mm', $member['dateformat']);
		$member['dateformat'] = str_replace('j', 'dd', $member['dateformat']);
		$member['dateformat'] = str_replace('y', 'yy', $member['dateformat']);
		$member['dateformat'] = str_replace('Y', 'yyyy', $member['dateformat']);

		$member['regdate'] = gmdate('Y-n-j h:i A', $member['regdate'] + $timeoffset * 3600);
		$member['lastvisit'] = gmdate('Y-n-j h:i A', $member['lastvisit'] + $timeoffset * 3600);

		echo "<br><form method=\"post\" action=\"admincp.php?action=memberprofile&uid=$uid&formhash=".FORMHASH."\">";

		showtype("$lang[members_edit] - $member[username]", 'top');
		showsetting('members_edit_username', 'usernamenew', $member['username'], 'text');
		showsetting('members_edit_password', 'passwordnew', '', 'text');
		showsetting('members_edit_clearquestion', 'clearquestion', !$member['secques'], 'radio');
		showsetting('members_edit_nickname', 'nicknamenew', $member['nickname'], 'text');
		showsetting('members_edit_gender', '', '', '<input type="radio" name="gendernew" value="1" '.$gendercheck[1].'> '.$lang['members_edit_gender_male'].' <input type="radio" name="gendernew" value="2" '.$gendercheck[2].'> '.$lang['members_edit_gender_female'].' <input type="radio" name="gendernew" value="0" '.$gendercheck[0].'> '.$lang['members_edit_gender_secret']);
		showsetting('members_edit_email', 'emailnew', $member['email'], 'text');
		showsetting('members_edit_posts', 'postsnew', $member['posts'], 'text');
		showsetting('members_edit_digestposts', 'digestpostsnew', $member['digestposts'], 'text');
		showsetting('members_edit_pageviews', 'pageviewsnew', $member['pageviews'], 'text');
		showsetting('members_edit_online_total', 'totalnew', $member['total'], 'text');
		showsetting('members_edit_online_thismonth', 'thismonthnew', $member['thismonth'], 'text');
		showsetting('members_edit_regip', 'regipnew', $member['regip'], 'text');
		showsetting('members_edit_regdate', 'regdatenew', $member['regdate'], 'text');
		showsetting('members_edit_lastvisit', 'lastvisitnew', $member['lastvisit'], 'text');
		showsetting('members_edit_lastip', 'lastipnew', $member['lastip'], 'text');
		
		showtype('members_edit_info');
		showsetting('members_edit_site', 'sitenew', $member['site'], 'text');
		showsetting('members_edit_qq', 'qqnew', $member['qq'], 'text');
		showsetting('members_edit_icq', 'icqnew', $member['icq'], 'text');
		showsetting('members_edit_yahoo', 'yahoonew', $member['yahoo'], 'text');
		showsetting('members_edit_msn', 'msnnew', $member['msn'], 'text');
		showsetting('members_edit_location', 'locationnew', $member['location'], 'text');
		showsetting('members_edit_bday', 'bdaynew', $member['bday'], 'text');
		showsetting('members_edit_avatar', 'avatarnew', $member['avatar'], 'text');
		showsetting('members_edit_avatar_width', 'avatarwidthnew', $member['avatarwidth'], 'text');
		showsetting('members_edit_avatar_height', 'avatarheightnew', $member['avatarheight'], 'text');
		showsetting('members_edit_avatarshowid', 'avatarshowidnew', $member['avatarshowid'], 'text');
		showsetting('members_edit_bio', 'bionew', $member['bio'], 'textarea');
		showsetting('members_edit_signature', 'signaturenew', $member['signature'], 'textarea');

		showtype('members_edit_option');
		showsetting('members_edit_style', '', '', $styleselect);
		showsetting('members_edit_tpp', 'tppnew', $member['tpp'], 'text');
		showsetting('members_edit_ppp', 'pppnew', $member['ppp'], 'text');
		showsetting('members_edit_cstatus', 'cstatusnew', $member['customstatus'], 'text');
		showsetting('members_edit_timeformat', '', '', '<input type="radio" name="timeformatnew" value="0" '.$tfcheck[0].'> '.$lang['default'].' &nbsp; <input type="radio" name="timeformatnew" value="1" '.$tfcheck[1].'> '.$lang['members_edit_timeformat_12'].' &nbsp; <input type="radio" name="timeformatnew" value="2" '.$tfcheck[2].'> '.$lang['members_edit_timeformat_24']);
		showsetting('members_edit_dateformat', 'dateformatnew', $member['dateformat'], 'text');
		showsetting('members_edit_timeoffset', 'timeoffsetnew', $member['timeoffset'], 'text');
		showsetting('members_edit_pmsound', '', '', '<input type="radio" value="0" name="pmsoundnew" '.$pscheck[0].'>'.$lang['none'].' &nbsp; <input type="radio" value="1" name="pmsoundnew" '.$pscheck[1].'><a href="images/sound/pm_1.wav">#1</a> &nbsp; <input type="radio" value="2" name="pmsoundnew" '.$pscheck[2].'><a href="images/sound/pm_2.wav">#2</a> &nbsp; <input type="radio" value="3" name="pmsoundnew" '.$pscheck[3].'><a href="images/sound/pm_3.wav">#3</a>');
		showsetting('members_edit_invisible', 'invisiblenew', $member['invisible'], 'radio');
		showsetting('members_edit_showemail', 'showemailnew', $member['showemail'], 'radio');
		showsetting('members_edit_newsletter', 'newsletternew', $member['newsletter'], 'radio');
		showsetting('members_edit_ignorepm', 'ignorepmnew', $member['ignorepm'], 'textarea');

		if($fields) {
			showtype('members_edit_profilefield');
			foreach($fields as $field) {
				if($field['selective']) {
					$fieldselect = "<select name=\"field_$field[fieldid]new\"><option value=\"\">&nbsp;</option>";
					foreach($field['choices'] as $index => $choice) {
						$fieldselect .= "<option value=\"$index\" ".($index == $member['field_'.$field['fieldid']] ? 'selected="selected"' : '').">$choice</option>";
					}
					$fieldselect .= '</select>';
					showsetting($field['title'], '', '', $fieldselect);
				} else {
					showsetting($field['title'], "field_$field[fieldid]new", $member['field_'.$field['fieldid']], 'text');
				}
			}
		}

		showtype('', 'bottom');

		echo '<br><br><center><input type="submit" name="editsubmit" value="'.$lang['submit'].'"></center></form>';

	} else {

		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

		$usernameold = addslashes($member['username']);
		if($usernamenew && $usernameold != $usernamenew) {
			$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$usernamenew'");
			if(($db->result($query, 0)) && ($db->result($query, 0)) != $member['uid']) {
				cpmsg('members_edit_duplicate');
			}
			$db->query("UPDATE {$tablepre}announcements SET author='$usernamenew' WHERE author='$usernameold'");
			$db->query("UPDATE {$tablepre}banned SET admin='$usernamenew' WHERE admin='$usernameold'");
			$db->query("UPDATE {$tablepre}forums SET lastpost=REPLACE(lastpost, '\t$usernameold', '\t$usernamenew')");
			$db->query("UPDATE {$tablepre}members SET username='$usernamenew' WHERE uid='$member[uid]'");
			$db->query("UPDATE {$tablepre}pms SET msgfrom='$usernamenew' WHERE msgfromid='$member[uid]'");
			$db->query("UPDATE {$tablepre}posts SET author='$usernamenew' WHERE authorid='$member[uid]'");
			$db->query("UPDATE {$tablepre}threads SET author='$usernamenew' WHERE authorid='$member[uid]'");
			$db->query("UPDATE {$tablepre}threads SET lastposter='$usernamenew' WHERE lastposter='$usernameold'");
			$db->query("UPDATE {$tablepre}threadsmod SET username='$usernamenew' WHERE uid='$member[uid]'");

			$username = $usernamenew;
		}

		$creditsnew = intval($creditsnew);

		$regdatenew = strtotime($regdatenew);
		$lastvisitnew = strtotime($lastvisitnew);

		$passwordadd = $passwordnew ? ", password='".md5($passwordnew)."'" : '';
		$secquesadd = $clearquestion ? ", secques=''" : '';

		$dateformatnew = str_replace('mm', 'n', $dateformatnew);
		$dateformatnew = str_replace('dd', 'j', $dateformatnew);
		$dateformatnew = str_replace('yyyy', 'Y', $dateformatnew);
		$dateformatnew = str_replace('yy', 'y', $dateformatnew);

		$signaturenew = censor($signaturenew);
		$sigstatusnew = $signaturenew ? 1 : 0;
		$sightmlnew = addslashes(discuzcode(stripslashes($signaturenew), 1, 0, 0, 0, $member['allowsigbbcode'], $member['allowsigimgcode'], 0));

		$oltimenew = round($totalnew / 60);

		$fieldadd = '';
		foreach(array_merge($_DCACHE['fields_required'], $_DCACHE['fields_optional']) as $field) {
			$field_key = 'field_'.$field['fieldid'];
			$field_val = trim(${'field_'.$field['fieldid'].'new'});
			if($field['selective'] && $field_val != '' && !isset($field['choices'][$field_val])) {
				cpmsg('undefined_action');
			} else {
				$fieldadd .= ", $field_key='".dhtmlspecialchars($field_val)."'";
			}
		}

		$db->query("UPDATE {$tablepre}members SET gender='$gendernew', email='$emailnew', posts='$postsnew', digestposts='$digestpostsnew',
			pageviews='$pageviewsnew', regip='$regipnew', regdate='$regdatenew', lastvisit='$lastvisitnew', lastip='$lastipnew', bday='$bdaynew',
			styleid='$styleidnew', tpp='$tppnew', ppp='$pppnew', timeformat='$timeformatnew', dateformat='$dateformatnew', oltime='$oltimenew',
			avatarshowid='$avatarshowidnew', showemail='$showemailnew', newsletter='$newsletternew', invisible='$invisiblenew', timeoffset='$timeoffsetnew',
			pmsound='$pmsoundnew', sigstatus='$sigstatusnew' $passwordadd $secquesadd WHERE uid='$uid'");

		$db->query("UPDATE {$tablepre}memberfields SET nickname='$nicknamenew', site='$sitenew', qq='$qqnew', icq='$icqnew', yahoo='$yahoonew', msn='$msnnew',
			location='$locationnew', bio='$bionew', customstatus='$cstatusnew', ignorepm='$ignorepmnew', avatar='$avatarnew',
			avatarwidth='$avatarwidthnew', avatarheight='$avatarheightnew', signature='$signaturenew', sightml='$sightmlnew'
			$fieldadd WHERE uid='$uid'");

		$db->query("REPLACE INTO {$tablepre}onlinetime (uid, thismonth, total)
			VALUES ('$uid', '$thismonthnew', '$totalnew')");

		cpmsg('members_edit_succeed');

	}

} elseif($action == 'profilefields') {

	if(!submitcheck('fieldsubmit') && !submitcheck('editsubmit') && !$edit) {

		$query = $db->query("SELECT * FROM {$tablepre}profilefields");
		while($field = $db->fetch_array($query)) {
			$profilefields .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[{$field[fieldid]}]\" value=\"$field[fieldid]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"18\" name=\"titlenew[{$field[fieldid]}]\" value=\"$field[title]\">\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"availablenew[{$field[fieldid]}]\" value=\"1\" ".($field['available'] ? 'checked' : '')."></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"checkbox\" name=\"invisiblenew[{$field[fieldid]}]\" value=\"1\" ".($field['invisible'] ? 'checked' : '')."></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"unchangeablenew[{$field[fieldid]}]\" value=\"1\" ".($field['unchangeable'] ? 'checked' : '')."></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"checkbox\" name=\"showinthreadnew[{$field[fieldid]}]\" value=\"1\" ".($field['showinthread'] ? 'checked' : '')."></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\" name=\"displayordernew[{$field[fieldid]}]\" value=\"$field[displayorder]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><a href=\"admincp.php?action=profilefields&edit=$field[fieldid]\">[$lang[detail]]</a></td></tr>\n";
		}

?>
<form method="post" action="admincp.php?action=profilefields">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header" align="center">
<td width="48"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['fields_title']?></td><td><?=$lang['available']?></td><td><?=$lang['fields_invisible']?></td><td><?=$lang['fields_unchangeable']?></td><td><?=$lang['fields_show_in_thread']?></td><td><?=$lang['display_order']?></td><td><?=$lang['edit']?></td></tr>
<?=$profilefields?>
<tr><td colspan="7" class="singleborder">&nbsp;</td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>"><?=$lang['add_new']?></td>
<td bgcolor="<?=ALTBG2?>"><input type='text' name="newtitle" size="18"></td>
<td colspan="6" bgcolor="<?=ALTBG2?>">&nbsp;</td>
</tr></table><br>
<center><input type="submit" name="fieldsubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} elseif(submitcheck('fieldsubmit')) {

		if(is_array($titlenew)) {
			foreach($titlenew as $id => $val) {
				$db->query("UPDATE {$tablepre}profilefields SET title='$titlenew[$id]', available='$availablenew[$id]', invisible='$invisiblenew[$id]', displayorder='$displayordernew[$id]', unchangeable='$unchangeablenew[$id]', showinthread='$showinthreadnew[$id]' WHERE fieldid='$id'");
			}
		}

		if(is_array($delete)) {
			$ids = implode('\',\'', $delete);
			$dropfields = implode(',DROP field_', $delete);
			$db->query("DELETE FROM {$tablepre}profilefields WHERE fieldid IN ('$ids')");
			$db->query("ALTER TABLE {$tablepre}memberfields DROP field_$dropfields");
		}

		if($newtitle) {
			$db->query("INSERT INTO {$tablepre}profilefields (available, invisible, title, size)
					VALUES ('1', '0', '$newtitle', '50')");
			$fieldid = $db->insert_id();
			$db->query("ALTER TABLE {$tablepre}memberfields ADD field_$fieldid varchar(50) NOT NULL", 'SILENT');
		}

		updatecache('fields_required');
		updatecache('fields_optional');
		updatecache('fields_thread');
		cpmsg('fields_edit_succeed', 'admincp.php?action=profilefields');

	} elseif($edit) {

		$query = $db->query("SELECT * FROM {$tablepre}profilefields WHERE fieldid='$edit'");
		if(!$field = $db->fetch_array($query)) {
			cpmsg('undefined_action');
		}

		if(!submitcheck('editsubmit')) {

			echo "<form method=\"post\" action=\"admincp.php?action=profilefields&edit=$edit&formhash=".FORMHASH."\">\n";

			showtype("$lang[fields_edit] - $field[title]", 'top');
			showsetting('fields_edit_title', 'titlenew', $field['title'], 'text');
			showsetting('fields_edit_desc', 'descriptionnew', $field['description'], 'text');
			showsetting('fields_edit_size', 'sizenew', $field['size'], 'text');
			showsetting('fields_edit_invisible', 'invisiblenew', $field['invisible'], 'radio');
			showsetting('fields_edit_required', 'requirednew', $field['required'], 'radio');
			showsetting('fields_edit_unchangeable', 'unchangeablenew', $field['unchangeable'], 'radio');
			showsetting('fields_edit_show_in_thread', 'showinthreadnew', $field['showinthread'], 'radio');
			showsetting('fields_edit_selective', 'selectivenew', $field['selective'], 'radio');
			showsetting('fields_edit_choices', 'choicesnew', $field['choices'], 'textarea');
			showtype('', 'bottom');

			echo "<br><center><input type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

		} else {

			$titlenew = trim($titlenew);
			$sizenew = $sizenew <= 255 ? $sizenew : 255;
			if(!$titlenew || !$sizenew) {
				cpmsg('fields_invalid');
			}

			if($sizenew != $field['size']) {
				$db->query("ALTER TABLE {$tablepre}memberfields CHANGE field_$edit field_$edit varchar($sizenew) NOT NULL");
			}

			$db->query("UPDATE {$tablepre}profilefields SET title='$titlenew', description='$descriptionnew', size='$sizenew', invisible='$invisiblenew', required='$requirednew', unchangeable='$unchangeablenew', showinthread='$showinthreadnew', selective='$selectivenew', choices='$choicesnew' WHERE fieldid='$edit'");

			updatecache('fields_required');
			updatecache('fields_optional');
			updatecache('fields_thread');
			cpmsg('fields_edit_succeed', 'admincp.php?action=profilefields');
		}

	}

} elseif($action == 'ipban') {

	if(!submitcheck('ipbansubmit')) {

		require_once DISCUZ_ROOT.'./include/misc.func.php';

		$iptoban = explode('.', $ip);

		$ipbanned = '';
		$query = $db->query("SELECT * FROM {$tablepre}banned ORDER BY dateline");
		while($banned = $db->fetch_array($query)) {
			for($i = 1; $i <= 4; $i++) {
				if($banned["ip$i"] == -1) {
					$banned["ip$i"] = '*';
				}
			}
			$disabled = $adminid != 1 && $banned['admin'] != $discuz_userss ? 'disabled' : '';
			$banned['dateline'] = gmdate($dateformat, $banned['dateline'] + $timeoffset * 3600);
			$banned['expiration'] = gmdate($dateformat, $banned['expiration'] + $timeoffset * 3600);
			$theip = "$banned[ip1].$banned[ip2].$banned[ip3].$banned[ip4]";
			$ipbanned .= "<tr align=\"center\">\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[$banned[id]]\" value=\"$banned[id]\" $disabled></td>\n".
				"<td bgcolor=\"".ALTBG2."\">$theip</td>\n".
				"<td bgcolor=\"".ALTBG1."\">".convertip($theip, "./")."</td>\n".
				"<td bgcolor=\"".ALTBG2."\">$banned[admin]</td>\n".
				"<td bgcolor=\"".ALTBG1."\">$banned[dateline]</td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"10\" name=\"expirationnew[$banned[id]]\" value=\"$banned[expiration]\" $disabled></td></tr>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['ipban_tips']?>
</td></tr></table>

<form method="post" action="admincp.php?action=ipban">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header" align="center">
<td width="48"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['ip']?></td><td><?=$lang['ipban_location']?></td><td><?=$lang['operator']?></td><td><?=$lang['start_time']?></td><td><?=$lang['end_time']?></td></tr>
<?=$ipbanned?>
<tr><td colspan="6" class="singleborder">&nbsp;</td></tr>
<tr align="center" bgcolor="<?=ALTBG1?>">
<td><?=$lang['add_new']?></td>
<td colspan="3"><b>
<input type="text" name="ip1new" value="<?=$iptoban[0]?>" size="3" maxlength="3"> . 
<input type="text" name="ip2new" value="<?=$iptoban[1]?>" size="3" maxlength="3"> . 
<input type="text" name="ip3new" value="<?=$iptoban[2]?>" size="3" maxlength="3"> . 
<input type="text" name="ip4new" value="<?=$iptoban[3]?>" size="3" maxlength="3"></b></td>
<td colspan="2"><?=$lang['validity']?>: <input type="text" name="validitynew" value="30" size="3"> <?=$lang['ipban_days']?></td>
</tr></table><br>
<center><input type="submit" name="ipbansubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$ids = $comma = '';
		if(is_array($delete)) {
			foreach($delete as $id) {
				$ids .= "$comma'$id'";
				$comma = ', ';
			}
		}
		if($ids) {
			$db->query("DELETE FROM {$tablepre}banned WHERE id IN ($ids) AND ('$adminid'='1' OR admin='$discuz_user')");
		}

		if($ip1new != '' && $ip2new != '' && $ip3new != '' && $ip4new != '') {
			$own = 0;
			$ip = explode('.', $onlineip);
			for($i = 1; $i <= 4; $i++) {
				if(!is_numeric(${'ip'.$i.'new'}) || ${'ip'.$i.'new'} < 0) {
					if($adminid != 1) {
						cpmsg('ipban_nopermission');
					}
					${'ip'.$i.'new'} = -1;
					$own++;
				} elseif(${'ip'.$i.'new'} == $ip[$i - 1]) {
					$own++;
				}
				${'ip'.$i.'new'} = intval(${'ip'.$i.'new'});
			}

			if($own == 4) {
				cpmsg('ipban_illegal');
			}

			$query = $db->query("SELECT * FROM {$tablepre}banned");
			while($banned = $db->fetch_array($query)) {
				$exists = 0;
				for($i = 1; $i <= 4; $i++) {
					if($banned["ip$i"] == -1) {
						$exists++;
					} elseif($banned["ip$i"] == ${"ip".$i."new"}) {
						$exists++;
					}
				}
				if($exists == 4) {
					cpmsg('ipban_invalid');
				}
			}

			$expiration = $timestamp + $validitynew * 86400;

			$db->query("UPDATE {$tablepre}sessions SET groupid='6' WHERE ('$ip1new'='-1' OR ip1='$ip1new') AND ('$ip2new'='-1' OR ip2='$ip2new') AND ('$ip3new'='-1' OR ip3='$ip3new') AND ('$ip4new'='-1' OR ip4='$ip4new')");
			$db->query("INSERT INTO {$tablepre}banned (ip1, ip2, ip3, ip4, admin, dateline, expiration)
				VALUES ('$ip1new', '$ip2new', '$ip3new', '$ip4new', '$discuz_user', '$timestamp', '$expiration')");

		}

		if(is_array($expirationnew)) {
			foreach($expirationnew as $id => $expiration) {
				$db->query("UPDATE {$tablepre}banned SET expiration='".strtotime($expiration)."' WHERE id='$id' AND ('$adminid'='1' OR admin='$discuz_user')");
			}
		}

		updatecache('ipbanned');
		cpmsg('ipban_succeed', 'admincp.php?action=ipban');

	}
	
}

function banlog($username, $origgroupid, $newgroupid, $expiration, $reason) {
	global $discuz_userss, $groupid, $onlineip, $timestamp, $forum, $reason;
	@$fp = fopen(DISCUZ_ROOT.'./forumdata/banlog.php', 'a');
	@flock($fp, 2);
	@fwrite($fp, "$timestamp\t".dhtmlspecialchars($discuz_userss)."\t$groupid\t$onlineip\t".dhtmlspecialchars($username)."\t$origgroupid\t$newgroupid\t$expiration\t".dhtmlspecialchars(stripslashes($reason))."\n");
	@fclose($fp);
}

?>