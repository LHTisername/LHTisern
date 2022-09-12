<?php

/*
	[DISCUZ!] admin/announcements.inc.php - make or delete announcements
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-3-10 9:43
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'announcements') {

	if(!submitcheck('announcesubmit') && !submitcheck('addsubmit') && !$edit) {

		$announcements = '';
		$query = $db->query("SELECT * FROM {$tablepre}announcements ORDER BY displayorder, starttime DESC, id DESC");
		while($announce = $db->fetch_array($query)) {
			$disabled = $adminid != 1 && $announce['author'] != $discuz_userss ? 'disabled' : NULL;
			$announce['starttime'] = $announce['starttime'] ? gmdate($dateformat, $announce['starttime'] + $timeoffset * 3600) : $lang['unlimited'];
			$announce['endtime'] = $announce['endtime'] ? gmdate($dateformat, $announce['endtime'] + $timeoffset * 3600) : $lang['unlimited'];
			$announcements .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$announce[id]\" $disabled></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><a href=\"./viewpro.php?username=".rawurlencode($announce['author'])."\" target=\"_blank\">$announce[author]</a></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><a href=\"admincp.php?action=announcements&edit=$announce[id]\" $disabled>".dhtmlspecialchars($announce['subject'])."</a></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><a href=\"admincp.php?action=announcements&edit=$announce[id]\">".cutstr(strip_tags($announce['message']), 20)."</a></td>\n".
				"<td bgcolor=\"".ALTBG1."\">$announce[starttime]</td>\n".
				"<td bgcolor=\"".ALTBG2."\">$announce[endtime]</td>\n".
				"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"2\" name=\"displayordernew[$announce[id]]\" value=\"$announce[displayorder]\" $disabled></td></tr>\n";
		}
		$newstarttime = gmdate('Y-n-j', $timestamp + $timeoffset * 3600);

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['announce_tips']?>
</td></tr></table>

<br><form method="post" action="admincp.php?action=announcements">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td colspan="7"><?=$lang['announce_edit']?></td></tr>
<tr align="center" class="category">
<td width="48"><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['author']?></td><td><?=$lang['subject']?></td><td><?=$lang['message']?></td><td><?=$lang['start_time']?></td><td><?=$lang['end_time']?></td><td><?=$lang['display_order']?></td></tr>
<?=$announcements?></table><br><center>
<input type="submit" name="announcesubmit" value="<?=$lang['submit']?>"></center></form>

<br><form method="post" action="admincp.php?action=announcements">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['announce_add']?></td></tr>

<tr><td width="21%" bgcolor="<?=ALTBG1?>"><b><?=$lang['subject']?>:</b></td>
<td width="79%" bgcolor="<?=ALTBG2?>"><input type="text" size="45" name="newsubject"></td></tr>

<tr><td width="21%" bgcolor="<?=ALTBG1?>"><b><?=$lang['start_time']?>:</b><br><?=$lang['announce_time_comment']?></td>
<td width="79%" bgcolor="<?=ALTBG2?>"><input type="text" size="45" name="newstarttime" value="<?=$newstarttime?>"></td></tr>

<tr><td width="21%" bgcolor="<?=ALTBG1?>"><b><?=$lang['end_time']?>:</b><br><?=$lang['announce_time_comment']?></td>
<td width="79%" bgcolor="<?=ALTBG2?>"><input type="text" size="45" name="newendtime"> <?=$lang['announce_end_time_comment']?></td></tr>

<tr><td width="21%" bgcolor="<?=ALTBG1?>" valign="top"><b><?=$lang['message']?>:</b><br><?=$lang['announce_message_comment']?></td>
<td width="79%" bgcolor="<?=ALTBG2?>"><textarea name="newmessage" cols="60" rows="10"></textarea></td></tr>

</table><br><center><input type="submit" name="addsubmit" value="<?=$lang['submit']?>">
</form>
<?

	} elseif($edit) {

		$query = $db->query("SELECT * FROM {$tablepre}announcements WHERE id='$edit' AND ('$adminid'='1' OR author='$discuz_user')");
		if(!$announce = $db->fetch_array($query)) {
			cpmsg('announce_nonexistence');
		}

		if(!submitcheck('editsubmit')) {

			$announce['starttime'] = $announce['starttime'] ? gmdate('Y-n-j', $announce['starttime'] + $timeoffset * 3600) : "";
			$announce['endtime'] = $announce['endtime'] ? gmdate('Y-n-j', $announce['endtime'] + $timeoffset * 3600) : "";

?>
<br><form method="post" action="admincp.php?action=announcements&edit=<?=$edit?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['announce_edit']?></td></tr>

<tr><td width="21%" bgcolor="<?=ALTBG1?>"><b><?=$lang['subject']?>:</b></td>
<td width="79%" bgcolor="<?=ALTBG2?>"><input type="text" size="45" name="subjectnew" value="<?=dhtmlspecialchars($announce['subject'])?>"></td></tr>

<tr><td width="21%" bgcolor="<?=ALTBG1?>"><b><?=$lang['start_time']?>:</b><br><?=$lang['announce_time_comment']?></td>
<td width="79%" bgcolor="<?=ALTBG2?>"><input type="text" size="45" name="starttimenew" value="<?=$announce[starttime]?>"></td></tr>

<tr><td width="21%" bgcolor="<?=ALTBG1?>"><b><?=$lang['end_time']?>:</b><br><?=$lang['announce_time_comment']?></td>
<td width="79%" bgcolor="<?=ALTBG2?>"><input type="text" size="45" name="endtimenew" value="<?=$announce[endtime]?>"> <?=$lang['announce_end_time_comment']?></td></tr>

<tr><td width="21%" bgcolor="<?=ALTBG1?>" valign="top"><b><?=$lang['message']?>:</b><br><?=$lang['announce_message_comment']?></td>
<td width="79%" bgcolor="<?=ALTBG2?>"><textarea name="messagenew" cols="60" rows="10"><?=dhtmlspecialchars($announce['message'])?></textarea></td></tr>

</table><br><center><input type="submit" name="editsubmit" value="<?=$lang['submit']?>">
</form>
<?

		} else {

			if(strpos($starttimenew, '-')) {
				$time = explode('-', $starttimenew);
				$starttimenew = gmmktime(0, 0, 0, $time[1], $time[2], $time[0]) - $timeoffset * 3600;
			} else {
				$starttimenew = 0;
			}
			if(strpos($endtimenew, '-')) {
				$time = explode('-', $endtimenew);
				$endtimenew = gmmktime(0, 0, 0, $time[1], $time[2], $time[0]) - $timeoffset * 3600;
			} else {
				$endtimenew = 0;
			}

			if(!$starttimenew) {
				cpmsg('announce_start_time_invalid');
			} elseif(!trim($subjectnew) || !trim($messagenew)) {
				cpmsg('announce_invalid');
			} else {
				$db->query("UPDATE {$tablepre}announcements SET subject='$subjectnew', starttime='$starttimenew', endtime='$endtimenew', message='$messagenew' WHERE id='$edit'");
				updatecache('announcements');
				updatecache('announcements_forum');
				cpmsg('announce_succeed', 'admincp.php?action=announcements');
			}
		}

	} elseif(submitcheck('announcesubmit')) {

		if(is_array($delete)) {
			$ids = $comma = '';
			foreach($delete as $id) {
				$ids .= "$comma'$id'";
				$comma = ',';
			}
			$db->query("DELETE FROM {$tablepre}announcements WHERE id IN ($ids) AND ('$adminid'='1' OR author='$discuz_user')");
		}

		if(is_array($displayordernew)) {
			foreach($displayordernew as $id => $displayorder) {
				$db->query("UPDATE {$tablepre}announcements SET displayorder='$displayorder' WHERE id='$id' AND ('$adminid'='1' OR author='$discuz_user')");
			}
		}

		updatecache('announcements');
		updatecache('announcements_forum');
		cpmsg('announce_update_succeed', 'admincp.php?action=announcements');

	} elseif(submitcheck('addsubmit')) {

		//$newsubject = dhtmlspecialchars($newsubject);
		$newstarttime = $newstarttime ? strtotime($newstarttime) : 0;
		$newendtime = $newendtime ? strtotime($newendtime) : 0;

		if(!$newstarttime) {
			cpmsg('announce_start_time_invalid');
		} elseif(!trim($newsubject) || !trim($newmessage)) {
			cpmsg('announce_invalid');
		} else {
			$db->query("INSERT INTO {$tablepre}announcements (author, subject, starttime, endtime, message)
				VALUES ('$discuz_user', '$newsubject', '$newstarttime', '$newendtime', '$newmessage')");
			updatecache('announcements');
			updatecache('announcements_forum');
			cpmsg('announce_succeed', 'admincp.php?action=announcements');
		}
	}

}

?>