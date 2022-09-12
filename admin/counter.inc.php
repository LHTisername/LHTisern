<?php

/*
	[DISCUZ!] admin/counter.inc.php - rebuild counter of board
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2004/11/9 12:01
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if(!submitcheck('forumsubmit', 1) && !submitcheck('digestsubmit', 1) && !submitcheck('membersubmit', 1) && !submitcheck('threadsubmit', 1)) {

?>
<br><br>
<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="60%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_forum']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td align="center"><?=$lang['counter_amount']?> &nbsp; &nbsp; <input type="text" name="pertask" value="15"></td>
</tr></table><br><center>
<input type="submit" name="forumsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input type="reset" value="<?=$lang['reset']?>"></center></form><br>

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="60%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_digest']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td align="center"><?=$lang['counter_amount']?> &nbsp; &nbsp; <input type="text" name="pertask" value="1000"></td>
</tr></table><br><center>
<input type="submit" name="digestsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input type="reset" value="<?=$lang['reset']?>"></center></form><br>

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="60%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_member']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td align="center"><?=$lang['counter_amount']?> &nbsp; &nbsp; <input type="text" name="pertask" value="1000"></td>
</tr></table><br><center>
<input type="submit" name="membersubmit" value="<?=$lang['submit']?>"> &nbsp;
<input type="reset" value="<?=$lang['reset']?>"></center></form><br>

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="60%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_thread']?></td></tr>
<tr bgcolor="<?=ALTBG2?>">
<td align="center"><?=$lang['counter_amount']?> &nbsp; &nbsp; <input type="text" name="pertask" value="500"></td></tr>
</table><br><center>
<input type="submit" name="threadsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input type="reset" value="<?=$lang['reset']?>"></center></form>
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

	$queryf = $db->query("SELECT fid FROM {$tablepre}forums WHERE type<>'group' LIMIT $current, $pertask");
	while($forum = $db->fetch_array($queryf)) {
		$processed = 1;

		$query = $db->query("SELECT COUNT(*) AS threads, SUM(replies)+COUNT(*) AS posts FROM {$tablepre}threads WHERE fid='$forum[fid]' AND displayorder>='0'");
		extract($db->fetch_array($query));

		$query = $db->query("SELECT tid, subject, lastpost, lastposter FROM {$tablepre}threads WHERE fid='$forum[fid]' AND displayorder>='0' ORDER BY lastpost DESC LIMIT 1");
		$thread = $db->fetch_array($query);
		$lastpost = addslashes("$thread[tid]\t$thread[subject]\t$thread[lastpost]\t$thread[lastposter]");

		$db->query("UPDATE {$tablepre}forums SET threads='$threads', posts='$posts', lastpost='$lastpost' WHERE fid='$forum[fid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_forum]: $lang[counter_processing]", $nextlink);
	} else {
		$db->query("UPDATE {$tablepre}forums SET threads='0', posts='0' WHERE type='group'");
		cpmsg('counter_forum_succeed');
	}

} elseif(submitcheck('digestsubmit', 1)) {

	if(!$current) {
		$db->query("UPDATE {$tablepre}members SET digestposts=0", 'UNBUFFERED');
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&digestsubmit=yes";
	$processed = 0;
	$membersarray = $postsarray = array();

	$query = $db->query("SELECT authorid FROM {$tablepre}threads WHERE digest<>'0' AND displayorder>='0' LIMIT $current, $pertask");
	while($thread = $db->fetch_array($query)) {
		$processed = 1;
		$membersarray[$thread['authorid']]++;
	}

	foreach($membersarray as $uid => $posts) {
		$postsarray[$posts] .= ','.$uid;
	}
	unset($membersarray);

	foreach($postsarray as $posts => $uids) {
		$db->query("UPDATE {$tablepre}members SET digestposts=digestposts+'$posts' WHERE uid IN (0$uids)", 'UNBUFFERED');
	}

	if($processed) {
		cpmsg("$lang[counter_digest]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_digest_succeed');
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

	$queryt = $db->query("SELECT uid FROM {$tablepre}members LIMIT $current, $pertask");
	while($mem = $db->fetch_array($queryt)) {
		$processed = 1;
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE authorid='$mem[uid]' AND invisible='0'");
		$db->query("UPDATE {$tablepre}members SET posts='".$db->result($query, 0)."' WHERE uid='$mem[uid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_member]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_member_succeed');
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

	$queryt = $db->query("SELECT tid FROM {$tablepre}threads WHERE displayorder>='0' LIMIT $current, $pertask");
	while($threads = $db->fetch_array($queryt)) {
		$processed = 1;
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE tid='$threads[tid]' AND invisible='0'");
		$replynum = $db->result($query, 0);
		$replynum--;
		$db->query("UPDATE {$tablepre}threads SET replies='$replynum' WHERE tid='$threads[tid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_thread]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_thread_succeed');
	}

}

?>