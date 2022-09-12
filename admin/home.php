<?php

/*
	[DISCUZ!] admin/main.php - homepage of administrators' control panel
	This is NOT a freeware, use is subject to license terms

	Version: 2.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2003/3/10 9:35
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

if(file_exists(DISCUZ_ROOT.'./install.php')) {
	@unlink(DISCUZ_ROOT.'./install.php');
	if(file_exists(DISCUZ_ROOT.'./install.php')) {
		dexit('Please delete install.php via FTP!');
	}
}

require DISCUZ_ROOT.'./include/attachment.php';

if($adminid == 1) {

	$serverinfo = PHP_OS.' / PHP v'.PHP_VERSION;
	$serverinfo .= @ini_get('safe_mode') ? ' Safe Mode' : NULL;
	$dbversion = $db->result($db->query("SELECT VERSION()"), 0);

	if(@ini_get('file_uploads')) {
		$fileupload = $lang['yes'].': file '.ini_get('upload_max_filesize').' - form '.ini_get('post_max_size');
	} else {
		$fileupload = '<font color="red">'.$lang['no'].'</font>';
	}

	$forumselect = $groupselect = '';
	$query = $db->query("SELECT groupid, grouptitle FROM $table_usergroups ORDER BY creditslower, groupid");
	while($group = $db->fetch_array($query)) {
		$groupselect .= '<option value="'.$group['groupid'].'">'.$group['grouptitle'].'</option>';
	}
	$query = $db->query("SELECT fid, name FROM $table_forums WHERE type='forum' OR type='sub'");
	while($forum = $db->fetch_array($query)) {
		$forumselect .= '<option value="'.$forum['fid'].'">'.$forum['name'].'</option>';
	}

	$dbsize = 0;
	$query = $db->query("SHOW TABLE STATUS LIKE '$tablepre%'", 'SILENT');
	while($table = $db->fetch_array($query)) {
		$dbsize += $table['Data_length'] + $table['Index_length'];
	}
	$dbsize = $dbsize ? sizecount($dbsize) : $lang['unknown'];

	$attachsize = dirsize(DISCUZ_ROOT.$attachdir);
	$attachsize = is_numeric($attachsize) ? sizecount($attachsize) : $lang['unknown'];

}

cpheader();

?>
<font class="mediumtxt">
<b><?=$lang['welcome_to']?> <a href="http://www.Discuz.net" target="_blank">Discuz! <?=$version?></a> Administrators' Control Panel</b><br>
Copyright&copy; <a href="http://www.crossday.com" target="_blank">Crossday Studio</a>, 2002, 2003.

<br><br><br><table cellspacing="0" cellpadding="0" border="0" width="85%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="0" width="100%">
<tr><td>
<table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="3"><?=$lang['home_stuff']?></td></tr>

<form method="get" action="http://www.php.net/manual-lookup.php" target="_blank"><tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_php_lookup']?></td>
<td><input type="text" size="30" name="function"></td><td><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<? if($adminid == 1) { ?>

<form method="post" action="admincp.php?action=members">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_edit_member']?></td>
<td><input type="text" size="30" name="username"></td><td><input type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="post" action="admincp.php?action=export&type=mini&saveto=server">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_data_export']?></td>
<td><input type="text" size="30" name="filename" value="./forumdata/dz_<?=date("md")."_".random(5)?>.sql"></td><td><input type="submit" name="exportsubmit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="post" action="admincp.php?action=forumdetail">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_edit_forum']?></td>
<td><select name="fid"><?=$forumselect?></select></td><td><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="post" action="admincp.php?action=usergroups">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_edit_group']?></td>
<td><select name="edit"><?=$groupselect?></td><td><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<? } ?>

</table></td></tr></table></td></tr></table><br><br>

<? if($adminid == 1) { ?>

<table cellspacing="0" cellpadding="0" border="0" width="85%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="0" width="100%">
<tr><td>
<table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="2"><?=$lang['home_sys_info']?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td width="50%"><?=$lang['home_environment']?></td><td><?=$serverinfo?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_database']?></td><td><?=$dbversion?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_upload_perm']?></td><td><?=$fileupload?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_database_size']?></td><td><?=$dbsize?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_attach_size']?></td><td><?=$attachsize?></td></tr>
</table></td></tr></table></td></tr></table><br><br>

<? } ?>

<table cellspacing="0" cellpadding="0" border="0" width="85%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="0" width="100%">
<tr><td>
<table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="2"><?=$lang['home_dev']?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td width="50%"><?=$lang['home_dev_chief']?></td><td><a href="http://discuz.net/viewpro.php?uid=1" target="_blank">Kevin (Crossday) Dell</a></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_dev_market']?></td><td><a href="http://www.bokavan.com" target="_blank">Bokavan Software Co., Ltd, Beijing, China</a></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_dev_addons']?></td><td><a href="http://discuz.net/viewpro.php?uid=675" target="_blank">Laobing Jiuba</a>, <a href="http://discuz.net/viewpro.php?uid=2629" target="_blank">rain5017</a>, <a href="http://discuz.net/viewpro.php?uid=248" target="_blank">feixin</a>, <a href="http://discuz.net/viewpro.php?uid=2865" target="_blank">cknuke</a>, <a href="http://discuz.net/viewpro.php?uid=9600" target="_blank">theoldmemory</a></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_dev_skins']?></td><td><a href="http://discuz.net/viewpro.php?uid=122" target="_blank">Lao Gui</a>, <a href="http://discuz.net/viewpro.php?uid=159" target="_blank">Tyc</a>, <a href="http://discuz.net/viewpro.php?uid=177" target="_blank">stoneage</a>, <a href="http://discuz.net/viewpro.php?uid=233" target="_blank">Huli Hutu</a></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['home_dev_site']?></td><td><a href="http://www.discuz.com" target="_blank">http://www.Discuz.com</a></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['home_dev_community']?></td><td><a href="http://www.discuz.net" target="_blank">http://www.Discuz.net</a></td></tr>
</table></td></tr></table></td></tr></table>