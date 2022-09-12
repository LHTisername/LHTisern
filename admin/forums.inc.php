<?php

/*
	[DISCUZ!] admin/forums.inc.php - modifing forums settings of Discuz! admincp
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-4-12 15:16
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'forumadd')  {

	if((!submitcheck('catsubmit') && !submitcheck('forumsubmit'))) {
		$groupselect = $forumselect = "<select name=\"fup\">\n<option value=\"0\" selected=\"selected\"> - $lang[none] - </option>\n";
		$query = $db->query("SELECT fid, name, type FROM {$tablepre}forums WHERE type<>'sub' ORDER BY displayorder");
		while($fup = $db->fetch_array($query)) {
			if($fup['type'] == 'group') {
				$groupselect .= "<option value=\"$fup[fid]\">$fup[name]</option>\n";
			} else {
				$forumselect .= "<option value=\"$fup[fid]\">$fup[name]</option>\n";
			}
		}
		$groupselect .= '</select>';
		$forumselect .= '</select>';

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forums_add_tips']?>
</td></tr></table>

<br><form method="post" action="admincp.php?action=forumadd&add=category">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['forums_add_category']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['name']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="70%"><input type="text" name="newcat" value="Name of New Category" size="40"></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><input type="submit" name="catsubmit" value="<?=$lang['submit']?>"></td></tr>
</table></form>

<form method="post" action="admincp.php?action=forumadd&add=forum">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['forums_add_forum']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['name']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="28%"><input type="text" name="newforum" value="Name of New Forum" size="20"></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['forums_add_parent_category']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="27%"><?=$groupselect?></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><input type="submit" name="forumsubmit" value="<?=$lang['submit']?>"></td></tr>
</table></form>

<form method="post" action="admincp.php?action=forumadd&add=forum">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['forums_add_sub']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['name']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="28%"><input type="text" name="newforum" value="Name of New Sub Forum" size="20"></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['forums_add_parent_forum']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="27%"><?=$forumselect?></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><input type="submit" name="forumsubmit" value="<?=$lang['submit']?>"></td></tr>
</table></form><br>
<?

	} elseif(submitcheck('catsubmit')) {

		$db->query("INSERT INTO {$tablepre}forums (type, name, status)
			VALUES ('group', '$newcat', '1')");
		$fid = $db->insert_id();

		$db->query("INSERT INTO {$tablepre}forumfields (fid)
			VALUES ('$fid')");

		updatecache('forums');
		cpmsg('forums_add_category_succeed', 'admincp.php?action=forumsedit');

	} elseif(submitcheck('forumsubmit')) {

		$modarray = array();
		$query = $db->query("SELECT fup, type, inheritedmod FROM {$tablepre}forums WHERE fid='$fup'");
		if(!$forum = $db->fetch_array($query)) {
			$fup = 0;
		}

		$type = $forum['type'] == 'forum' ? 'sub' : 'forum';
		$db->query("INSERT INTO {$tablepre}forums (fup, type, name, status, allowsmilies, allowbbcode, allowimgcode, allowblog, allowtrade)
			VALUES ('$fup', '$type', '$newforum', '1', '1', '1', '1', '1', '3')");
		$fid = $db->insert_id();

		$db->query("INSERT INTO {$tablepre}forumfields (fid)
			VALUES ('$fid')");

		$query = $db->query("SELECT uid, inherited FROM {$tablepre}moderators WHERE fid='$fup'");
		while($mod = $db->fetch_array($query)) {
			if($mod['inherited'] || $forum['inheritedmod']) {
				$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, inherited)
					VALUES ('$mod[uid]', '$fid', '1')");
			}
		}

		updatecache('forums');
		cpmsg('forums_add_forum_succeed', 'admincp.php?action=forumsedit');

	}

} elseif($action == 'forumsedit') {

	if(!submitcheck('editsubmit')) {

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forums_tips']?>
</td></tr></table><br><br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['forums_edit']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>"><br>
<form method="post" action="admincp.php?action=forumsedit">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		$forums = $showedforums = array();
		$query = $db->query("SELECT f.fid, f.type, f.status, f.name, f.fup, f.displayorder, f.inheritedmod, ff.moderators
			FROM {$tablepre}forums f LEFT JOIN {$tablepre}forumfields ff USING(fid)
			ORDER BY f.type<>'group', f.displayorder");

		while($forum = $db->fetch_array($query)) {
			$forums[] = $forum;
		}

		for($i = 0; $i < count($forums); $i++) {
			if($forums[$i]['type'] == 'group') {
				echo '<ul>'.showforum($i, 'group');
				for($j = 0; $j < count($forums); $j++) {
					if($forums[$j]['fup'] == $forums[$i]['fid'] && $forums[$j]['type'] == 'forum') {
						echo '<ul>'.showforum($j);
						for($k = 0; $k < count($forums); $k++) {
							if($forums[$k]['fup'] == $forums[$j]['fid'] && $forums[$k]['type'] == 'sub') {
								echo '<ul>'.showforum($k, 'sub').'</ul>';
							}
						}
						echo '</ul>';
					}
				}
				echo '</ul>';
			} elseif(!$forums[$i]['fup'] && $forums[$i]['type'] == 'forum') {
				echo '<ul>'.showforum($i);
				for($j = 0; $j < count($forums); $j++) {
					if($forums[$j]['fup'] == $forums[$i]['fid'] && $forums[$j]['type'] == 'sub') {
						echo '<ul>'.showforum($j, 'sub').'</ul>';
					}
				}
				echo '</ul>';
			}
		}

		foreach($forums as $key => $forum) {
			if(!in_array($key, $showedforums)) {
				$db->query("UPDATE {$tablepre}forums SET fup='0', type='forum' WHERE fid='$forum[fid]'");
				echo '<ul>'.showforum($key).'</ul>';
			}
		}

		echo "<br><center><input type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center><br></td></tr></table>\n";

	} else {

		// read from groups
		$usergroups = array();
		$query = $db->query("SELECT groupid, type, creditshigher, creditslower FROM {$tablepre}usergroups");
		while($group = $db->fetch_array($query)) {
			$usergroups[$group['groupid']] = $group;
		}

		if(is_array($order)) {
			foreach($order as $fid => $value) {
				$db->query("UPDATE {$tablepre}forums SET displayorder='$order[$fid]' WHERE fid='$fid'");
			}
		}

		updatecache('forums');

		cpmsg('forums_update_succeed', 'admincp.php?action=forumsedit');
	}

} elseif($action == 'moderators' && $fid) {

	if(!submitcheck('modsubmit')) {

		$moderators = '';
		$query = $db->query("SELECT m.username, mo.* FROM {$tablepre}members m, {$tablepre}moderators mo WHERE mo.fid='$fid' AND m.uid=mo.uid ORDER BY mo.inherited, mo.displayorder");
		while($mod = $db->fetch_array($query)) {

			$moderators .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$mod[uid]\" ".($mod['inherited'] ? 'disabled' : '').">\n".
				"<td bgcolor=\"".ALTBG2."\"><a href=\"viewpro.php?uid=$mod[uid]\" target=\"_blank\">$mod[username]</a></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" name=\"displayordernew[$mod[uid]]\" value=\"$mod[displayorder]\" size=\"2\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\">".($mod['inherited'] ? '<b>'.$lang['yes'].'</b>' : $lang['no'])."</td></tr>\n";
		}

		if($forum['type'] == 'group' || $forum['type'] == 'sub') {
			$checked = $forum['type'] == 'group' ? 'checked' : '';
			$disabled = 'disabled';
		} else {
			$checked = $forum['inheritedmod'] ? 'checked' : '';
			$disabled = '';
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forums_moderators_tips']?>
</td></tr></table>

<form method="post" action="admincp.php?action=moderators&fid=<?=$fid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">
<tr class="header"><td colspan="4"><?=$lang['forums_moderators_edit']?> - <?=$forum['name']?></td></tr>
<tr align="center" class="category"><td><?=$lang['del']?></td><td><?=$lang['username']?></td><td><?=$lang['display_order']?></td><td><?=$lang['forums_moderators_inherited']?></td></tr>
<?=$moderators?>
<tr><td colspan="4" class="singleborder">&nbsp;</td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>"><?=$lang['add_new']?></td><td bgcolor="<?=ALTBG2?>"><input type='text' name="newmoderator" size="20"></td><td bgcolor="<?=ALTBG1?>"><input type="text" name="newdisplayorder" size="2" value="0"></td><td bgcolor="<?=ALTBG2?>">&nbsp;</td></tr>
<tr><td colspan="4" class="singleborder">&nbsp;</td></tr>
<tr><td colspan="4" bgcolor="<?=ALTBG2?>"><input type="checkbox" name="inheritedmodnew" value="1" <?=$checked?> <?=$disabled?>> <?=$lang['forums_moderators_inherit']?></td></tr>
</table><br>
<center><input type="submit" name="modsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if($forum['type'] == 'group') {
			$inheritedmodnew = 1;
		} elseif($forum['type'] == 'sub') {
			$inheritedmodnew = 0;
		}

		if(!empty($delete) || $newmoderator || (bool)$forum['inheritedmod'] != (bool)$inheritedmodnew) {

			$fidarray = $newmodarray = $origmodarray = array();

			if($forum['type'] == 'group') {
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE type='forum' AND fup='$fid'");
				while($sub = $db->fetch_array($query)) {
					$fidarray[] = $sub['fid'];
				}
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE type='sub' AND fup IN ('".implode('\',\'', $fidarray)."')");
				while($sub = $db->fetch_array($query)) {
					$fidarray[] = $sub['fid'];
				}
			} elseif($forum['type'] == 'forum') {
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE type='sub' AND fup='$fid'");
				while($sub = $db->fetch_array($query)) {
					$fidarray[] = $sub['fid'];
				}
			}

			if(is_array($delete)) {
				foreach($delete as $uid) {
					$db->query("DELETE FROM {$tablepre}moderators WHERE uid='$uid' AND ((fid='$fid' AND inherited='0') OR (fid IN ('".implode('\',\'', $fidarray)."') AND inherited='1'))");
				}

				$excludeuids = 0;
				$deleteuids = '\''.implode('\',\'', $delete).'\'';
				$query = $db->query("SELECT uid FROM {$tablepre}moderators WHERE uid IN ($deleteuids)");
				while($mod = $db->fetch_array($query)) {
					$excludeuids .= ','.$mod['uid'];
				}

				$usergroups = array();
				$query = $db->query("SELECT groupid, type, radminid, creditshigher, creditslower FROM {$tablepre}usergroups");
				while($group = $db->fetch_array($query)) {
					$usergroups[$group['groupid']] = $group;
				}

				$query = $db->query("SELECT uid, groupid, credits FROM {$tablepre}members WHERE uid IN ($deleteuids) AND uid NOT IN ($excludeuids) AND adminid NOT IN (1,2)");
				while($member = $db->fetch_array($query)) {
					if($usergroups[$member['groupid']]['type'] == 'special' && $usergroups[$member['groupid']]['radminid'] != 3) {
						$adminidnew = -1;
						$groupidnew = $member['groupid'];
					} else {
						$adminidnew = 0;
						foreach($usergroups as $group) {
							if($group['type'] == 'member' && $member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower']) {
								$groupidnew = $group['groupid'];
								break;
							}
						}
					}
					$db->query("UPDATE {$tablepre}members SET adminid='$adminidnew', groupid='$groupidnew' WHERE uid='$member[uid]'");
				}
			}

			if((bool)$forum['inheritedmod'] != (bool)$inheritedmodnew) {
				$query = $db->query("SELECT uid FROM {$tablepre}moderators WHERE fid='$fid' AND inherited='0'");
				while($mod = $db->fetch_array($query)) {
					$origmodarray[] = $mod['uid'];
					if(!$forum['inheritedmod'] && $inheritedmodnew) {
						$newmodarray[] = $mod['uid'];
					}
				}
				if($forum['inheritedmod'] && !$inheritedmodnew) {
					$db->query("DELETE FROM {$tablepre}moderators WHERE uid IN ('".implode('\',\'', $origmodarray)."') AND fid IN ('".implode('\',\'', $fidarray)."') AND inherited='1'");
				}
			}
			
			if($newmoderator) {
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$newmoderator'");
				if(!$member = $db->fetch_array($query)) {
					cpmsg('members_edit_nonexistence');
				} else {
					$newmodarray[] = $member['uid'];
					$db->query("UPDATE {$tablepre}members SET groupid='3' WHERE uid='$member[uid]' AND adminid NOT IN (1,2,3,4,5,6,7,8,-1)");
					$db->query("UPDATE {$tablepre}members SET adminid='3' WHERE uid='$member[uid]' AND adminid NOT IN (1,2)");
					$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, displayorder, inherited)
						VALUES ('$member[uid]', '$fid', '$newdisplayorder', '0')");
				}
			}

			foreach($newmodarray as $uid) {
				$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, inherited)
					VALUES ('$uid', '$fid', '0')");

				if($inheritedmodnew) {
					foreach($fidarray as $ifid) {
						$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, inherited)
							VALUES ('$uid', '$ifid', '1')");
					}
				}
			}

			if($forum['type'] == 'group') {
				$inheritedmodnew = 1;
			} elseif($forum['type'] == 'sub') {
				$inheritedmodnew = 0;
			}
			$db->query("UPDATE {$tablepre}forums SET inheritedmod='$inheritedmodnew' WHERE fid='$fid'");

		}

		if(is_array($displayordernew)) {
			foreach($displayordernew as $uid => $order) {
				$db->query("UPDATE {$tablepre}moderators SET displayorder='$order' WHERE fid='$fid' AND uid='$uid'");
			}
		}

		$moderators = $tab = '';
		$query = $db->query("SELECT m.username FROM {$tablepre}members m, {$tablepre}moderators mo WHERE mo.fid='$fid' AND mo.inherited='0' AND m.uid=mo.uid ORDER BY mo.displayorder");
		while($mod = $db->fetch_array($query)) {
			$moderators .= $tab.addslashes($mod['username']);
			$tab = "\t";
		}
		$db->query("UPDATE {$tablepre}forumfields SET moderators='$moderators' WHERE fid='$fid'");

		cpmsg('forums_moderators_update_succeed', "admincp.php?action=moderators&fid=$fid");

	}

} elseif($action == 'forumsmerge') {

	if(!submitcheck('mergesubmit') || $source == $target) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';
		require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

		$forumselect = "<select name=\"%s\">\n<option value=\"\">&nbsp;&nbsp;> $lang[select]</option><option value=\"\">&nbsp;</option>".str_replace('%', '%%', forumselect()).'</select>';

?>
<br><br><br><br><br>
<form method="post" action="admincp.php?action=forumsmerge">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['forums_merge']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="40%"><?=$lang['forums_merge_source']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="60%"><?=sprintf($forumselect, "source")?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="40%"><?=$lang['forums_merge_target']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="60%"><?=sprintf($forumselect, "target")?></td></tr>
</table><br><center><input type="submit" name="mergesubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}forums WHERE fid IN ('$source', '$target') AND type<>'group'");
		if(($db->result($query, 0)) != 2) {
			cpmsg('forums_nonexistence');
		}

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}forums WHERE fup='$source'");
		if($db->result($query, 0)) {
			cpmsg('forums_merge_source_sub_notnull');
		}

		$db->query("UPDATE {$tablepre}threads SET fid='$target' WHERE fid='$source'");
		$db->query("UPDATE {$tablepre}posts SET fid='$target' WHERE fid='$source'");

		$query = $db->query("SELECT threads, posts FROM {$tablepre}forums WHERE fid='$source'");
		$sourceforum = $db->fetch_array($query);

		$db->query("UPDATE {$tablepre}forums SET threads=threads+$sourceforum[threads], posts=posts+$sourceforum[posts] WHERE fid='$target'");
		$db->query("DELETE FROM {$tablepre}forums WHERE fid='$source'");
		$db->query("DELETE FROM {$tablepre}forumfields WHERE fid='$source'");
		$db->query("DELETE FROM {$tablepre}moderators WHERE fid='$source'");

		$query = $db->query("SELECT * FROM {$tablepre}access WHERE fid='$source'");
		while($access = $db->fetch_array($query)) {
			$db->query("INSERT INTO {$tablepre}access (uid, fid, allowview, allowpost, allowreply, allowgetattach)
				VALUES ('$access[uid]', '$target', '$access[allowview]', '$access[allowpost]', '$access[allowreply]', '$access[allowgetattach]')", 'SILENT');
		}
		$db->query("DELETE FROM {$tablepre}access WHERE fid='$source'");

		updatecache('forums');

		cpmsg('forums_merge_succeed', 'admincp.php?action=forumsedit');
	}

} elseif($action == 'forumdetail') {

	$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm', 'postattachperm');

	$query = $db->query("SELECT *, f.fid AS fid FROM {$tablepre}forums f
		LEFT JOIN {$tablepre}forumfields ff USING (fid)
		WHERE f.fid='$fid'");

	if(!$forum = $db->fetch_array($query)) {
		cpmsg('forums_nonexistence');
	}

	if(!submitcheck('detailsubmit')) {

?>
<br><form method="post" action="admincp.php?action=forumdetail&fid=<?=$fid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="type" value="<?=$forum['type']?>">
<?

		if($forum['type'] == 'group') {

			showtype("$lang[forums_cat_detail] - $forum[name]", 'top');
			showsetting('forums_cat_name', 'namenew', $forum['name'], 'text');
			showtype('', 'bottom');

		} else {

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forums_edit_tips']?>
</td></tr></table><br><br>
<?

			$fupselect = "<select name=\"fupnew\">\n<option value=\"0\" ".(!$forum[fup] ? "selected=\"selected\"" : NULL)."> - $lang[none] - </option>\n";
			$query = $db->query("SELECT fid, name FROM {$tablepre}forums WHERE fid<>'$fid' AND type<>'sub' ORDER BY displayorder");
			while($fup = $db->fetch_array($query)) {
				$selected = $fup['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
				$fupselect .= "<option value=\"$fup[fid]\" $selected>$fup[name]</option>\n";
			}
			$fupselect .= '</select>';

			$groups = array();
			$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups");
			while($group = $db->fetch_array($query)) {
				$groups[] = $group;
			}

			$styleselect = "<select name=\"styleidnew\"><option value=\"0\">$lang[use_default]</option>";
			$query = $db->query("SELECT styleid, name FROM {$tablepre}styles");
			while($style = $db->fetch_array($query)) {
				$styleselect .= "<option value=\"$style[styleid]\" ".
					($style['styleid'] == $forum['styleid'] ? 'selected="selected"' : NULL).
					">$style[name]</option>\n";
			}
			$styleselect .= '</select>';

			if($forum['autoclose']) {
				$acoption = $forum['autoclose'] / abs($forum['autoclose']);
				$forum['autoclose'] = abs($forum['autoclose']);
			} else {
				$acoption = 0;
			}
			$checkac = array($acoption => 'checked');

			$checktrade = array();
			$forum['allowtrade'] = sprintf('%02b', $forum['allowtrade']);
			for($i = 1; $i <= 2; $i++) {
				$checktrade[$i] = $forum['allowtrade'][2 - $i] ? 'checked' : '';
			}

			$checkmod = array($forum['modnewposts'] => 'checked');
			$checkrules = array($forum['alloweditrules'] => 'checked');

			foreach($perms as $perm) {
				$num = -1;
				$$perm = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" align=\"center\"><tr>";
				foreach($groups as $group) {
					$num++;
					if($num && $num % 4 == 0) {
						$$perm .= "</tr><tr>";
					}
					$checked = strstr($forum[$perm], "\t$group[groupid]\t") ? 'checked' : NULL;
					$$perm .= "<td><input type=\"checkbox\" name=\"{$perm}[]\" value=\"$group[groupid]\" $checked> $group[grouptitle]</td>\n";
				}
				$$perm .= '</tr></table>';
			}

			$viewaccess = $postaccess = $replyaccess = $getattachaccess = $postattachaccess = '';

			$query = $db->query("SELECT m.username, a.* FROM {$tablepre}access a LEFT JOIN {$tablepre}members m USING (uid) WHERE fid='$fid'");
			while($access = $db->fetch_array($query)) {
				$member = ", <a href=\"admincp.php?action=access&uid=$access[uid]\" target=\"_blank\">$access[username]</a>";
				$viewaccess .= $access['allowview'] ? $member : NULL;
				$postaccess .= $access['allowpost'] ? $member : NULL;
				$replyaccess .= $access['allowreply'] ? $member : NULL;
				$getattachaccess .= $access['allowgetattach'] ? $member : NULL;
				$postattachaccess .= $access['allowpostattach'] ? $member : NULL;
			}
			unset($member);

			/* old
			$forum['description'] = str_replace('&lt;', '<', $forum['description']);
			$forum['description'] = str_replace('&gt;', '>', $forum['description']);
			*/

			if($forum['threadtypes']) {
				$forum['threadtypes'] = unserialize($forum['threadtypes']);
				$forum['threadtypes']['status'] = 1;
			} else {
				$forum['threadtypes'] = array('status' => 0, 'required' => 0, 'listable' => 0, 'prefix' => 0, 'options' => array());
			}

			$typeselect = '';
			$query = $db->query("SELECT * FROM {$tablepre}threadtypes ORDER BY displayorder");
			while($type = $db->fetch_array($query)) {
				$typeselect .= '<input type="checkbox" name="threadtypesnew[options][]" value="'.$type['typeid'].'" '.(isset($forum['threadtypes']['types'][$type['typeid']]) ? 'checked' : '').'> '.$type['name'].($type['description'] ? ' ('.$type['description'].')' : '').'<br>';
			}
			$typeselect = $typeselect ? $typeselect : $lang['forums_edit_threadtypes_options_null'];

			$forum['postcredits'] = $forum['postcredits']? unserialize($forum['postcredits']) : array();
			$forum['replycredits'] = $forum['replycredits']? unserialize($forum['replycredits']) : array();

			showtype("$lang[forums_detail] - $forum[name]", 'top');
			showsetting('forums_edit_display', 'statusnew', $forum['status'], 'radio');
			showsetting('forums_edit_up', '', '', $fupselect);
			showsetting('forums_edit_style', '', '', $styleselect);
			showsetting('forums_edit_redirect', 'redirectnew', $forum['redirect'], 'text');
			showsetting('forums_edit_name', 'namenew', $forum['name'], 'text');
			showsetting('forums_edit_icon', 'iconnew', $forum['icon'], 'text');
			showsetting('forums_edit_description', 'descriptionnew', $forum['description'], 'textarea');
			showsetting('forums_edit_rules', 'rulesnew', $forum['rules'], 'textarea');
			showsetting('forums_edit_edit_rules', '', '', '<input type="radio" name="alloweditrulesnew" value="0" '.$checkrules[0].'> '.$lang['forums_edit_edit_rules_html_none'].'<br><input type="radio" name="alloweditrulesnew" value="1" '.$checkrules[1].'> '.$lang['forums_edit_edit_rules_html_no'].'<br><input type="radio" name="alloweditrulesnew" value="2" '.$checkrules[2].'> '.$lang['forums_edit_edit_rules_html_yes']);

			showtype('forums_edit_options');
			showsetting('forums_edit_modposts', '', '', '<input type="radio" name="modnewpostsnew" value="0" '.$checkmod[0].'> '.$lang['none'].'<br><input type="radio" name="modnewpostsnew" value="1" '.$checkmod[1].'> '.$lang['forums_edit_modposts_threads'].'<br><input type="radio" name="modnewpostsnew" value="2" '.$checkmod[2].'> '.$lang['forums_edit_modposts_posts']);
			showsetting('forums_edit_recyclebin', 'recyclebinnew', $forum['recyclebin'], 'radio');
			showsetting('forums_edit_blog', 'allowblognew', $forum['allowblog'], 'radio');
			showsetting('forums_edit_html', 'allowhtmlnew', $forum['allowhtml'], 'radio');
			showsetting('forums_edit_bbcode', 'allowbbcodenew', $forum['allowbbcode'], 'radio');
			showsetting('forums_edit_imgcode', 'allowimgcodenew', $forum['allowimgcode'], 'radio');
			showsetting('forums_edit_smilies', 'allowsmiliesnew', $forum['allowsmilies'], 'radio');
			showsetting('forums_edit_jammer', 'jammernew', $forum['jammer'], 'radio');
			showsetting('forums_edit_disablewatermark', 'disablewatermarknew', $forum['disablewatermark'], 'radio');
			showsetting('forums_edit_trade', '', '', '<input type="checkbox" name="allowtradenew[1]" value="1" '.$checktrade[1].'> '.$lang['forums_edit_trade_thread'].'<br><input type="checkbox" name="allowtradenew[2]" value="1" '.$checktrade[2].'> '.$lang['forums_edit_trade_payto']);
			showsetting('forums_edit_autoclose', '', '', '<input type="radio" name="autoclosenew" value="0" '.$checkac[0].' onclick="this.form.autoclosetimenew.disabled=true;"> '.$lang['forums_edit_autoclose_none'].'<br><input type="radio" name="autoclosenew" value="1" '.$checkac[1].' onclick="this.form.autoclosetimenew.disabled=false;"> '.$lang['forums_edit_autoclose_dateline'].'<br><input type="radio" name="autoclosenew" value="-1" '.$checkac[-1].' onclick="this.form.autoclosetimenew.disabled=false;"> '.$lang['forums_edit_autoclose_lastpost']);
			showsetting('forums_edit_autoclose_time', '', '', '<input type="text" size="30" value="'.$forum['autoclose'].'" name="autoclosetimenew" '.($acoption ? '' : 'disabled').'>');
			showsetting('forums_edit_attach_ext', 'attachextensionsnew', $forum['attachextensions'], 'text');

			showtype('forums_edit_credits');
			showsetting('forums_edit_postcredits', 'postcreditsstatus', $forum['postcredits'], 'radio');
			showsetting('forums_edit_replycredits', 'replycreditsstatus', $forum['replycredits'], 'radio');
			echo '<tr><td colspan="2" bgcolor="'.ALTBG1.'"><table cellspacing="'.INNERBORDERWIDTH.'" cellpadding="'.TABLESPACE.'" width="100%" align="center" class="tableborder">'.
				'<tr align="center" class="header"><td>'.$lang['credits_id'].'</td><td>'.$lang['credits_title'].'</td><td>'.$lang['forums_edit_postcredits_add'].'</td><td>'.$lang['forums_edit_replycredits_add'].'</td></tr>';
			for($i = 1; $i <= 8; $i++) {
				echo "<tr align=\"center\" ".(isset($extcredits[$i]) ? '' : 'disabled')."><td bgcolor=\"".ALTBG1."\">extcredits$i</td>".
					"<td bgcolor=\"".ALTBG2."\">{$extcredits[$i]['title']}</td>".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\" name=\"postcreditsnew[$i]\" value=\"".(isset($forum['postcredits'][$i]) ? $forum['postcredits'][$i] : 0)."\"></td>".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"2\" name=\"replycreditsnew[$i]\" value=\"".(isset($forum['replycredits'][$i]) ? $forum['replycredits'][$i] : 0)."\"></td></tr>";
			}
			echo '</table></td></tr>';

			showtype('forums_edit_threadtypes');
			showsetting('forums_edit_threadtypes_status', 'threadtypesnew[status]', $forum['threadtypes']['status'], 'radio');
			showsetting('forums_edit_threadtypes_required', 'threadtypesnew[required]', $forum['threadtypes']['required'], 'radio');
			showsetting('forums_edit_threadtypes_listable', 'threadtypesnew[listable]', $forum['threadtypes']['listable'], 'radio');
			showsetting('forums_edit_threadtypes_prefix', 'threadtypesnew[prefix]', $forum['threadtypes']['prefix'], 'radio');
			showsetting('forums_edit_threadtypes_options', '', '', $typeselect);

			showtype('forums_edit_perm');
			showsetting('forums_edit_perm_passwd', 'passwordnew', $forum['password'], 'text', '15%');
			echo '<tr><td colspan="2" class="singleborder">&nbsp;</td></tr>';

			showsetting('forums_edit_perm_view', '', '', str_replace('cdb_groupname', 'viewperm', $viewperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($viewaccess, 2).'</td></tr>';
			echo '<tr><td colspan="2" class="singleborder">&nbsp;</td></tr>';

			showsetting('forums_edit_perm_post', '', '', str_replace('cdb_groupname', 'postperm', $postperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($postaccess, 2).'</td></tr>';
			echo '<tr><td colspan="2" class="singleborder">&nbsp;</td></tr>';

			showsetting('forums_edit_perm_reply', '', '', str_replace('cdb_groupname', 'replyperm', $replyperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($replyaccess, 2).'</td></tr>';
			echo '<tr><td colspan="2" class="singleborder">&nbsp;</td></tr>';

			showsetting('forums_edit_perm_get_attach', '', '', str_replace('cdb_groupname', 'getattachperm', $getattachperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($getattachaccess, 2).'</td></tr>';
			echo '<tr><td colspan="2" class="singleborder">&nbsp;</td></tr>';

			showsetting('forums_edit_perm_post_attach', '', '', str_replace('cdb_groupname', 'postattachperm', $postattachperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($postattachaccess, 2).'</td></tr>';

			showtype('', 'bottom');

		}

		echo "<br><br><center><input type=\"submit\" name=\"detailsubmit\" value=\"$lang[submit]\"></form>";

	} else {

		if($type == 'group') {

			if($namenew) {
				$db->query("UPDATE {$tablepre}forums SET name='$namenew' WHERE fid='$fid'");
				updatecache('forums');

				cpmsg('forums_edit_succeed');
			} else {
				cpmsg('forums_edit_name_invalid');
			}
			
		} else {

			$extensionarray = array();
			foreach(explode(',', $attachextensionsnew) as $extension) {
				if($extension = trim($extension)) {
					$extensionarray[] = $extension;
				}
			}
			$attachextensionsnew = implode(', ', $extensionarray);

			foreach($perms as $perm) {
				${$perm.'new'} = is_array($$perm) && !empty($$perm) ? "\t".implode("\t", $$perm)."\t" : '';
			}

			$fupadd = '';
			if($fupnew != $forum['fup']) {
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE fup='$fid'");
				if($db->num_rows($query)) {
					cpmsg('forums_edit_sub_notnull');
				}

				$query = $db->query("SELECT fid, type, inheritedmod FROM {$tablepre}forums WHERE fid='$fupnew'");
				$fup = $db->fetch_array($query);

				$fupadd = ", type='".($fup['type'] == 'forum' ? 'sub' : 'forum')."', fup='$fup[fid]'";

				$db->query("DELETE FROM {$tablepre}moderators WHERE fid='$fid' AND inherited='1'");
				$query = $db->query("SELECT * FROM {$tablepre}moderators WHERE fid='$fupnew' ".($fup['inheritedmod'] ? '' : "AND inherited='1'"));
				while($mod = $db->fetch_array($query)) {
					$db->query("REPLACE INTO {$tablepre}moderators (uid, fid, displayorder, inherited)
						VALUES ('$mod[uid]', '$fid', '0', '1')");
				}

				$moderators = $tab = '';
				$query = $db->query("SELECT m.username FROM {$tablepre}members m, {$tablepre}moderators mo WHERE mo.fid='$fid' AND mo.inherited='0' AND m.uid=mo.uid ORDER BY mo.displayorder");
				while($mod = $db->fetch_array($query)) {
					$moderators .= $tab.addslashes($mod['username']);
					$tab = "\t";
				}
				$db->query("UPDATE {$tablepre}forumfields SET moderators='$moderators' WHERE fid='$fid'");
			}

			if(!$allowblognew && (bool)$allowblognew != (bool)$forum['allowblog']) {
				$db->query("UPDATE {$tablepre}threads SET blog='0' WHERE fid='$fid'");
			}

			$allowtradenew = bindec(intval($allowtradenew[2]).intval($allowtradenew[1]));

			$db->query("UPDATE {$tablepre}forums SET status='$statusnew', name='$namenew', styleid='$styleidnew',
				allowblog='$allowblognew', allowtrade='$allowtradenew', allowhtml='$allowhtmlnew', allowbbcode='$allowbbcodenew',
				allowimgcode='$allowimgcodenew', allowsmilies='$allowsmiliesnew', alloweditrules='$alloweditrulesnew',
				modnewposts='$modnewpostsnew', recyclebin='$recyclebinnew', jammer='$jammernew',
				disablewatermark='$disablewatermarknew', autoclose='".intval($autoclosenew * $autoclosetimenew)."' $fupadd
				WHERE fid='$fid'");

			$query = $db->query("SELECT fid FROM {$tablepre}forumfields WHERE fid='$fid'");
			if(!($db->num_rows($query))) {
				$db->query("INSERT INTO {$tablepre}forumfields (fid)
					VALUES ('$fid')");
			}

			$postcreditsarray = $replycreditsarray = array();

			if($postcreditsstatus) {
				for($i = 1; $i <= 8; $i++) {
					if(isset($extcredits[$i]) && isset($postcreditsnew[$i])) {
						$postcreditsnew[$i]  = $postcreditsnew[$i] < -99 ? -99 : $postcreditsnew[$i];
						$postcreditsnew[$i]  = $postcreditsnew[$i] > 99 ? 99 : $postcreditsnew[$i];
						$postcreditsarray[$i] = intval($postcreditsnew[$i]);
					}
				}
			}
			if($replycreditsstatus) {
				for($i = 1; $i <= 8; $i++) {
					if(isset($extcredits[$i]) && isset($replycreditsnew[$i])) {
						$replycreditsnew[$i]  = $replycreditsnew[$i] < -99 ? -99 : $replycreditsnew[$i];
						$replycreditsnew[$i]  = $replycreditsnew[$i] > 99 ? 99 : $replycreditsnew[$i];
						$replycreditsarray[$i] = intval($replycreditsnew[$i]);
					}
				}
			}
			$postcreditsnew = $postcreditsarray ? addslashes(serialize($postcreditsarray)) : '';
			$replycreditsnew = $replycreditsarray ? addslashes(serialize($replycreditsarray)) : '';

			if($threadtypesnew['status']) {
				if(is_array($threadtypesnew['options']) && $threadtypesnew['options']) {
					$threadtypesnew['types'] = array();
					$query = $db->query("SELECT * FROM {$tablepre}threadtypes WHERE typeid IN ('".implode('\',\'', $threadtypesnew['options'])."') ORDER BY displayorder");
					while($type = $db->fetch_array($query)) {
						$threadtypesnew['types'][$type['typeid']] = $type['name'];
					}

					$threadtypesnew = $threadtypesnew['types'] ? addslashes(serialize(array
						(
						'required' => (bool)$threadtypesnew['required'],
						'listable' => (bool)$threadtypesnew['listable'],
						'prefix' => (bool)$threadtypesnew['prefix'],
						'types' => $threadtypesnew['types']
						))) : '';
				}
			} else {
				$threadtypesnew = '';
			}

			$db->query("UPDATE {$tablepre}forumfields SET description='$descriptionnew', icon='$iconnew', password='$passwordnew', redirect='$redirectnew', rules='$rulesnew',
				attachextensions='$attachextensionsnew', threadtypes='$threadtypesnew', postcredits='$postcreditsnew', replycredits='$replycreditsnew', viewperm='$viewpermnew',
				postperm='$postpermnew', replyperm='$replypermnew', getattachperm='$getattachpermnew', postattachperm='$postattachpermnew' WHERE fid='$fid'");

			if($statusnew == 0) {
				$db->query("UPDATE {$tablepre}forums SET status='$statusnew' WHERE fup='$fid'", 'UNBUFFERED');
			}

			updatecache('forums');

			cpmsg('forums_edit_succeed', 'admincp.php?action=forumsedit');
		}

	}

} elseif($action == 'forumdelete') {

	$query = $db->query("SELECT COUNT(*) FROM {$tablepre}forums WHERE fup='$fid'");
	if($db->result($query, 0)) {
		cpmsg('forums_delete_sub_notnull');
	}

	if(!$confirmed) {
		cpmsg('forums_delete_confirm', "admincp.php?action=forumdelete&fid=$fid", 'form');
	} else {
		require_once DISCUZ_ROOT.'./include/post.func.php';

		$tids = 0;
		$query = $db->query("SELECT tid FROM {$tablepre}threads WHERE attachment>'0' AND fid='$fid'");
		while($thread = $db->fetch_array($query)) {
			$tids .= ','.$thread['tid'];
		}

		if($tids) {
			$query = $db->query("SELECT filename FROM {$tablepre}attachments WHERE tid IN ($tids)");
			while($attach = $db->fetch_array($query)) {
				@unlink($attachdir.'/'.$attach['filename']);
			}
			$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($tids)");
		}

		$db->query("DELETE FROM {$tablepre}threads WHERE fid='$fid'");
		$db->query("DELETE FROM {$tablepre}posts WHERE fid='$fid'");
		$db->query("DELETE FROM {$tablepre}forums WHERE fid='$fid'");
		$db->query("DELETE FROM {$tablepre}forumfields WHERE fid='$fid'");
		$db->query("DELETE FROM {$tablepre}moderators WHERE fid='$fid'");
		$db->query("DELETE FROM {$tablepre}access WHERE fid='$fid'");

		updatecache('forums');

		cpmsg('forums_delete_succeed', 'admincp.php?action=forumsedit');
	}

} elseif($action == 'threadtypes') {

	if(!submitcheck('typesubmit')) {

		$forumsarray = array();
		$query = $db->query("SELECT f.fid, f.name, ff.threadtypes FROM {$tablepre}forums f , {$tablepre}forumfields ff WHERE ff.threadtypes<>'' AND f.fid=ff.fid");
		while($forum = $db->fetch_array($query)) {
			$forum['threadtypes'] = unserialize($forum['threadtypes']);
			if(is_array($forum['threadtypes']['types'])) {
				foreach($forum['threadtypes']['types'] as $typeid => $name) {
					$forumsarray[$typeid][] = '<a href="forumdisplay.php?fid='.$forum['fid'].'" target="_blank">'.$forum['name'].'</a> [<a href="admincp.php?action=forumdetail&fid='.$forum['fid'].'">'.$lang['edit'].'</a>]';
				}
			}
		}			

		$threadtypes = '';
		$query = $db->query("SELECT * FROM {$tablepre}threadtypes ORDER BY displayorder");
		while($type = $db->fetch_array($query)) {
			$threadtypes .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$type[typeid]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"15\" name=\"namenew[$type[typeid]]\" value=\"".dhtmlspecialchars($type['name'])."\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\" name=\"displayordernew[$type[typeid]]\" value=\"$type[displayorder]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"30\" name=\"descriptionnew[$type[typeid]]\" value=\"$type[description]\"></td>\n".
				"<td bgcolor=\"".ALTBG1."\">".(is_array($forumsarray[$type['typeid']]) ? implode(', ', $forumsarray[$type['typeid']]) : '')."</td></tr>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forums_threadtypes_tips']?>
</td></tr></table>

<form method="post" action="admincp.php?action=threadtypes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['forums_threadtypes']?></td></tr>
<tr align="center" class="category"><td><?=$lang['del']?></td><td><?=$lang['forums_threadtypes']?></td><td><?=$lang['display_order']?></td><td><?=$lang['description']?></td><td><?=$lang['forums_threadtypes_forums']?></td></tr>
<?=$threadtypes?>
<tr><td colspan="5" class="singleborder">&nbsp;</td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>"><?=$lang['add_new']?></td><td bgcolor="<?=ALTBG2?>"><input type='text' name="newname" size="15"></td><td bgcolor="<?=ALTBG1?>"><input type="text" name="newdisplayorder" size="2" value="0"></td><td bgcolor="<?=ALTBG2?>"><input type="text" name="newdescription" size="30" value=""></td><td bgcolor="<?=ALTBG1?>">&nbsp;</td></tr>
</table><br>
<center><input type="submit" name="typesubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$modifiedtypes = array();

		if(is_array($delete)) {
			$modifiedtypes = $delete;
			$deleteids = '\''.implode('\',\'', $delete).'\'';
			$db->query("DELETE FROM {$tablepre}threadtypes WHERE typeid IN ($deleteids)");
			if($db->affected_rows()) {
				$db->query("UPDATE {$tablepre}threads SET typeid='0' WHERE typeid IN ($deleteids)");
			}
		}

		if(is_array($namenew)) {
			foreach($namenew as $typeid => $val) {
				$db->query("UPDATE {$tablepre}threadtypes SET name='".trim($namenew[$typeid])."', description='".dhtmlspecialchars(trim($descriptionnew[$typeid]))."', displayorder='$displayordernew[$typeid]' WHERE typeid='$typeid'");
				if($db->affected_rows()) {
					$modifiedtypes[] = $typeid;
				}
			}

			if($modifiedtypes = array_unique($modifiedtypes)) {
				$query = $db->query("SELECT f.fid, ff.threadtypes FROM {$tablepre}forums f, {$tablepre}forumfields ff WHERE ff.threadtypes<>'' AND f.fid=ff.fid");
				while($forum = $db->fetch_array($query)) {
					$forum['threadtypes'] = unserialize($forum['threadtypes']);
					foreach($modifiedtypes as $typeid) {
						if(isset($forum['threadtypes']['types'][$typeid])) {
							$db->query("SELECT * FROM {$tablepre}threadtypes WHERE typeid IN (".implode(',', array_keys($forum['threadtypes']['types'])).") ORDER BY displayorder");
							$forum['threadtypes']['types'] = array();
							while($type = $db->fetch_array($query)) {
								$forum['threadtypes']['types'][$type['typeid']] = $type['name'];
							}
							$db->query("UPDATE {$tablepre}forumfields SET threadtypes='".addslashes(serialize($forum['threadtypes']))."' WHERE fid='$fid'");
							break;
						}
					}
				}			
			}
		}

		if($newname != '') {
			$newname = dhtmlspecialchars(trim($newname));
			$query = $db->query("SELECT typeid FROM {$tablepre}threadtypes WHERE name='$newname'");
			if($db->num_rows($query)) {
				cpmsg('forums_threadtypes_duplicate');
			}
			$db->query("INSERT INTO	{$tablepre}threadtypes (name, description, displayorder) VALUES
					('$newname', '".dhtmlspecialchars(trim($newdescription))."', '$newdisplayorder')");
		}

		cpmsg('forums_threadtypes_succeed', 'admincp.php?action=threadtypes');

	}

} elseif($action == 'forumrules') {

	if(empty($fid)) {

		$forums = '';

		if($adminid == 2) {
			$query = $db->query("SELECT fid, name FROM {$tablepre}forums
				WHERE alloweditrules>'0' AND type IN ('forum', 'sub')");
		} else {
			$query = $db->query("SELECT f.fid, f.name, m.uid FROM {$tablepre}forums f
				LEFT JOIN {$tablepre}moderators m ON m.uid='$discuz_uid' AND m.fid=f.fid
				WHERE alloweditrules>'0' AND f.type IN ('forum', 'sub')");
		}

		while($forum = $db->fetch_array($query)) {
			if($forum['uid'] || $adminid == 2) {
				$forums .= "<option value=\"$forum[fid]\">".strip_tags($forum['name'])."</option>";
			}
		}

		if($forums) {
			$forums = '<select name="fid">'.$forums.'</select>';
		} else {
			cpmsg('forums_rules_nopermission');
		}

?>
<br><br><form method="post" action="admincp.php?action=forumrules">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="70%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['forums_edit']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td><?=$lang['forum']?>:</td><td><?=$forums?></td></tr>
</table><br><center>
<input type="submit" value="<?=$lang['submit']?>">
</center></form><br>
<?

	} else {

		$access = 0;
		if($adminid == 2) {
			$access = 1;
		} elseif($adminid == 3) {
			$query = $db->query("SELECT uid FROM {$tablepre}moderators WHERE uid='$discuz_uid' AND fid='$fid'");
			$access = $db->num_rows($query) ? 1 : 0;
		}

		$query = $db->query("SELECT f.fid, f.name, f.alloweditrules, ff.rules FROM {$tablepre}forums f
			LEFT JOIN {$tablepre}forumfields ff USING (fid)
			WHERE f.fid='$fid' AND alloweditrules>'0' AND type IN ('forum', 'sub')");

		if(!$access || !($forum = $db->fetch_array($query))) {
			cpmsg('forums_rules_nopermission');
		}

		if(!submitcheck('rulessubmit')) {

			$comment = $lang[($forum['alloweditrules'] == 1 ? 'forums_edit_edit_rules_html_no' : 'forums_edit_edit_rules_html_yes')];

?>
<br><br><form method="post" action="admincp.php?action=forumrules&fid=<?=$fid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="70%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['forums_edit']?> - <?=$forum['name']?></td></tr>
<tr bgcolor="<?=ALTBG2?>"><td valign="top"><span class="bold"><?=$lang['forums_edit_rules']?></span><br><?=$comment?></td>
<td><textarea name="rulesnew" rows="5" cols="60"><?=dhtmlspecialchars($forum['rules'])?></textarea></td></tr>
</table><br><center>
<input type="submit" name="rulessubmit" value="<?=$lang['submit']?>">
</center></form><br>
<?

		} else {

			if($forum['alloweditrules'] != 2) {
				$rulesnew = dhtmlspecialchars($rulesnew);
			}

			$db->query("UPDATE {$tablepre}forumfields SET rules='$rulesnew' WHERE fid='$fid'");

			cpmsg('forums_rules_succeed');

		}

	}

}

?>