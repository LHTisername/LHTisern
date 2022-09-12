<?php

/*
	[DISCUZ!] admin/counter.php - rebuild counter of board
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

if(!submitcheck('forumsubmit', 1) && !submitcheck('membersubmit', 1) && !submitcheck('threadsubmit', 1)) {

?>
<br><br><form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="2"><?=$lang['counter_thread']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td align="center"><?=$lang['counter_amount']?> &nbsp; &nbsp; <input type="text" name="pertask" value="500"></td></tr>
</table></td></tr></table><br><center>
<input type="submit" name="threadsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input type="reset" value="<?=$lang['reset']?>"></center></form><br>

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="2"><?=$lang['counter_member']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td align="center"><?=$lang['counter_amount']?> &nbsp; &nbsp; <input type="text" name="pertask" value="1000"></td>
</tr></table></td></tr></table><br><center>
<input type="submit" name="membersubmit" value="<?=$lang['submit']?>"> &nbsp;
<input type="reset" value="<?=$lang['reset']?>"></center></form><br>

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="2"><?=$lang['counter_forum']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td align="center"><?=$lang['counter_amount']?> &nbsp; &nbsp; <input type="text" name="pertask" value="15"></td>
</tr></table></td></tr></table><br><center>
<input type="submit" name="forumsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input type="reset" value="<?=$lang['reset']?>"></center></form><br>
<?

} elseif(submitcheck('forumsubmit', 1)) {

	if(!$current) {
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&forumsubmit=yes";
	$processed = 0;

	$queryf = $db->query("SELECT fid FROM $table_forums WHERE type<>'group' LIMIT $current, $pertask");
	while($forum = $db->fetch_array($queryf)) {
		$processed = 1;

		$query = $db->query("SELECT COUNT(*) FROM $table_threads WHERE fid='$forum[fid]'");
		$threadnum = $db->result($query, 0);
		$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE fid='$forum[fid]'");
		$postnum = $db->result($query, 0);

		$query = $db->query("SELECT subject, lastpost, lastposter FROM $table_threads WHERE fid='$forum[fid]' ORDER BY lastpost DESC LIMIT 1");
		$thread = $db->fetch_array($query);
		$lastpost = addslashes("$thread[subject]\t$thread[lastpost]\t$thread[lastposter]");

		$db->query("UPDATE $table_forums SET threads='$threadnum', posts='$postnum', lastpost='$lastpost' WHERE fid='$forum[fid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_forum]: $lang[counter_processing]", $nextlink);
	} else {
		$db->query("UPDATE $table_forums SET threads='0', posts='0' WHERE type='group'");
		cpmsg('counter_forum_succeed');
	}

} elseif(submitcheck('threadsubmit', 1)) {

	if(!$current) {
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&threadsubmit=yes";
	$processed = 0;

	$queryt = $db->query("SELECT tid FROM $table_threads LIMIT $current, $pertask");
	while($threads = $db->fetch_array($queryt)) {
		$processed = 1;
		$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$threads[tid]'");
		$replynum = $db->result($query, 0);
		$replynum--;
		$db->query("UPDATE $table_threads SET replies='$replynum' WHERE tid='$threads[tid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_thread]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_thread_succeed');
	}

} elseif(submitcheck('membersubmit', 1)) {

	if(!$current) {
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&membersubmit=yes";
	$processed = 0;

	$queryt = $db->query("SELECT uid FROM $table_members LIMIT $current, $pertask");
	while($mem = $db->fetch_array($queryt)) {
		$processed = 1;
		$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE authorid='$mem[uid]'");
		$db->query("UPDATE $table_members SET postnum='".$db->result($query, 0)."' WHERE uid='$mem[uid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_member]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_member_succeed');
	}
}

?>