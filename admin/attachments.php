<?php

/*
	[DISCUZ!] admin/attachments.php - edit attachments
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

$app = 35;

if(!submitcheck('deletesubmit') && !submitcheck('searchsubmit')) {
	require DISCUZ_ROOT.'./include/forum.php';

?>
<br><form method="post" action="admincp.php?action=attachments">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="95%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">

<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr><td class="header" colspan="2"><?=$lang['attachments_search']?></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_nomatched']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="checkbox" name="nomatched" value="1"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_forum']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><select name="forum"><option value="all">&nbsp;&nbsp;> <?=$lang['all']?></option>
<option value="">&nbsp;</option><?=forumselect()?></select></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_sizeless']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="sizeless" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_sizemore']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="sizemore" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_dlcountless']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="dlcountless" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_dlcountmore']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="dlcountmore" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_daysold']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="daysold" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_filename']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="filename" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_author']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="author" size="40"></td></tr>

</table></td></tr></table><br><center>
<input type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

} elseif(submitcheck('searchsubmit')) {

	require DISCUZ_ROOT.'./include/attachment.php';

	$sql = "a.pid=p.pid";

	if($forum && $forum != 'all') {
		$sql .= " AND p.fid='$forum'";
	} elseif($forum != 'all') {
		cpmsg('attachments_forum_invalid');
	}
	if($daysold) {
		$sql .= " AND p.dateline<='".($timestamp - (86400 * $daysold))."'";
	}
	if($author) {
		$sql .= " AND p.author='$author'";
	}
	if($filename) {
		$sql .= " AND a.filename LIKE '%$filename%'";
	}
	if($sizeless) {
		$sql .= " AND a.filesize<'$sizeless'";
	}
	if($sizemore) {
		$sql .= " AND a.filesize>'$sizemore' ";
	}
	if($dlcountless) {
		$sql .= " AND a.downloads<'$dlcountless'";
	}
	if($dlcountmore) {
		$sql .= " AND a.downloads>'$dlcountmore'";
	}

	$attachments = '';
	$query = $db->query("SELECT a.*, p.fid, p.author, t.tid, t.tid, t.subject, f.name AS fname FROM $table_attachments a, $table_posts p, $table_threads t, $table_forums f WHERE t.tid=a.tid AND f.fid=p.fid AND $sql");
	while($attachment = $db->fetch_array($query)) {
		$matched = file_exists(DISCUZ_ROOT."./$attachdir/$attachment[attachment]") ? NULL : "<b>$lang[attachments_lost]</b><br>";
		$attachsize = sizecount($attachment['filesize']);
		if(!$nomatched || ($nomatched && $matched)) {
			$attachments .= "<tr><td bgcolor=\"".ALTBG1."\" width=\"45\" align=\"center\" valign=\"middle\"><input type=\"checkbox\" name=\"delete[]\" value=\"$attachment[aid]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\" align=\"center\" width=\"20%\"><b>$attachment[filename]</b><br><a href=\"attachment.php?aid=$attachment[aid]\" target=\"_blank\">[$lang[attachments_download]]</a></td>\n".
				"<td bgcolor=\"".ALTBG1."\" align=\"center\" width=\"20%\">$matched<a href=\"$attachurl/$attachment[attachment]\" class=\"smalltxt\" target=\"_blank\">$attachment[attachment]</a></td>\n".
				"<td bgcolor=\"".ALTBG2."\" align=\"center\" width=\"8%\">$attachment[author]</td>\n".
				"<td bgcolor=\"".ALTBG1."\" valign=\"middle\" width=\"25%\"><a href=\"viewthread.php?tid=$attachment[tid]\" target=\"_blank\"><b>".cutstr($attachment['subject'], 18)."</b></a><br>$lang[forum]:<a href=\"forumdisplay.php?fid=$attachment[fid]\" target=\"_blank\">$attachment[fname]</a></td>\n".
				"<td bgcolor=\"".ALTBG2."\" valign=\"middle\" width=\"18%\" align=\"center\">$attachsize</td>\n".
				"<td bgcolor=\"".ALTBG1."\" valign=\"middle\" width=\"7%\" align=\"center\">$attachment[downloads]</td></tr>\n";
		}
	}

?>
<br><form method="post" action="admincp.php?action=attachments">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="95%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%" style="table-layout: fixed;word-break: break-all">
<tr><td class="header" width="6%" align="center"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td class="header" width="15%" align="center"><?=$lang['attachments_name']?></td>
<td class="header" width="25%" align="center"><?=$lang['attachments_filename']?></td>
<td class="header" width="14%" align="center"><?=$lang['author']?></td>
<td class="header" width="23%" align="center"><?=$lang['attachments_thread']?></td>
<td class="header" width="8%" align="center"><?=$lang['size']?></td>
<td class="header" width="8%" align="center"><?=$lang['download']?></td></tr>
<?=$attachments?>
</table></td></tr>
</table><br>
<center><input type="submit" name="deletesubmit" value="<?=$lang['submit']?>"></center></form>
<?

} elseif(submitcheck('deletesubmit')) {

	if(is_array($delete)) {
		$ids = $comma = '';
		foreach($delete as $aid) {
			$ids .= "$comma'$aid'";
			$comma = ',';
		}

		$tids = $pids = $comma1 = $comma2 = '';
		$query = $db->query("SELECT tid, pid, attachment FROM $table_attachments WHERE aid IN ($ids)");
		while($attach = $db->fetch_array($query)) {
			@unlink("$attachdir/$attach[attachment]");
			$tids .= "$comma1'$attach[tid]'";
			$comma1 = ',';
			$pids .= "$comma2'$attach[pid]'";
			$comma2 = ',';
		}
		$db->query("DELETE FROM $table_attachments WHERE aid IN ($ids)");
		$db->query("UPDATE $table_posts SET aid='0' WHERE pid IN ($pids)");

		$attachtids = $comma = '';
		$query = $db->query("SELECT tid, filetype FROM $table_attachments WHERE tid IN ($tids) GROUP BY tid ORDER BY pid DESC");
		while($attach = $db->fetch_array($query)) {
			$db->query("UPDATE $table_threads SET attachment='$attach[filetype]' WHERE tid='$attach[tid]'");

			$attachtids .= "$comma'$attach[tid]'";
			$comma = ',';
		}
		$db->query("UPDATE $table_threads SET attachment='' WHERE tid IN ($tids)".($attachtids ? " AND tid NOT IN ($attachtids)" : NULL));

		cpmsg('attachments_edit_succeed');
	} else {
		cpmsg('attachments_edit_invalid');
	}
}

?>