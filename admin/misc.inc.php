<?php

/*
	[DISCUZ!] admin/misc.inc.php - misc functions of Admin CP
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-2-25 23:25
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'onlinelist') {

	if(!submitcheck('onlinesubmit')) {

		$listarray = array();
		$query = $db->query("SELECT * FROM {$tablepre}onlinelist");
		while($list = $db->fetch_array($query)) {
			$listarray[$list['groupid']] = $list;
		}

		$onlinelist = '';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups WHERE groupid<>'7' AND type<>'member'");
		$group = array('groupid' => 0, 'grouptitle' => 'Member');
		do {
			$onlinelist .= "<tr align=\"center\">\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"3\" name=\"displayordernew[$group[groupid]]\" value=\"{$listarray[$group[groupid]][displayorder]}\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\">".($group['groupid'] <= 8 ? $lang['usergroups_system_'.$group['groupid']] : $group['grouptitle'])."</td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"15\" name=\"titlenew[$group[groupid]]\" value=\"".($listarray[$group['groupid']]['title'] ? $listarray[$group['groupid']]['title'] : $group['grouptitle'])."\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"20\" name=\"urlnew[$group[groupid]]\" value=\"{$listarray[$group[groupid]][url]}\">\n".
				($listarray[$group['groupid']]['url'] ? "<img src=\"images/common/{$listarray[$group['groupid']]['url']}\">" : '')."</td></tr>\n";
		} while($group = $db->fetch_array($query));

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="75%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['onlinelist_tips']?>
</td></tr></table>

<br><form method="post"	action="admincp.php?action=onlinelist">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="75%" align="center" class="tableborder">
<tr align="center" class="header">
<td><?=$lang['display_order']?></td><td><?=$lang['usergroups_title']?></td><td><?=$lang['usergroups_title']?></td><td><?=$lang['onlinelist_image']?></td></tr>
<?=$onlinelist?></table><br>
<center><input type="submit" name="onlinesubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if(is_array($urlnew)) {
			$db->query("DELETE FROM {$tablepre}onlinelist");
			foreach($urlnew as $id => $url) {
				$url = trim($url);
				if($id == 0 || $url) {
					$db->query("INSERT INTO {$tablepre}onlinelist (groupid, displayorder, title, url)
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
		$query = $db->query("SELECT * FROM {$tablepre}forumlinks ORDER BY displayorder");
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
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forumlinks_tips']?>
</td></tr></table>

<br><form method="post"	action="admincp.php?action=forumlinks">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['forumlinks_edit']?></td></tr>
<tr align="center" class="category">
<td><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['display_order']?></td><td><?=$lang['forumlinks_edit_name']?></td><td><?=$lang['forumlinks_edit_url']?></td><td><?=$lang['forumlinks_edit_note']?></td>
<td><?=$lang['forumlinks_edit_logo']?></td></tr>
<?=$forumlinks?>
<tr><td colspan="6" class="singleborder">&nbsp;</td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="3"	name="newdisplayorder"></td>
<td><input type="text" size="15" name="newname"></td>
<td><input type="text" size="15" name="newurl"></td>
<td><input type="text" size="15" name="newnote"></td>
<td><input type="text" size="15" name="newlogo"></td>
</tr></table><br>
<center><input type="submit" name="forumlinksubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	{$tablepre}forumlinks WHERE	id IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id =>	$val) {
				$db->query("UPDATE {$tablepre}forumlinks SET displayorder='$displayorder[$id]', name='$name[$id]', url='$url[$id]',	note='$note[$id]', logo='$logo[$id]' WHERE id='$id'");
			}
		}

		if($newname != '') {
			$db->query("INSERT INTO	{$tablepre}forumlinks (displayorder, name, url, note, logo)	VALUES ('$newdisplayorder', '$newname',	'$newurl', '$newnote', '$newlogo')");
		}

		updatecache('forumlinks');
		cpmsg('forumlinks_succeed', 'admincp.php?action=forumlinks');

	}

} elseif($action == 'medals') {

	if(!submitcheck('medalsubmit')) {

		$medals = '';
		$query = $db->query("SELECT * FROM {$tablepre}medals");
		while($medal = $db->fetch_array($query)) {
			$checkavailable = $medal['available'] ? 'checked' : '';
			$medals .= "<tr bgcolor=\"".ALTBG2."\" align=\"center\">\n".
				"<td bgcolor=\"".ALTBG1."\" width=\"48\"><input type=\"checkbox\" name=\"delete[]\" value=\"$medal[medalid]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"30\" name=\"name[$medal[medalid]]\" value=\"$medal[name]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"available[$medal[medalid]]\" value=\"1\" $checkavailable></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"25\" name=\"image[$medal[medalid]]\" value=\"$medal[image]\">\n".
				"<img src=\"images/common/$medal[image]\"></td></tr>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['medals_tips']?>
</td></tr></table>

<br><form method="post"	action="admincp.php?action=medals">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['medals_edit']?></td></tr>
<tr align="center" class="category">
<td><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form, 'delete')"><?=$lang['del']?></td>
<td><?=$lang['name']?></td><td><?=$lang['available']?></td><td><?=$lang['medals_image']?></td></tr>
<?=$medals?>
<tr><td colspan="4" class="singleborder">&nbsp;</td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="30" name="newname"></td>
<td><input type="checkbox" name="availablenew" value="1"></td>
<td><input type="text" size="25" name="newimage"></td>
</tr></table><br>
<center><input type="submit" name="medalsubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	{$tablepre}medals WHERE medalid IN ($ids)");
		}

		if(is_array($name)) {
			foreach($name as $id =>	$val) {
				$db->query("UPDATE {$tablepre}medals SET name=".($name[$id] ? '\''.dhtmlspecialchars($name[$id]).'\'' : 'name').", available='$available[$id]', image=".($image[$id] ? '\''.$image[$id].'\'' : 'image')." WHERE medalid='$id'");
			}
		}

		if($newname != '' && $newimage != '') {
			$db->query("INSERT INTO	{$tablepre}medals (name, available, image) VALUES ('".dhtmlspecialchars($newname)."', '$newavailable', '$newimage')");
		}

		updatecache('medals');
		cpmsg('medals_succeed', 'admincp.php?action=medals');
	}

} elseif($action == 'discuzcodes') {

	if(!submitcheck('bbcodessubmit') && !$edit) {

		$discuzcodes = '';
		$query = $db->query("SELECT * FROM {$tablepre}bbcodes");
		while($bbcode = $db->fetch_array($query)) {
			$discuzcodes .= "<tr bgcolor=\"".ALTBG2."\" align=\"center\">\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$bbcode[id]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"15\" name=\"tagnew[$bbcode[id]]\" value=\"$bbcode[tag]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"availablenew[$bbcode[id]]\" value=\"1\" ".($bbcode['available'] ? 'checked' : NULL)."></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><a href=\"admincp.php?action=discuzcodes&edit=$bbcode[id]\">[$lang[detail]]</a></td></tr>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['discuzcodes_edit_tips']?>
</td></tr></table>

<br><form method="post"	action="admincp.php?action=discuzcodes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['discuzcodes_edit']?></td></tr>
<tr align="center" class="category">
<td width="48"><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td><?=$lang['discuzcodes_tag']?></td><td><?=$lang['available']?></td>
<td><?=$lang['edit']?></td></tr>
<?=$discuzcodes?>
<tr><td colspan="4" class="singleborder">&nbsp;</td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" size="15" name="newtag"></td>
<td colspan="2">&nbsp;</td>
</tr></table><br>
<center><input type="submit" name="bbcodessubmit" value="<?=$lang['submit']?>"></center></form></td></tr>
<?

	} elseif(submitcheck('bbcodessubmit')) {

		if(is_array($delete)) {
			$ids = '\''.implode('\',\'', $delete).'\'';
			$db->query("DELETE FROM	{$tablepre}bbcodes WHERE id IN ($ids)");
		}

		if(is_array($tagnew)) {
			foreach($tagnew as $id => $val) {
				if(!preg_match("/^[0-9a-z]+$/i", $tagnew[$id]) && strlen($tagnew[$id]) < 20) {
					cpmsg('discuzcodes_edit_tag_invalid');
				}
				$db->query("UPDATE {$tablepre}bbcodes SET tag='$tagnew[$id]', available='$availablenew[$id]' WHERE id='$id'");
			}
		}

		if($newtag != '') {
			if(!preg_match("/^[0-9a-z]+$/i", $newtag && strlen($newtag) < 20)) {
				cpmsg('discuzcodes_edit_tag_invalid');
			}
			$db->query("INSERT INTO	{$tablepre}bbcodes (tag, available, params, nest)
				VALUES ('$newtag', '0', '1', '1')");
		}

		updatecache('bbcodes');
		cpmsg('discuzcodes_edit_succeed', 'admincp.php?action=discuzcodes');

	} elseif($edit) {

		$query = $db->query("SELECT * FROM {$tablepre}bbcodes WHERE id='$edit'");
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

			$db->query("UPDATE {$tablepre}bbcodes SET tag='$tagnew', replacement='$replacementnew', example='$examplenew', explanation='$explanationnew', params='$paramsnew', nest='$nestnew' WHERE id='$edit'");

			updatecache('bbcodes');
			cpmsg('discuzcodes_edit_succeed', 'admincp.php?action=discuzcodes');

		}
	}

} elseif($action == 'censor') {

	if(!submitcheck('censorsubmit')) {

		$censorwords = '';
		$query = $db->query("SELECT * FROM {$tablepre}words");
		while($censor =	$db->fetch_array($query)) {
			$disabled = $adminid != 1 && $censor['admin'] != $discuz_userss ? 'disabled' : NULL;
			$censorwords .=	"<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$censor[id]\" $disabled></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"30\" name=\"find[$censor[id]]\" value=\"$censor[find]\" $disabled></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"30\" name=\"replace[$censor[id]]\" value=\"$censor[replacement]\" $disabled></td>\n".
				"<td bgcolor=\"".ALTBG2."\">$censor[admin]</td></tr>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['censor_tips']?>
</td></tr></table>

<br><form method="post"	action="admincp.php?action=censor">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">
<tr align="center" class="header"><td width="48"><input type="checkbox"	name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['censor_word']?></td><td><?=$lang['censor_replacement']?></td><td><?=$lang['operator']?></td></tr>
<?=$censorwords?>
<tr><td colspan="4" class="singleborder">&nbsp;</td></tr>
<tr bgcolor="<?=ALTBG1?>">
<td align="center"><?=$lang['add_new']?></td>
<td align="center"><input type="text" size="30"	name="newfind"></td>
<td align="center"><input type="text" size="30"	name="newreplace"></td>
<td>&nbsp;</td>
</tr></table><br>
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
			$db->query("DELETE FROM	{$tablepre}words WHERE id IN ($ids) AND ('$adminid'='1' OR admin='$discuz_user')");
		}

		if(is_array($find)) {
			foreach($find as $id =>	$val) {
				if($find[$id]) {
					$db->query("UPDATE {$tablepre}words SET find='$find[$id]', replacement='$replace[$id]' WHERE id='$id' AND ('$adminid'='1' OR admin='$discuz_user')");
				}
			}
		}

		if($newfind != '') {
			$db->query("INSERT INTO	{$tablepre}words (admin, find, replacement) VALUES
					('$discuz_user', '$newfind', '$newreplace')");
		}

		updatecache('censor');
		cpmsg('censor_succeed', 'admincp.php?action=censor');

	}

} elseif($action == 'smilies') {

	if(!submitcheck('smiliesubmit')) {

		$smilies = $icons = '';
		$query = $db->query("SELECT * FROM {$tablepre}smilies ORDER BY displayorder");
		while($smiley =	$db->fetch_array($query)) {
			if($smiley['type'] == 'smiley') {
				$smilies .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\"></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"25\" name=\"code[$smiley[id]]\" value=\"".dhtmlspecialchars($smiley['code'])."\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"25\" name=\"url[$smiley[id]]\" value=\"$smiley[url]\"></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"hidden\" name=\"type[$smiley[id]]\" value=\"$smiley[type]\"><img src=\"".SMDIR."/$smiley[url]\"></td></tr>\n";
			} elseif($smiley['type'] == 'icon') {
				$icons	.= "<tr	align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$smiley[id]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"2\" name=\"displayorder[$smiley[id]]\" value=\"$smiley[displayorder]\"></td>\n".
					"<td bgcolor=\"".ALTBG1."\" colspan=\"2\"><input type=\"text\" size=\"35\" name=\"url[$smiley[id]]\" value=\"$smiley[url]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"hidden\" name=\"type[$smiley[id]]\" value=\"$smiley[type]\"><img src=\"".SMDIR."/$smiley[url]\"></td></tr>\n";
			}
		}

?>
<form method="post" action="admincp.php?action=smilies">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">
<tr class="header"><td colspan="5" align="left"><?=$lang['smilies_edit']?></td></tr>
<tr align="center" class="category">
<td width="48"><?=$lang['del']?></td><td><?=$lang['display_order']?></td>
<td><?=$lang['smilies_edit_code']?></td><td><?=$lang['smilies_edit_filename']?></td><td><?=$lang['smilies_edit_image']?></td></tr>
<?=$smilies?>
<tr><td colspan="4" class="singleborder">&nbsp;</td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center"><td><?=$lang['add_new']?></td>
<td><input type="text" size="2" name="newdisplayorder1"></td>
<td><input type="text" size="25" name="newcode"></td>
<td><input type="text" size="25" name="newurl1"></td>
<td></td></tr><tr>
<td colspan="4" class="singleborder">&nbsp;</td></tr>
<tr><td	colspan="5" class="header"><?=$lang['smilies_edit_icon']?></td></tr>
<tr align="center" class="category">
<td width="48"><?=$lang['del']?></td><td><?=$lang['display_order']?></td>
<td colspan="2"><?=$lang['smilies_edit_filename']?></td><td><?=$lang['smilies_edit_image']?></td></tr>
<?=$icons?>
<tr><td colspan="4" class="singleborder">&nbsp;</td></tr>
<tr bgcolor="<?=ALTBG1?>" align="center">
<td><?=$lang['add_new']?></td>
<td><input type="text" name="newdisplayorder2" size="2"></td>
<td colspan="2"><input type="text" name="newurl2" size="35"></td><td>&nbsp;</td>
</tr></table><br>
<center><input type="submit" name="smiliesubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		if(is_array($delete)) {
			$ids = $comma =	'';
			foreach($delete	as $id)	{
				$ids .=	"$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM	{$tablepre}smilies WHERE id IN ($ids)");
		}

		if(is_array($url)) {
			foreach($url as	$id => $val) {
				$db->query("UPDATE {$tablepre}smilies SET displayorder='$displayorder[$id]', type='$type[$id]', code='$code[$id]', url='$url[$id]' WHERE id='$id'");
			}
		}

		if($newurl1 != '') {
			$query = $db->query("INSERT INTO {$tablepre}smilies (displayorder, type, code, url)
				VALUES ('$newdisplayorder1', 'smiley', '$newcode', '$newurl1')");
		}
		if($newurl2 != '') {
			$query = $db->query("INSERT INTO {$tablepre}smilies (displayorder, type, code, url)
				VALUES ('$newdisplayorder2', 'icon', '', '$newurl2')");
		}

		updatecache('smilies');
		updatecache('icons');
		cpmsg('smilies_succeed', 'admincp.php?action=smilies');

	}

} elseif($action == 'attachtypes') {

	if(!submitcheck('typesubmit')) {

		$attachtypes = '';
		$query = $db->query("SELECT * FROM {$tablepre}attachtypes");
		while($type = $db->fetch_array($query)) {
			$attachtypes .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$type[id]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"10\" name=\"extension[$type[id]]\" value=\"$type[extension]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"15\" name=\"maxsize[$type[id]]\" value=\"$type[maxsize]\"></td></tr>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['attachtypes_tips']?>
</td></tr></table>

<br><form method="post"	action="admincp.php?action=attachtypes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">
<tr align="center" class="header"><td width="48"><input type="checkbox"	name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['attachtypes_ext']?></td><td><?=$lang['attachtypes_maxsize']?></td></tr>
<?=$attachtypes?>
<tr><td colspan="3" class="singleborder">&nbsp;</td></tr>
<tr bgcolor="<?=ALTBG1?>">
<td align="center"><?=$lang['add_new']?></td>
<td align="center"><input type="text" size="10"	name="newextension"></td>
<td align="center"><input type="text" size="15"	name="newmaxsize"></td>
</tr></table><br>
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
			$db->query("DELETE FROM	{$tablepre}attachtypes WHERE id IN ($ids)");
		}

		if(is_array($extension)) {
			foreach($extension as $id => $val) {
				$db->query("UPDATE {$tablepre}attachtypes SET extension='$extension[$id]', maxsize='$maxsize[$id]' WHERE id='$id'");
			}
		}

		if($newextension != '') {
			$newextension = trim($newextension);
			$query = $db->query("SELECT id FROM {$tablepre}attachtypes WHERE extension='$newextension'");
			if($db->result($query, 0)) {
				cpmsg('attachtypes_duplicate');
			}
			$db->query("INSERT INTO	{$tablepre}attachtypes (extension, maxsize) VALUES
					('$newextension', '$newmaxsize')");
		}

		cpmsg('attachtypes_succeed', 'admincp.php?action=attachtypes');

	}

} elseif($action == 'creditslog') {

	$lpp = empty($lpp) ? 50 : $lpp;
	$page = !ispage($page) ? 1 : $page;
	$start_limit = ($page - 1) * $lpp;

	$keywordadd = !empty($keyword) ? "AND c.fromto LIKE '%$keyword%'" : '';

	$mpurl = "admincp.php?action=$action&keyword=".rawurlencode($keyword)."&lpp=$lpp";
	if(!empty($operations) && is_array($operations)) {
		$operationadd = "AND c.operation IN ('".implode('\',\'', $operations)."')";
		foreach($operations as $operation) {
			$mpurl .= '&operations[]='.rawurlencode($operation);
		}
	} else {
		$operationadd = '';
	}

	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}creditslog c WHERE 1 $keywordadd $operationadd");
	$num = $db->result($query, 0);

	$multipage = multi($num, $lpp, $page, $mpurl);

	$creditsoperations = '';
	foreach(array('TFR', 'RCV', 'EXC', 'UGP', 'AFD') as $operation) {
		$creditsoperations .= '<input type="checkbox" name="operations[]" value="'.$operation.'" '.(!empty($operations) && is_array($operations) && in_array($operation, $operations) ? 'checked' : '').'> '.$lang['logs_credit_operation_'.strtolower($operation)].' &nbsp; ';
	}

	$logs = '';
	$total['send'] = $total['receive'] = array();
	$query = $db->query("SELECT c.*, m.username FROM {$tablepre}creditslog c
		LEFT JOIN {$tablepre}members m USING (uid)
		WHERE 1 $keywordadd $operationadd ORDER BY dateline DESC LIMIT $start_limit, $lpp");

	while($log = $db->fetch_array($query)) {
		$total['send'][$log['sendcredits']] += $log['send'];
		$total['receive'][$log['receivecredits']] += $log['receive'];
		$log['dateline'] = gmdate('y-n-j H:i', $log['dateline'] + $timeoffset * 3600);
		$log['operation'] = $lang['logs_credit_operation_'.strtolower($log['operation'])];
		$logs .= "<tr align=\"center\"><td class=\"altbg1\"><a href=\"viewpro.php?username=".rawurlencode($log['username'])."\" target=\"_blank\">$log[username]</td>".
			"<td class=\"altbg2\">$log[fromto]</td>".
			"<td class=\"altbg1\">$log[dateline]</td>".
			"<td class=\"altbg2\">".(isset($extcredits[$log['sendcredits']]) ? $extcredits[$log['sendcredits']]['title'].' '.$log['send'].' '.$extcredits[$log['sendcredits']]['unit'] : $log['send'])."</td>".
			"<td class=\"altbg1\">".(isset($extcredits[$log['receivecredits']]) ? $extcredits[$log['receivecredits']]['title'].' '.$log['receive'].' '.$extcredits[$log['receivecredits']]['unit'] : $log['receive'])."</td>".
			"<td class=\"altbg2\">$log[operation]</td></tr>";
	}

	$result = array('send' => array(), 'receive' => array());
	foreach(array('send', 'receive') as $key) {
		foreach($total[$key] as $id => $amount) {
			if(isset($extcredits[$id])) {
				$result[$key][] = $extcredits[$id]['title'].' '.$amount.' '.$extcredits[$id]['unit'];
			}
		}
	}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="0" width="98%" align="center" class="tableborder">
<tr><td><table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="3"><?=$lang['logs_credit']?></td></tr>


<form method="post" action="admincp.php?action=creditslog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG2?>"><td width="25%"><?=$lang['logs_lpp']?></td>
<td width="55%"><input type="text" name="lpp" size="40" maxlength="40" value="<?=$lpp?>"></td>
<td width="20%"><input type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

<form method="post" action="admincp.php?action=creditslog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['logs_search']?></td><td><input type="text" name="keyword" size="40" value="<?=dhtmlspecialchars($keyword)?>"></td>
<td><input type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

<form method="post" action="admincp.php?action=creditslog">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG2?>"><td><?=$lang['action']?></td><td><?=$creditsoperations?></td>
<td><input type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

</table></td></tr></table><br><br>

<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header" align="center">
<td width="16%"><?=$lang['username']?></td>
<td width="16%"><?=$lang['logs_credit_fromto']?></td>
<td width="17%"><?=$lang['time']?></td>
<td width="16%"><?=$lang['logs_credit_send']?></td>
<td width="15%"><?=$lang['logs_credit_receive']?></td>
<td width="20%"><?=$lang['action']?></td>
</tr>
<?=$logs?>
<tr class="category" align="right"><td colspan="6"><b><?=$lang['logs_credit_send_total']?></b> <?=implode('; ', $result['receive'])?> <b>|</b> <b><?=$lang['logs_credit_receive_total']?></b> <?=implode(', ', $result['send'])?></td></tr>
</table>

<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>
<?

} elseif($action == 'logout') {

	$db->query("DELETE FROM {$tablepre}adminsessions WHERE uid='$discuz_uid' AND errorcount='-1'");
	cpmsg('logout_succeed');

}


?>