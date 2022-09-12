<?php

/*
	[DISCUZ!] admin/main.php - homepage of administrators' control panel
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-2-19 21:10
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

if(file_exists(DISCUZ_ROOT.'./install.php')) {
	@unlink(DISCUZ_ROOT.'./install.php');
	if(file_exists(DISCUZ_ROOT.'./install.php')) {
		dexit('Please delete install.php via FTP!');
	}
}

require_once DISCUZ_ROOT.'./discuz_version.php';
require_once DISCUZ_ROOT.'./include/attachment.func.php';

$onlines = array();
$query = $db->query("SELECT a.*, m.username, m.adminid, m.regip
	FROM {$tablepre}adminsessions a
	LEFT JOIN {$tablepre}members m USING(uid) ORDER BY a.errorcount");

while($member = $db->fetch_array($query)) {
	$memlink = '<a href="viewpro.php?uid='.$member['uid'].'" target="_blank" alt="'.
		"$lang[time]: ".gmdate("$dateformat $timeformat", $member['dateline'] + $timeoffset * 3600)."\n".
		($member['errorcount'] == -1 ? '' : "$lang[home_onlines_errors]: $member[errorcount]\n").
		($allowviewip && ($adminid <= $member['adminid'] || $member['adminid'] <= 0) ? "$lang[home_online_regip]: ".
		"$member[regip]\n$lang[home_onlines_ip]: $member[ip]" : '').'">'.
		$member['username'].'</a>';
	$onlines[] = $member['errorcount'] == -1 ? $memlink : "<i>$memlink</i>";
}

if(submitcheck('notesubmit')) {
	if(is_array($delete)) {
		$db->query("DELETE FROM {$tablepre}adminnotes WHERE id IN ('".implode('\',\'', $delete)."') AND (admin='$discuz_user' OR adminid>='$adminid')");
	}
	if($newmessage) {
		$newaccess[$adminid] = 1;
		$newaccess = bindec(intval($newaccess[1]).intval($newaccess[2]).intval($newaccess[3]));
		$newexpiration = strtotime($newexpiration) - $timeoffset * 3600 + date('Z');
		$newmessage = nl2br(dhtmlspecialchars($newmessage));
		$db->query("INSERT INTO {$tablepre}adminnotes (admin, access, adminid, dateline, expiration, message)
			VALUES ('$discuz_user', '$newaccess', '$adminid', '$timestamp', '$newexpiration', '$newmessage')");
	}
}

switch($adminid) {
	case 1: $access = '4,5,6,7'; break;
	case 2: $access = '2,3,6,7'; break;
	default: $access = '1,3,5,7'; break;
}

$notes = '';
$query = $db->query("SELECT * FROM {$tablepre}adminnotes WHERE access IN ($access) ORDER BY dateline DESC");
while($note = $db->fetch_array($query)) {
	if($note['expiration'] < $timestamp) {
		$db->query("DELETE FROM {$tablepre}adminnotes WHERE id='$note[id]'");
	} else {
		$note['adminenc'] = rawurlencode($note['admin']);
		$note['dateline'] = gmdate("$dateformat $timeformat", $note['dateline'] + $timeoffset * 3600);
		$note['expiration'] = gmdate($dateformat, $note['expiration'] + $timeoffset * 3600);
		$note['access'] = sprintf('%03b', $note['access']);
		$notes .= "<tr class=\"smalltxt\"><td bgcolor=\"".ALTBG1."\" align=\"center\"><input type=\"checkbox\" name=\"delete[]\" ".($note['admin'] == $discuz_userss || $note['adminid'] >= $adminid ? "value=\"$note[id]\"" : 'disabled')."></td>\n".
			"<td bgcolor=\"".ALTBG2."\" align=\"center\"><a href=\"viewpro.php?username=$note[adminenc]\" target=\"_blank\">$note[admin]</a></td>\n".
			"<td bgcolor=\"".ALTBG1."\" align=\"center\">$note[dateline]</td>\n".
			"<td bgcolor=\"".ALTBG2."\"><b>$note[message]</b></td>\n".
			"<td bgcolor=\"".ALTBG1."\" align=\"center\">".($note['access'][0] ? $lang['yes'] : '')."</td>\n".
			"<td bgcolor=\"".ALTBG2."\" align=\"center\">".($note['access'][1] ? $lang['yes'] : '')."</td>\n".
			"<td bgcolor=\"".ALTBG1."\" align=\"center\">".($note['access'][2] ? $lang['yes'] : '')."</td>\n".
			"<td bgcolor=\"".ALTBG2."\" align=\"center\">$note[expiration]</td></tr>\n";
	}
}

if($adminid == 1) {

	require_once DISCUZ_ROOT.'./include/forum.func.php';
	require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

	$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
	$serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
	$dbversion = $db->result($db->query("SELECT VERSION()"), 0);

	if(@ini_get('file_uploads')) {
		$fileupload = $lang['yes'].': file '.ini_get('upload_max_filesize').' - form '.ini_get('post_max_size');
	} else {
		$fileupload = '<font color="red">'.$lang['no'].'</font>';
	}

	$groupselect = '';
	$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups ORDER BY creditslower, groupid");
	while($group = $db->fetch_array($query)) {
		$groupselect .= '<option value="'.$group['groupid'].'">'.$group['grouptitle'].'</option>';
	}

	$dbsize = 0;
	$query = $db->query("SHOW TABLE STATUS LIKE '$tablepre%'", 'SILENT');
	while($table = $db->fetch_array($query)) {
		$dbsize += $table['Data_length'] + $table['Index_length'];
	}
	$dbsize = $dbsize ? sizecount($dbsize) : $lang['unknown'];

	if(isset($attachsize)) {
		$attachsize = dirsize($attachdir);
		$attachsize = is_numeric($attachsize) ? sizecount($attachsize) : $lang['unknown'];
	} else {
		$attachsize = '<a href="admincp.php?action=home&attachsize">[ '.$lang['detail'].' ]</a>';
	}

	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE invisible='2'");
	$postsmod = $db->result($query, 0);

	$threadsdel = $threadsmod = 0;
	$query = $db->query("SELECT displayorder FROM {$tablepre}threads WHERE displayorder<'0'");
	while($thread = $db->fetch_array($query)) {
		if($thread['displayorder'] == -1) {
			$threadsdel++;
		} elseif($thread['displayorder'] == -2) {
			$threadsmod++;
		}
	}

} elseif($allowmodpost) {

	if($adminid == 3) {
		$fids = '0';
		$query = $db->query("SELECT fid FROM {$tablepre}moderators WHERE uid='$discuz_uid'");
		while($forum = $db->fetch_array($query)) {
			$fids .= ','.$forum['fid'];
		}
		if($fids) {
			$fidadd = "fid IN ($fids) AND";
		} else {
			$fidadd = '';
			$allowmodpost = 0;
		}
	}

	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}threads WHERE $fidadd displayorder='-2'");
	$threadsmod = $db->result($query, 0);

	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE $fidadd invisible='2'");
	$postsmod = $db->result($query, 0);

}	

cpheader();

?>
<span class="outertxt">
<b><?=$lang['welcome_to']?> <a href="http://www.Discuz.net" target="_blank">Discuz! <?=$version?></a> <?=$lang['admincp']?></b><br>
Copyright&copy; <a href="http://www.comsenz.com" target="_blank">Comsenz Technology Ltd</a>, 2001-2005.</span><br><br><br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="0" width="85%" align="center" class="tableborder">
<tr><td><table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="3"><?=$lang['home_onlines']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=implode(', ', $onlines)?></td></tr></table></td></tr></table></br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="0" width="85%" align="center" class="tableborder">
<tr><td><table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="3"><?=$lang['home_stuff']?></td></tr>

<form method="get" action="http://www.php.net/manual-lookup.php" target="_blank"><tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_php_lookup']?></td>
<td><input type="text" size="30" name="function"></td><td><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<? if(($adminid == 2 || $adminid == 3) && ($allowedituser || $allowbanuser)) { ?>

<form method="post" action="admincp.php?action=editmember">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_edit_member']?></td>
<td><input type="text" size="30" name="username"></td><td><input type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></td></tr></form>

<? } ?>

<? if($adminid == 1) { ?>

<form method="post" action="admincp.php?action=members">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_edit_member']?></td>
<td><input type="text" size="30" name="username"></td><td><input type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="post" action="admincp.php?action=forumdetail">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_edit_forum']?></td>
<td><select name="fid"><option value="">&nbsp;&nbsp;> <?=$lang['select']?></option><option value="">&nbsp;</option>
<?=forumselect(1)?></select></td><td><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="post" action="admincp.php?action=usergroups">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_edit_group']?></td>
<td><select name="edit"><?=$groupselect?></td><td><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<? } ?>

</table></td></tr></table><br>

<form method="post" action="admincp.php?action=home">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header" align="center"><td width="48"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form, 'delete');"><?=$lang['del']?></td>
<td><?=$lang['username']?></td>
<td><?=$lang['time']?></td>
<td width="35%"><?=$lang['message']?></td>
<td width="8%" nowrap><?=$lang['usergroups_system_1']?></td>
<td width="8%" nowrap><?=$lang['usergroups_system_2']?></td>
<td width="8%" nowrap><?=$lang['usergroups_system_3']?></td>
<td width="15%"><?=$lang['validity']?></td></tr>
<?=$notes?>
<tr><td colspan="8" class="singleborder">&nbsp;</td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>"><?=$lang['add_new']?></td>
<td bgcolor="<?=ALTBG2?>" colspan="3"><textarea name="newmessage" rows="2" style="width: 100%; word-break: break-all"></textarea></td>
<td bgcolor="<?=ALTBG1?>"><input type="checkbox" name="newaccess[1]" value="1" checked <?=($adminid == 1 ? 'disabled' : '')?>></td>
<td bgcolor="<?=ALTBG2?>"><input type="checkbox" name="newaccess[2]" value="1" checked <?=($adminid == 2 ? 'disabled' : '')?>></td>
<td bgcolor="<?=ALTBG1?>"><input type="checkbox" name="newaccess[3]" value="1" checked <?=($adminid == 3 ? 'disabled' : '')?>></td>
<td bgcolor="<?=ALTBG2?>"><input type="text" name="newexpiration" size="8" value="<?=gmdate('Y-n-j', $timestamp + $timeoffset * 3600 + 86400 * 30)?>">
<input type="submit" name="notesubmit" value="<?=$lang['submit']?>"></td></tr>
</table><br>

<? if((($threadsmod || $postsmod) && $allowmodpost) || ($threadsdel && $adminid == 1)) { ?>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="0" width="85%" align="center" class="tableborder">
<tr><td><table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="2"><?=$lang['home_threads_posts']?></td></tr>
<?

$thisbg = ALTBG1;
if($allowmodpost) {
	if($threadsmod) {
		$thisbg = $thisbg == ALTBG2 ? ALTBG1 : ALTBG2;
		echo "<tr bgcolor=\"$thisbg\" style=\"font-weight: bold\"><td width=\"45%\"><a href=\"admincp.php?action=modthreads\">$lang[home_mod_threads]</a></td><td>$threadsmod</td></tr>\n";
	}
	if($postsmod) {
		$thisbg = $thisbg == ALTBG2 ? ALTBG1 : ALTBG2;
		echo "<tr bgcolor=\"$thisbg\" style=\"font-weight: bold\"><td width=\"45%\"><a href=\"admincp.php?action=modreplies\">$lang[home_mod_posts]</a></td><td>$postsmod</td></tr>\n";
	}
}
if($threadsdel && $adminid == 1) {
	$thisbg = $thisbg == ALTBG2 ? ALTBG1 : ALTBG2;
	echo "<tr bgcolor=\"$thisbg\" style=\"font-weight: bold\"><td width=\"45%\"><a href=\"admincp.php?action=recyclebin\">$lang[home_delete_threads]</td><td>$threadsdel</td></tr>\n";
}

?>

</table></td></tr></table><br>

<? } if($adminid == 1) { ?>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="0" width="85%" align="center" class="tableborder">
<tr><td><table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="2"><?=$lang['home_sys_info']?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td width="45%"><?=$lang['home_environment']?></td><td><?=$serverinfo?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_database']?></td><td><?=$dbversion?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_upload_perm']?></td><td><?=$fileupload?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_database_size']?></td><td><?=$dbsize?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_attach_size']?></td><td><?=$attachsize?></td></tr>
</table></td></tr></table><br>

<? } ?>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="0" width="85%" align="center" class="tableborder">
<tr><td><table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="2"><?=$lang['home_dev']?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td width="45%"><?=$lang['home_dev_copyright']?></td><td class="smalltxt"><a href="http://www.comsenz.com" target="_blank">&#x5317;&#x4EAC;&#x5EB7;&#x76DB;&#x4E16;&#x7EAA;&#x79D1;&#x6280;&#x6709;&#x9650;&#x516C;&#x53F8; (Comsenz Technology Ltd)</a></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_dev_manager']?></td><td class="smalltxt"><a href="http://discuz.net/viewpro.php?uid=1" target="_blank">&#x6234;&#x5FD7;&#x5EB7; (Kevin 'Crossday' Dell)</a></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_dev_team']?></td><td class="smalltxt"><a href="http://discuz.net/viewpro.php?uid=2691" target="_blank">Liang 'Readme' Chen</a>, <a href="http://discuz.net/viewpro.php?uid=69514" target="_blank">Jinhui 'Deepseath' Guo</a>, <a href="http://discuz.net/viewpro.php?uid=1519" target="_blank">Yang 'Summer' Xia</a>, <a href="http://discuz.net/viewpro.php?uid=141983" target="_blank">Jian 'Karlos' Han</a>, <a href="http://discuz.net/viewpro.php?uid=16678" target="_blank">Yang 'Tong Hu' Song</a></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_dev_addons']?></td><td class="smalltxt"><a href="http://discuz.net/viewpro.php?uid=9600" target="_blank">theoldmemory</a>, <a href="http://discuz.net/viewpro.php?uid=859" target="_blank">cnteacher</a>, <a href="http://discuz.net/viewpro.php?uid=2629" target="_blank">rain5017</a>, <a href="http://discuz.net/viewpro.php?uid=26926" target="_blank">Snow Wolf</a>, <a href="http://discuz.net/viewpro.php?uid=17149" target="_blank">hehechuan</a>, <a href="http://discuz.net/viewpro.php?uid=9132" target="_blank">pk0909</a>, <a href="http://discuz.net/viewpro.php?uid=248" target="_blank">feixin</a>, <a href="http://discuz.net/viewpro.php?uid=675" target="_blank">Laobing Jiuba</a></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_dev_skins']?></td><td class="smalltxt"><a href="http://discuz.net/viewpro.php?uid=13877" target="_blank">Artery</a>, <a href="http://discuz.net/viewpro.php?uid=233" target="_blank">Huli Hutu</a>, <a href="http://discuz.net/viewpro.php?uid=122" target="_blank">Lao Gui</a>, <a href="http://discuz.net/viewpro.php?uid=159" target="_blank">Tyc</a>, <a href="http://discuz.net/viewpro.php?uid=177" target="_blank">stoneage</a></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_dev_enterprise_site']?></td><td class="smalltxt"><a href="http://www.comsenz.com" target="_blank">http://www.Comsenz.com</a></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_dev_project_site']?></td><td class="smalltxt"><a href="http://www.discuz.com" target="_blank">http://www.Discuz.com</a></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_dev_community']?></td><td class="smalltxt"><a href="http://www.discuz.net" target="_blank">http://www.Discuz.net</a></td></tr>
</table></td></tr></table>
<?

if($adminid == 1) {
	$members = $db->result($db->query("SELECT COUNT(*) FROM {$tablepre}members"), 0);
	$threads = $db->result($db->query("SELECT COUNT(*) FROM {$tablepre}threads"), 0);
	$posts = $db->result($db->query("SELECT COUNT(*) FROM {$tablepre}posts"), 0);
	echo '<script language="JavaScript" src="http://customer.discuz.net/news.php?version='.rawurlencode(DISCUZ_VERSION).'&release='.rawurlencode(DISCUZ_RELEASE).'&php='.PHP_VERSION.'&mysql='.$dbversion.'&charset='.rawurlencode($charset).'&bbname='.rawurlencode($bbname).'&members='.$members.'&threads='.$threads.'&posts='.$posts.'&md5hash='.md5(preg_replace("/http:\/\/(.+?)\/.*/i", "\\1", $_SERVER['HTTP_REFERER']).$_SERVER['HTTP_USER_AGENT'].DISCUZ_VERSION.DISCUZ_RELEASE.$bbname.$members.$threads.$posts).'"></script>';
}

?>