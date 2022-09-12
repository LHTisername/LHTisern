<?php

/*
	[DISCUZ!] admin/attachments.inc.php - edit attachments
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-3-7 21:28
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

$app = 35;	// Attachments per page

if(!submitcheck('deletesubmit') && !submitcheck('searchsubmit')) {
	require_once DISCUZ_ROOT.'./include/forum.func.php';

?>
<br><form method="post" action="admincp.php?action=attachments">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr><td class="header" colspan="2"><?=$lang['attachments_search']?></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_nomatched']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="checkbox" name="nomatched" value="1"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_forum']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><select name="inforum"><option value="all">&nbsp;&nbsp;> <?=$lang['all']?></option>
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

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_keyword']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="keywords" size="40"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['attachments_author']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="author" size="40"></td></tr>

</table><br><center>
<input type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

} elseif(submitcheck('searchsubmit')) {

	require_once DISCUZ_ROOT.'./include/attachment.func.php';

	$sql = "a.pid=p.pid";

	if($inforum != 'all') {
		if($inforum) {
			$sql .= " AND p.fid='$inforum'";
		} else {
			cpmsg('attachments_forum_invalid');
		}
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
	if($keywords) {
		$sqlkeywords = $or = '';
		foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
			$sqlkeywords .= " $or a.description LIKE '%$keyword%'";
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
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
	$query = $db->query("SELECT a.*, p.fid, p.author, t.tid, t.tid, t.subject, f.name AS fname
		FROM {$tablepre}attachments a, {$tablepre}posts p, {$tablepre}threads t, {$tablepre}forums f
		WHERE t.tid=a.tid AND f.fid=p.fid AND t.displayorder>='0' AND p.invisible='0' AND $sql");
	while($attachment = $db->fetch_array($query)) {
		$matched = file_exists($attachdir.'/'.$attachment['attachment']) ? '' : "$lang[attachments_lost]";
		$attachsize = sizecount($attachment['filesize']);
		if(!$nomatched || ($nomatched && $matched)) {
			$attachments .= "<tr><td bgcolor=\"".ALTBG1."\" align=\"center\" valign=\"middle\"><input type=\"checkbox\" name=\"delete[]\" value=\"$attachment[aid]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\" align=\"center\"><b>$attachment[filename]</b><br>$attachment[description]</td>\n".
				"<td bgcolor=\"".ALTBG1."\" align=\"center\"><b>".($matched ? $matched : "<a href=\"attachment.php?aid=$attachment[aid]\" target=\"_blank\">[$lang[attachments_download]]</a>")."</b><br><a href=\"$attachurl/$attachment[attachment]\" class=\"smalltxt\" target=\"_blank\">$attachment[attachment]</a></td>\n".
				"<td bgcolor=\"".ALTBG2."\" align=\"center\">$attachment[author]</td>\n".
				"<td bgcolor=\"".ALTBG1."\" valign=\"middle\"><a href=\"viewthread.php?tid=$attachment[tid]\" target=\"_blank\"><b>".cutstr($attachment['subject'], 18)."</b></a><br>$lang[forum]:<a href=\"forumdisplay.php?fid=$attachment[fid]\" target=\"_blank\">$attachment[fname]</a></td>\n".
				"<td bgcolor=\"".ALTBG2."\" valign=\"middle\" align=\"center\">$attachsize</td>\n".
				"<td bgcolor=\"".ALTBG1."\" valign=\"middle\" align=\"center\">$attachment[downloads]</td></tr>\n";
		}
	}

?>
<br><form method="post" action="admincp.php?action=attachments">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr><td class="header" width="6%" align="center"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td class="header" width="15%" align="center"><?=$lang['attachments_name']?></td>
<td class="header" width="25%" align="center"><?=$lang['filename']?></td>
<td class="header" width="14%" align="center"><?=$lang['author']?></td>
<td class="header" width="23%" align="center"><?=$lang['attachments_thread']?></td>
<td class="header" width="8%" align="center"><?=$lang['size']?></td>
<td class="header" width="8%" align="center"><?=$lang['download']?></td></tr>
<?=$attachments?>
</table><br>
<center><input type="submit" name="deletesubmit" value="<?=$lang['submit']?>"></center></form>
<?

} elseif(submitcheck('deletesubmit')) {

	if(is_array($delete)) {

		$ids = '\''.implode('\',\'', $delete).'\'';

		$tids = $pids = 0;
		$query = $db->query("SELECT tid, pid, attachment FROM {$tablepre}attachments WHERE aid IN ($ids)");
		while($attach = $db->fetch_array($query)) {
			@unlink($attachdir.'/'.$attach['attachment']);
			$tids .= ','.$attach['tid'];
			$pids .= ','.$attach['pid'];
		}
		$db->query("DELETE FROM {$tablepre}attachments WHERE aid IN ($ids)");
		$db->query("UPDATE {$tablepre}posts SET attachment='0' WHERE pid IN ($pids)");

		$attachtids = 0;
		$query = $db->query("SELECT tid, filetype FROM {$tablepre}attachments WHERE tid IN ($tids) GROUP BY tid ORDER BY pid DESC");
		while($attach = $db->fetch_array($query)) {
			$db->query("UPDATE {$tablepre}threads SET attachment='$attach[filetype]' WHERE tid='$attach[tid]'");
			$attachtids .= ','.$attach['tid'];
		}
		$db->query("UPDATE {$tablepre}threads SET attachment='' WHERE tid IN ($tids)".($attachtids ? " AND tid NOT IN ($attachtids)" : NULL));

		cpmsg('attachments_edit_succeed');

	} else {

		cpmsg('attachments_edit_invalid');

	}

}

?>