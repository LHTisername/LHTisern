<?php

/*
	[DISCUZ!] admin/misc.php - misc functions of Admin CP
	This is NOT a freeware, use is subject to license terms

	Version: 2.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2002/12/25 10:00
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

cpheader();

if($action == 'onlinelist') {

	if(!submitcheck('onlinesubmit')) {

		$listarray = array();
		$query = $db->query("SELECT * FROM $table_onlinelist");
		while($list = $db->fetch_array($query)) {
			$listarray[$list['groupid']] = $list;
		}

		$onlinelist = '';
		$query = $db->query("SELECT groupid, grouptitle FROM $table_usergroups WHERE groupid<>'7' AND type<>'member'");
		$group = array('groupid' => 0, 'grouptitle' => 'Member');
		do {
			$onlinelist .= "<tr align=\"center\">\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"3\" name=\"displayordernew[$group[groupid]]\" value=\"{$listarray[$group[groupid]][displayorder]}\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"15\" name=\"titlenew[$group[groupid]]\" value=\"".($listarray[$group['groupid']]['title'] ? $listarray[$group['groupid']]['title'] : $group['grouptitle'])."\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"20\" name=\"urlnew[$group[groupid]]\" value=\"{$listarray[$group[groupid]][url]}\"></td></tr>\n";
		} while($group = $db->fetch_array($query));

?>
<table cellspacing="0" cellpadding="0" border="0" width="70%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['onlinelist_tips']?>
</td></tr></table></td></tr></table>

<br><form method="post"	action="admincp.php?action=onlinelist">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="70%" align="center">
<tr><td	bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr align="center" class="header">
<td><?=$lang['display_order']?></td><td><?=$lang['usergroups_title']?></td><td><?=$lang['onlinelist_image']?></td></tr>
<?=$onlinelist?></table></td></tr></table><br>
<center><input type="submit" name="onlinesubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if(is_array($urlnew)) {
			$db->query("DELETE FROM $table_onlinelist");
			foreach($urlnew as $id => $url) {
				$url = trim($url);
				if($id == 0 || $url) {
					$db->query("INSERT INTO $table_onlinelist (groupid, displayorder, title, url)
						VALUES ('$id', '$displayordernew[$id]', '$titlenew[$id]', '$url')");
				}
			}
		}

		updatecache('onlinelist');
		cpmsg('onlinelist_succeed', 'admincp.php?action=onlinelist');

	}

} elseif($action == 'forumlinks') {

	if(!submitcheck('forumlinksubmit')) {

		$forumlinks = '';
		$query = $db->query("SELECT * FROM $table_forumlinks ORDER BY displayorder");
		while($forumlink = $db->fetch_array($query)) {
			$forumlinks .= "<tr bgcolor=\"".ALTBG2."\" align=\"center\">\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$forumlink[id]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"3\" name=\"displayorder[$forumlink[id]]\" value=\"$forumlink[displayorder]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"15\" name=\"name[$forumlink[id]]\" value=\"$forumlink[name]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"15\" name=\"url[$forumlink[id]]\" value=\"$forumlink[url]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"15\" name=\"note[$forumlink[id]]\" value=\"$forumlink[note]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"15\" name=\"logo[$forumlink[id]]\" value=\"$forumlink[logo]\"></td></tr>\n";
		}

?>
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forumlinks_tips']?>
</td></tr></table></td></tr></table>

<br><form method="post"	action="admincp.php?action=forumlinks">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td	bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="6"><?=$lang['forumlinks_edit']?></td></tr>
<tr align="center" class="category">
<td><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['display_order']?></td><td><?=$lang['forumlinks_edit_name']?></td><td><?=$lang['forumlinks_edit_url']?></td><td><?=$lang['forumlinks_edit_note']?></td>
<td><?=$lang['forumlinks_edit_logo']?></td></tr>
<?=$forumlinks?>
<tr bgcolor="<?=ALTBG2?>"><td colspan="6" height="1"></td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="3"	name="newdisplayorder"></td>
<td><input type="text" size="15" name="newname"></td>
<td><input type="text" size="15" name="newurl"></td>
<td><input type="text" size="15" name="newnote"></td>
<td><input type="text" size="15" name="newlogo"></td>
</tr></table></td></tr></table><br>
<center><input type="submit" name="forumlinksubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	$table_forumlinks WHERE	id IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id =>	$val) {
				$db->query("UPDATE $table_forumlinks SET displayorder='$displayorder[$id]', name='$name[$id]', url='$url[$id]',	note='$note[$id]', logo='$logo[$id]' WHERE id='$id'");
			}
		}

		if($newname != '') {
			$db->query("INSERT INTO	$table_forumlinks (displayorder, name, url, note, logo)	VALUES ('$newdisplayorder', '$newname',	'$newurl', '$newnote', '$newlogo')");
		}

		updatecache('forumlinks');
		cpmsg('forumlinks_succeed', 'admincp.php?action=forumlinks');

	}

} elseif($action == 'discuzcodes') {

	if(!submitcheck('bbcodessubmit') && !$edit) {

		$discuzcodes = '';
		$query = $db->query("SELECT * FROM $table_bbcodes");
		while($bbcode = $db->fetch_array($query)) {
			$discuzcodes .= "<tr bgcolor=\"".ALTBG2."\" align=\"center\">\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$bbcode[id]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"15\" name=\"tagnew[$bbcode[id]]\" value=\"$bbcode[tag]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"availablenew[$bbcode[id]]\" value=\"1\" ".($bbcode['available'] ? 'checked' : NULL)."></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><a href=\"admincp.php?action=discuzcodes&edit=$bbcode[id]\">[$lang[detail]]</a></td></tr>\n";
		}

?>
<br><form method="post"	action="admincp.php?action=discuzcodes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td	bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="6"><?=$lang['discuzcodes_edit']?></td></tr>
<tr align="center" class="category">
<td><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['discuzcodes_tag']?></td><td><?=$lang['available']?></td>
<td><?=$lang['edit']?></td></tr>
<?=$discuzcodes?>
<tr bgcolor="<?=ALTBG2?>"><td colspan="4" height="1"></td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="15" name="newtag"></td>
<td colspan="2">&nbsp;</td>
</tr></table></td></tr></table><br>
<center><input type="submit" name="bbcodessubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} elseif(submitcheck('bbcodessubmit')) {

		if(is_array($delete)) {
			$ids = '\''.implode('\',\'', $delete).'\'';
			$db->query("DELETE FROM	$table_bbcodes WHERE id IN ($ids)");
		}

		if(is_array($tagnew)) {
			foreach($tagnew as $id => $val) {
				if(!preg_match("/^[0-9a-z]+$/i", $tagnew[$id])) {
					cpmsg('discuzcodes_edit_tag_invalid');
				}
				$db->query("UPDATE $table_bbcodes SET tag='$tagnew[$id]', available='$availablenew[$id]' WHERE id='$id'");
			}
		}

		if($newtag != '') {
			$db->query("INSERT INTO	$table_bbcodes (tag, available, params, nest)
				VALUES ('$newtag', '0', '1', '1')");
		}

		updatecache('bbcodes');
		cpmsg('discuzcodes_edit_succeed', 'admincp.php?action=discuzcodes');

	} elseif($edit) {

		$query = $db->query("SELECT * FROM $table_bbcodes WHERE id='$edit'");
		if(!$bbcode = $db->fetch_array($query)) {
			cpmsg('undefined_action');
		}

		if(!submitcheck('editsubmit')) {

			echo "<form method=\"post\" action=\"admincp.php?action=discuzcodes&edit=$edit&formhash=".FORMHASH."\">\n";

			showtype($lang['discuzcodes_edit'].' - '.$bbcode['tag'], 'top');
			showsetting('discuzcodes_edit_tag', 'tagnew', $bbcode['tag'], 'text');
			showsetting('discuzcodes_edit_replacement', 'replacementnew', $bbcode['replacement'], 'textarea');
			showsetting('discuzcodes_edit_example', 'examplenew', $bbcode['example'], 'text');
			showsetting('discuzcodes_edit_explanation', 'explanationnew', $bbcode['explanation'], 'text');
			showsetting('discuzcodes_edit_params', 'paramsnew', $bbcode['params'], 'text');
			showsetting('discuzcodes_edit_nest', 'nestnew', $bbcode['nest'], 'text');
			showtype('', 'bottom');

			echo "<br><center><input type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

		} else {

			$tagnew = trim($tagnew);
			if(!preg_match("/^[0-9a-z]+$/i", $tagnew)) {
				cpmsg('discuzcodes_edit_tag_invalid');
			} elseif($paramsnew < 1 || $paramsnew > 3 || $nestnew < 1 || $nestnew > 3) {
				cpmsg('discuzcodes_edit_range_invalid');
			}

			$db->query("UPDATE $table_bbcodes SET tag='$tagnew', replacement='$replacementnew', example='$examplenew', explanation='$explanationnew', params='$paramsnew', nest='$nestnew' WHERE id='$edit'");

			updatecache('bbcodes');
			cpmsg('discuzcodes_edit_succeed', 'admincp.php?action=discuzcodes');

		}
	}

} elseif($action == 'censor') {

	if(!submitcheck('censorsubmit')) {

		$censorwords = '';
		$query = $db->query("SELECT * FROM $table_words");
		while($censor =	$db->fetch_array($query)) {
			$disabled = $adminid != 1 && $censor['admin'] != $discuz_userss ? 'disabled' : NULL;
			$censorwords .=	"<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$censor[id]\" $disabled></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"30\" name=\"find[$censor[id]]\" value=\"$censor[find]\" $disabled></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"30\" name=\"replace[$censor[id]]\" value=\"$censor[replacement]\" $disabled></td>\n".
				"<td bgcolor=\"".ALTBG2."\">$censor[admin]</td></tr>\n";
		}

?>
<table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['censor_tips']?>
</td></tr></table></td></tr></table>

<br><form method="post"	action="admincp.php?action=censor">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
<tr><td	bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr align="center" class="header"><td width="45"><input	type="checkbox"	name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['censor_word']?></td><td><?=$lang['censor_replacement']?></td><td><?=$lang['operator']?></td></tr>
<?=$censorwords?>
<tr bgcolor="<?=ALTBG2?>"><td colspan="4" height="1"></td></tr>
<tr bgcolor="<?=ALTBG1?>">
<td align="center"><?=$lang['add_new']?></td>
<td align="center"><input type="text" size="30"	name="newfind"></td>
<td align="center"><input type="text" size="30"	name="newreplace"></td>
<td>&nbsp;</td>
</tr></table></td></tr></table><br>
<center><input type="submit" name="censorsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	$table_words WHERE id IN ($ids) AND ('$adminid'='1' OR admin='$discuz_user')");
		}

		if(is_array($find)) {
			foreach($find as $id =>	$val) {
				$db->query("UPDATE $table_words	SET find='$find[$id]', replacement='$replace[$id]' WHERE id='$id' AND ('$adminid'='1' OR admin='$discuz_user')");
			}
		}

		if($newfind != '') {
			$db->query("INSERT INTO	$table_words (admin, find, replacement) VALUES
					('$discuz_user', '$newfind', '$newreplace')");
		}

		updatecache('censor');
		cpmsg('censor_succeed', 'admincp.php?action=censor');

	}

} elseif($action == 'smilies') {

	if(!submitcheck('smiliesubmit')) {

		$smilies = $icons = '';
		$query = $db->query("SELECT * FROM $table_smilies");
		while($smiley =	$db->fetch_array($query)) {
			if($smiley['type'] == 'smiley') {
				$smilies .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"25\" name=\"code[$smiley[id]]\" value=\"$smiley[code]\"></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"25\" name=\"url[$smiley[id]]\" value=\"$smiley[url]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"hidden\" name=\"type[$smiley[id]]\" value=\"$smiley[type]\"><img src=\"./".SMDIR."/$smiley[url]\"></td></tr>\n";
			} elseif($smiley['type'] == 'icon') {
				$icons	.= "<tr	align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\"></td>\n".
					"<td colspan=\"2\" bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"35\" name=\"url[$smiley[id]]\" value=\"$smiley[url]\"></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"hidden\" name=\"type[$smiley[id]]\" value=\"$smiley[type]\"><img src=\"./".SMDIR."/$smiley[url]\"></td></tr>\n";
			}
		}

?>
<form method="post" action="admincp.php?action=smilies">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
<tr><td	bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="4" align="left"><?=$lang['smilies_edit']?></td></tr>
<tr align="center" class="category">
<td width="45"><?=$lang['del']?></td>
<td><?=$lang['smilies_edit_code']?></td><td><?=$lang['smilies_edit_filename']?></td><td><?=$lang['smilies_edit_image']?></td></tr>
<?=$smilies?>
<tr><td	bgcolor="<?=ALTBG2?>" colspan="4" height="1"></td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center"><td><?=$lang['add_new']?></td>
<td><input type="text" size="25" name="newcode"></td>
<td><input type="text" size="25" name="newurl1"></td>
<td></td></tr><tr>
<td bgcolor="<?=ALTBG2?>" colspan="4" height="1"></td></tr>
<tr><td	colspan="4" class="header"><?=$lang['smilies_edit_icon']?></td></tr>
<tr align="center" class="category">
<td width="45"><?=$lang['del']?></td>
<td colspan="2"><?=$lang['smilies_edit_filename']?></td><td><?=$lang['smilies_edit_image']?></td></tr>
<?=$icons?>
<tr><td	bgcolor="<?=ALTBG2?>" colspan="4" height="1"></td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center">
<td><?=$lang['add_new']?></td><td colspan="2"><input type="text" name="newurl2" size="35"></td><td>&nbsp;</td>
</tr></table></td></tr></table><br>
<center><input type="submit" name="smiliesubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	$table_smilies WHERE id	IN ($ids)");
		}

		if(is_array($url)) {
			foreach($url as	$id => $val) {
				$db->query("UPDATE $table_smilies SET type='$type[$id]', code='$code[$id]', url='$url[$id]' WHERE id='$id'");
			}
		}

		if($newcode != '') {
			$query = $db->query("INSERT INTO $table_smilies	(type, code, url)
				VALUES ('smiley', '$newcode', '$newurl1')");
		}
		if($newurl2 != '') {
			$query = $db->query("INSERT INTO $table_smilies	(type, code, url)
				VALUES ('icon', '', '$newurl2')");
		}

		updatecache('smilies');
		updatecache('icons');
		cpmsg('smilies_succeed', 'admincp.php?action=smilies');

	}

} elseif($action == 'attachtypes') {

	if(!submitcheck('typesubmit')) {

		$attachtypes = '';
		$query = $db->query("SELECT * FROM $table_attachtypes");
		while($type = $db->fetch_array($query)) {
			$attachtypes .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$type[id]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"10\" name=\"extension[$type[id]]\" value=\"$type[extension]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"15\" name=\"maxsize[$type[id]]\" value=\"$type[maxsize]\"></td></tr>\n";
		}

?>
<table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['attachtypes_tips']?>
</td></tr></table></td></tr></table>

<br><form method="post"	action="admincp.php?action=attachtypes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
<tr><td	bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr align="center" class="header"><td width="45"><input	type="checkbox"	name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['attachtypes_ext']?></td><td><?=$lang['attachtypes_maxsize']?></td></tr>
<?=$attachtypes?>
<tr bgcolor="<?=ALTBG2?>"><td colspan="3" height="1"></td></tr>
<tr bgcolor="<?=ALTBG1?>">
<td align="center"><?=$lang['add_new']?></td>
<td align="center"><input type="text" size="10"	name="newextension"></td>
<td align="center"><input type="text" size="15"	name="newmaxsize"></td>
</tr></table></td></tr></table><br>
<center><input type="submit" name="typesubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	$table_attachtypes WHERE id IN ($ids)");
		}

		if(is_array($find)) {
			foreach($find as $id =>	$val) {
				$db->query("UPDATE $table_attachtypes SET extension='$extension[$id]', maxsize='$maxsize[$id]' WHERE id='$id'");
			}
		}

		if($newextension != '') {
			$newextension = trim($newextension);
			$query = $db->query("SELECT id FROM $table_attachtypes WHERE extension='$newextension'");
			if($db->result($query, 0)) {
				cpmsg('attachtypes_duplicate');
			}
			$db->query("INSERT INTO	$table_attachtypes (extension, maxsize) VALUES
					('$newextension', '$newmaxsize')");
		}

		cpmsg('attachtypes_succeed', 'admincp.php?action=attachtypes');

	}

} elseif($action == 'updatecache') {

	updatecache();

	$tpl = dir(DISCUZ_ROOT.'./forumdata/templates');
	while($entry = $tpl->read()) {
		if (strpos($entry, '.tpl.php')) {
			@unlink(DISCUZ_ROOT.'./forumdata/templates/'.$entry);
		}
	}
	$tpl->close();

	$db->query("DELETE FROM $table_searchindex");

	cpmsg('update_cache_succeed');

}elseif($action == 'logout') {

	session_unregister('admin_user');
	session_unregister('admin_pw');
	cpmsg('logout_succeed');

}


?>