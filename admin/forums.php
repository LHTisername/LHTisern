<?php

/*
	[DISCUZ!] forums.php - modifing forums settings of Discuz! admincp
	This is NOT a freeware, use is subject to license terms

	Version: 3.1.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2003/9/16 06:13
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

cpheader();

if($action == 'forumadd') {

	if((!submitcheck('catsubmit') && !submitcheck('forumsubmit'))) {
		$groupselect = $forumselect = "<select name=\"fup\">\n<option value=\"0\" selected=\"selected\"> - $lang[none] - </option>\n";
		$query = $db->query("SELECT fid, name, type FROM $table_forums WHERE type<>'sub' ORDER BY displayorder");
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
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forums_tips']?>
</td></tr></table></td></tr></table>

<br><form method="post" action="admincp.php?action=forumadd&add=category">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="3"><?=$lang['forums_new_cat']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['name']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="70%"><input type="text" name="newcat" value="Name of New Category" size="40"></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><input type="submit" name="catsubmit" value="<?=$lang['submit']?>"></td></tr>
</table></td></tr></table></form>

<form method="post" action="admincp.php?action=forumadd&add=forum">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="5"><?=$lang['forums_new_forum']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['name']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="28%"><input type="text" name="newforum" value="Name of New Forum" size="20"></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['forums_cat_parent']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="27%"><?=$groupselect?></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><input type="submit" name="forumsubmit" value="<?=$lang['submit']?>"></td></tr>
</table></td></tr></table></form>

<form method="post" action="admincp.php?action=forumadd&add=forum">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="5"><?=$lang['forums_new_sub']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['name']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="28%"><input type="text" name="newforum" value="Name of New Sub Forum" size="20"></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><?=$lang['forums_edit_parent']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="27%"><?=$forumselect?></td>
<td bgcolor="<?=ALTBG1?>" width="15%"><input type="submit" name="forumsubmit" value="<?=$lang['submit']?>"></td></tr>
</table></td></tr></table></form><br>
<?

	} elseif(submitcheck('catsubmit')) {
		$db->query("INSERT INTO $table_forums (type, name, status)
			VALUES ('group', '$newcat', '1')");

		updatecache('forums');
		cpmsg('forums_new_cat_succeed', 'admincp.php?action=forumsedit');
	} elseif(submitcheck('forumsubmit')) {
		$query = $db->query("SELECT type FROM $table_forums WHERE fid='$fup'");
		$type = $db->result($query, 0) == "forum" ? "sub" : "forum";
		$db->query("INSERT INTO $table_forums (fup, type, name, status, allowsmilies, allowbbcode, allowimgcode)
			VALUES ('$fup', '$type', '$newforum', '1', '1', '1', '1')");

		updatecache('forums');
		cpmsg('forums_new_forum_succeed', 'admincp.php?action=forumsedit');
	}		

} elseif($action == 'forumsedit') {

	if(!submitcheck('editsubmit')) {

?>
<table cellspacing="0" cellpadding="0" border="0" width="<?=TABLEWIDTH?>" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">

<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td><?=$lang['forums_edit']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>"><br>
<form method="post" action="admincp.php?action=forumsedit">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		$modsorig = $comma = '';
		$query = $db->query("SELECT fid, type, status, name, fup, displayorder, moderator FROM $table_forums ORDER BY displayorder");
		while($forum = $db->fetch_array($query)) {
			$forums[] = $forum;
			$modsorig .= $comma.$forum['moderator'];
			$comma = ',';
		}

		for($i = 0; $i < count($forums); $i++) {
			if($forums[$i]['type'] == 'group') {
				echo '<ul>';
				showforum($forums[$i], 1, 'group');
				for($j = 0; $j < count($forums); $j++) {
					if($forums[$j]['fup'] == $forums[$i]['fid'] && $forums[$j]['type'] == 'forum') {
						echo '<ul>';
						showforum($forums[$j], 2);
						for($k = 0; $k < count($forums); $k++) {
							if($forums[$k]['fup'] == $forums[$j]['fid'] && $forums[$k]['type'] == 'sub') {
								echo '<ul>';
								showforum($forums[$k], 3, 'sub');
								echo '</ul>';
							}
						}
						echo '</ul>';
					}
				}
				echo '</ul>';
			} elseif(!$forums[$i]['fup'] && $forums[$i]['type'] == 'forum') {
				echo '<ul>';
				showforum($forums[$i], 1);
				for($j = 0; $j < count($forums); $j++) {
					if($forums[$j]['fup'] == $forums[$i]['fid'] && $forums[$j]['type'] == 'sub') {
						echo '<ul>';
						showforum($forums[$j], 2, 'sub');
						echo '</ul>';
					}
				}
				echo '</ul>';
			}
		}
		echo "<input type=\"hidden\" name=\"modsorig\" value=\"$modsorig\"><br><center>\n".
			"<input type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center><br></td></tr></table></td></tr></table>\n";

	} else {

		// read from groups
		$usergroups = array();
		$query = $db->query("SELECT groupid, type, creditshigher, creditslower FROM $table_usergroups");
		while($group = $db->fetch_array($query)) {
			$usergroups[$group['groupid']] = $group;
		}

		if(is_array($order)) {
			$modlist = $comma = '';
			foreach($order as $fid => $value) {
				if($moderator[$fid]) {
					$modlist .= $comma.$moderator[$fid];
					$comma = ',';
				}
				$moderator[$fid] = str_replace(',', ', ', str_replace(' ', '', $moderator[$fid]));
				$db->query("UPDATE $table_forums SET moderator='$moderator[$fid]', displayorder='$order[$fid]' WHERE fid='$fid'");
			}
		}

		$modsorig = "'".str_replace(',', "','", str_replace(' ', '', $modsorig))."'";
		$modlist = "'".str_replace(',', "','", str_replace(' ', '', $modlist))."'";

		$db->query("UPDATE $table_members SET groupid='3' WHERE username IN ($modlist) AND adminid NOT IN (1,2,3,4,5,6,7,8,-1)");
		$db->query("UPDATE $table_members SET adminid='3' WHERE username IN ($modlist) AND adminid NOT IN (1,2)");

		$query = $db->query("SELECT uid, groupid, credit FROM $table_members WHERE username IN ($modsorig) AND username NOT IN ($modlist) AND adminid NOT IN (1,2)");
		while($member = $db->fetch_array($query)) {
			if($usergroups[$member['groupid']]['type'] == 'special') {
				$adminidnew = -1;
				$groupidnew = $member['groupid'];
			} else {
				$adminidnew = 0;
				foreach($usergroups as $group) {
					if($group['type'] == 'member' && $member['credit'] >= $group['creditshigher'] && $member['credit'] < $group['creditslower']) {
						$groupidnew = $group['groupid'];
						break;
					}
				}
			}
			$db->query("UPDATE $table_members SET adminid='$adminidnew', groupid='$groupidnew' WHERE uid='$member[uid]'");
		}

		updatecache('forums');
		cpmsg('forums_updated_succeed', 'admincp.php?action=forumsedit');
	}

} elseif($action == 'forumsmerge') {

	if(!submitcheck('mergesubmit') || $source == $target || !$source || !$target) {
		$forumselect = "<select name=\"%s\">\n<option value=\"0\" selected=\"selected\"> - $lang[none] - </option>\n";
		$query = $db->query("SELECT fid, name FROM $table_forums WHERE type<>'group' ORDER BY displayorder");
		while($forum = $db->fetch_array($query)) {
			$forumselect .= "<option value=\"$forum[fid]\">$forum[name]</option>\n";
		}
		$forumselect .= '</select>';

?>
<br><br><br><br><br>
<form method="post" action="admincp.php?action=forumsmerge">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="85%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="3"><?=$lang['forums_merge']?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="40%"><?=$lang['forums_merge_source']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="60%"><?=sprintf($forumselect, "source")?></td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>" width="40%"><?=$lang['forums_merge_target']?>:</td>
<td bgcolor="<?=ALTBG2?>" width="60%"><?=sprintf($forumselect, "target")?></td></tr>
</table></td></tr></table><br><center><input type="submit" name="mergesubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} else {

		$query = $db->query("SELECT COUNT(*) FROM $table_forums WHERE fup='$source'");
		if($db->result($query, 0)) {
			cpmsg('forums_merge_source_sub_notnull');
		}

		$db->query("UPDATE $table_threads SET fid='$target' WHERE fid='$source'");
		$db->query("UPDATE $table_posts SET fid='$target' WHERE fid='$source'");

		$query = $db->query("SELECT threads, posts FROM $table_forums WHERE fid='$source'");
		$sourceforum = $db->fetch_array($query);
		$db->query("UPDATE $table_forums SET threads=threads+$sourceforum[threads], posts=posts+$sourceforum[posts] WHERE fid='$target'");
		$db->query("DELETE FROM $table_forums WHERE fid='$source'");

		$query = $db->query("SELECT * FROM $table_access WHERE fid='$source'");
		while($access = $db->fetch_array($query)) {
			$db->query("INSERT INTO $table_access (uid, fid, allowview, allowpost, allowreply, allowgetattach)
				VALUES ('$access[uid]', '$target', '$access[allowview]', '$access[allowpost]', '$access[allowreply]', '$access[allowgetattach]')", 'SILENT');
		}
		$db->query("DELETE FROM $table_access WHERE fid='$source'");

		updatecache('forums');
		cpmsg('forums_merge_succeed', 'admincp.php?action=forumsedit');
	}

} elseif($action == 'forumdetail') {

	$perms = array('viewperm', 'postperm', 'replyperm', 'getattachperm');

	if(!submitcheck('detailsubmit')) {

		$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
		$forum = $db->fetch_array($query);
		$forum['name'] = dhtmlspecialchars($forum['name']);

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
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['forums_edit_tips']?>
</td></tr></table></td></tr></table><br><br>
<?

			$fupselect = "<select name=\"fupnew\">\n<option value=\"0\" ".(!$forum[fup] ? "selected=\"selected\"" : NULL)."> - $lang[none] - </option>\n";
			$query = $db->query("SELECT fid, name FROM $table_forums WHERE fid<>'$fid' AND type<>'sub' ORDER BY displayorder");
			while($fup = $db->fetch_array($query)) {
				$selected = $fup['fid'] == $forum['fup'] ? "selected=\"selected\"" : NULL;
				$fupselect .= "<option value=\"$fup[fid]\" $selected>$fup[name]</option>\n";
			}
			$fupselect .= '</select>';
			$query = $db->query("SELECT groupid, grouptitle FROM $table_usergroups");
			while($group = $db->fetch_array($query)) {
				$groups[] = $group;
			}

			$styleselect = "<select name=\"styleidnew\"><option value=\"0\">$lang[default]</option>";
			$query = $db->query("SELECT styleid, name FROM $table_styles");
			while($style = $db->fetch_array($query)) {
				$styleselect .= "<option value=\"$style[styleid]\" ".
					($style['styleid'] == $forum['styleid'] ? 'selected="selected"' : NULL).
					">$style[name]</option>\n";
			}
			$styleselect .= '</select>';

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

			$viewaccess = $postaccess = $replyaccess = $getattachaccess = '';

			$query = $db->query("SELECT m.username, a.* FROM $table_access a LEFT JOIN $table_members m USING (uid) WHERE fid='$fid'");
			while($access = $db->fetch_array($query)) {
				$member = ", <a href=\"admincp.php?action=access&uid=$access[uid]\" target=\"_blank\">$access[username]</a>";
				$viewaccess .= $access['allowview'] ? $member : NULL;
				$postaccess .= $access['allowpost'] ? $member : NULL;
				$replyaccess .= $access['allowreply'] ? $member : NULL;
				$getattachaccess .= $access['allowgetattach'] ? $member : NULL;
			}
			unset($member);

			$forum['description'] = str_replace('&lt;', '<', $forum['description']);
			$forum['description'] = str_replace('&gt;', '>', $forum['description']);

			showtype("$lang[forums_detail] - $forum[name]", 'top');
			showsetting('forums_edit_display', 'statusnew', $forum['status'], 'radio');
			showsetting('forums_edit_up', '', '', $fupselect);
			showsetting('forums_edit_style', '', '', $styleselect);
			showsetting('forums_edit_name', 'namenew', $forum['name'], 'text');
			showsetting('forums_edit_icon', 'iconnew', $forum['icon'], 'text');
			showsetting('forums_edit_description', 'descriptionnew', $forum['description'], 'textarea');

			showtype('forums_edit_func');
			showsetting('forums_edit_html', 'allowhtmlnew', $forum['allowhtml'], 'radio');
			showsetting('forums_edit_bbcode', 'allowbbcodenew', $forum['allowbbcode'], 'radio');
			showsetting('forums_edit_imgcode', 'allowimgcodenew', $forum['allowimgcode'], 'radio');
			showsetting('forums_edit_smilies', 'allowsmiliesnew', $forum['allowsmilies'], 'radio');
			showsetting('forums_edit_postcredits', 'postcreditsnew', $forum['postcredits'], 'text');
			showsetting('forums_edit_replycredits', 'replycreditsnew', $forum['replycredits'], 'text');
			
			showtype('forums_edit_perm');
			showsetting('forums_edit_perm_passwd', 'passwordnew', $forum['password'], 'text', '15%');
			echo '<tr><td colspan="2" height="1" bgcolor="'.ALTBG2.'"></td></tr>';

			showsetting('forums_edit_perm_view', '', '', str_replace('cdb_groupname', 'viewperm', $viewperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($viewaccess, 2).'</td></tr>';
			echo '<tr><td colspan="2" height="1" bgcolor="'.ALTBG2.'"></td></tr>';

			showsetting('forums_edit_perm_post', '', '', str_replace('cdb_groupname', 'postperm', $postperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($postaccess, 2).'</td></tr>';
			echo '<tr><td colspan="2" height="1" bgcolor="'.ALTBG2.'"></td></tr>';

			showsetting('forums_edit_perm_reply', '', '', str_replace('cdb_groupname', 'replyperm', $replyperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($replyaccess, 2).'</td></tr>';
			echo '<tr><td colspan="2" height="1" bgcolor="'.ALTBG2.'"></td></tr>';

			showsetting('forums_edit_perm_download', '', '', str_replace('cdb_groupname', 'getattachperm', $getattachperm), '15%');
			echo '<td width="15%" bgcolor="'.ALTBG1.'">'.$lang['forums_edit_access_mask'].'</td><td bgcolor="'.ALTBG2.'">'.substr($getattachaccess, 2).'</td></tr>';

			showtype('', 'bottom');

		}

		echo "<br><br><center><input type=\"submit\" name=\"detailsubmit\" value=\"$lang[submit]\"></form>";

	} else {

		if($type == 'group') {

			if($namenew) {
				$db->query("UPDATE $table_forums SET name='$namenew' WHERE fid='$fid'");
				updatecache('forums');
				cpmsg('forums_edit_succeed');
			} else {
				cpmsg('forums_edit_name_invalid');
			}
			
		} else {

			foreach($perms as $perm) {
				if(is_array($$perm)) {
					${$perm.'new'} = "\t";
					foreach($$perm as $groupid) {
						${$perm.'new'} .= "\t$groupid";
					}
					${$perm.'new'} .= "\t\t";
				}
			}

			$query = $db->query("SELECT type FROM $table_forums WHERE fid='$fupnew'");
			$fuptype = $db->result($query, 0);
			$typenew = $fuptype == 'forum' ? 'sub' : 'forum';
			$db->query("UPDATE $table_forums SET type='$typenew', status='$statusnew', fup='$fupnew', name='$namenew', icon='$iconnew',
				description='$descriptionnew', styleid='$styleidnew', allowhtml='$allowhtmlnew', allowbbcode='$allowbbcodenew',
				allowimgcode='$allowimgcodenew', allowsmilies='$allowsmiliesnew', postcredits='".intval($postcreditsnew)."',
				replycredits='".intval($replycreditsnew)."', password='$passwordnew', viewperm='$viewpermnew',
				postperm='$postpermnew', replyperm='$replypermnew', getattachperm='$getattachpermnew' WHERE fid='$fid'");

			updatecache('forums');
			cpmsg('forums_edit_succeed', 'admincp.php?action=forumsedit');
		}

	}

} elseif($action == 'forumdelete') {

	$query = $db->query("SELECT COUNT(*) FROM $table_forums WHERE fup='$fid'");
	if($db->result($query, 0)) {
		cpmsg('forums_delete_sub_notnull');
	}

	if(!$confirmed) {
		cpmsg('forums_delete_confirm', "admincp.php?action=forumdelete&fid=$fid", 'form');
	} else {
		require DISCUZ_ROOT.'./include/post.php';

		$query = $db->query("SELECT pid FROM $table_posts WHERE aid<>'0' AND fid='$fid'");
		$aid = $comma = '';
		while($post = $db->fetch_array($query)) {
			$aid .= "$comma'$post[aid]'";
			$comma = ',';
		}

		if($aid) {
			$query = $db->query("SELECT filename FROM $table_attachments WHERE aid IN ($aid)");
			while($attach = $db->fetch_array($query)) {
				@unlink(DISCUZ_ROOT."./$attachdir/$attach[filename]");
			}
			$db->query("DELETE FROM $table_attachments WHERE aid IN ($aid)");
		}

		$db->query("DELETE FROM $table_threads WHERE fid='$fid'");
		$db->query("DELETE FROM $table_posts WHERE fid='$fid'");
		$db->query("DELETE FROM $table_forums WHERE fid='$fid'");
		$db->query("DELETE FROM $table_access WHERE fid='$fid'");

		updatecache('forums');
		cpmsg('forums_delete_succeed', 'admincp.php?action=forumsedit');
	}

}

?>