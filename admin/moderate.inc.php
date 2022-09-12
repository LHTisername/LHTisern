<?php

/*
	[DISCUZ!] moderate.inc.php - moderation of new threads and new replies
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-2-25 23:21
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

cpheader();

if($action == 'modmembers') {

	//Table validating status: 0=Awaiting for moderation; 1=Invalidated; 2=Validated;
	if(!submitcheck('modsubmit') && !submitcheck('prunesubmit', 1)) {

		$query = $db->query("SELECT status, COUNT(*) AS count FROM {$tablepre}validating GROUP BY status");
		while($num = $db->fetch_array($query)) {
			$count[$num['status']] = $num['count'];
		}

		$sendemail = isset($sendemail) ? $sendemail : 1;
		$checksendemail = $sendemail ? 'checked' : '';

		$page = !ispage($page) ? 1 : $page;
		$start_limit = ($page - 1) * $memberperpage;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}validating WHERE status='0'");
		$multipage = multi($db->result($query, 0), $memberperpage, $page, 'admincp.php?action=modmembers&sendemail=$sendemail');

		$vuids = '0';
		$members = '';
		$query = $db->query("SELECT m.uid, m.username, m.groupid, m.email, m.regdate, m.regip, v.message, v.submittimes, v.submitdate, v.moddate, v.admin, v.remark
			FROM {$tablepre}validating v, {$tablepre}members m
			WHERE v.status='0' AND m.uid=v.uid ORDER BY v.submitdate DESC LIMIT $start_limit, $memberperpage");
		while($member = $db->fetch_array($query)) {
			if($member['groupid'] != 8) {
				$vuids .= ','.$member['uid'];
				continue;
			}
			$member['regdate'] = gmdate("$dateformat $timeformat", $member['regdate'] + $timeoffset * 3600);
			$member['submitdate'] = gmdate("$dateformat $timeformat", $member['submitdate'] + $timeoffset * 3600);
			$member['moddate'] = $member['moddate'] ? gmdate("$dateformat $timeformat", $member['moddate'] + $timeoffset * 3600) : $lang['none'];
			$member['admin'] = $member['admin'] ? "<a href=\"viewpro.php?username=".rawurlencode($member['admin'])."\" target=\"_blank\">$member[admin]</a>" : $lang['none'];
			$members .= "<tr class=\"smalltxt\"><td bgcolor=\"".ALTBG2."\"><input type=\"radio\" name=\"mod[$member[uid]]\" value=\"invalidate\"> $lang[invalidate]<br><input type=\"radio\" name=\"mod[$member[uid]]\" value=\"validate\" checked> $lang[validate]<br>\n".
				"<input type=\"radio\" name=\"mod[$member[uid]]\" value=\"delete\"> $lang[delete]<br><input type=\"radio\" name=\"mod[$member[uid]]\" value=\"ignore\"> $lang[ignore]</td><td bgcolor=\"".ALTBG1."\"><b><a href=\"viewpro.php?uid=$member[uid]\" target=\"_blank\">$member[username]</a></b>\n".
				"<br>$lang[members_edit_regdate] $member[regdate]<br>$lang[members_edit_regip] $member[regip]<br>Email: $member[email]</td>\n".
				"<td bgcolor=\"".ALTBG2."\" align=\"center\"><textarea rows=\"4\" name=\"remark[$member[uid]]\" style=\"width: 100%; word-break: break-all\">$member[message]</textarea></td>\n".
				"<td bgcolor=\"".ALTBG1."\">$lang[moderate_members_submit_times]: $member[submittimes]<br>$lang[moderate_members_submit_time]: $member[submitdate]<br>$lang[moderate_members_admin]: $member[admin]<br>\n".
				"$lang[moderate_members_mod_time]: $member[moddate]</td><td bgcolor=\"".ALTBG1."\"><textarea rows=\"4\" name=\"remark[$member[uid]]\" style=\"width: 100%; word-break: break-all\">$member[remark]</textarea></td></tr>\n";
		}

		if($vuids) {
			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($vuids)", 'UNBUFFERED');
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['moderate_members_tips']?>
</td></tr></table><br>

<form method="post" action="admincp.php?action=modmembers">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['moderate_members_prune']?></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['moderate_members_prune_submitmore']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="submitmore" size="40" value="5"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['moderate_members_prune_regbefore']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="regbefore" size="40" value="30"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['moderate_members_prune_modbefore']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="modbefore" size="40" value="15"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['moderate_members_prune_regip']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="regip" size="40"></td></tr>

</table><br>
<center><input type="submit" name="prunesubmit" value="<?=$lang['submit']?>"></center>
</form>

<form method="post" action="admincp.php?action=modmembers">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header"><td colspan="5"><?=$lang['moderate_members']?></td></tr>
<tr class="category"><td colspan="5">
<table cellspacing="0" cellpadding="0" width="100%"><tr><td><input type="button" value="<?=$lang['moderate_all_invalidate']?>" onclick="checkalloption(this.form, 'invalidate')"> &nbsp;
<input type="button" value="<?=$lang['moderate_all_validate']?>" onclick="checkalloption(this.form, 'validate')"> &nbsp;
<input type="button" value="<?=$lang['moderate_all_delete']?>" onclick="checkalloption(this.form, 'delete')"> &nbsp;
<input type="button" value="<?=$lang['moderate_all_ignore']?>" onclick="checkalloption(this.form, 'ignore')">
</td><td align="right"><input type="checkbox" name="sendemail" value="1" <?=$checksendemail?>> <?=$lang['moderate_members_email']?></td></tr></table>
</td></tr><tr><td colspan="5" class="singleborder">&nbsp;</td></tr>
<tr align="center" class="header"><td width="10%"><?=$lang['operation']?></td><td width="25%"><?=$lang['members_edit_info']?></td><td width="20%"><?=$lang['moderate_members_message']?></td><td width="25%"><?=$lang['moderate_members_info']?></td><td width="20%"><?=$lang['moderate_members_remark']?></td></tr>
<?=$members?>
</table>

<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table><br><center>
<input type="submit" name="modsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} elseif(submitcheck('modsubmit')) {

		$moderation = array('invalidate' => array(), 'validate' => array(), 'delete' => array(), 'ignore' => array());

		$uids = 0;
		if(is_array($mod)) {
			foreach($mod as $uid => $action) {
				$uid = intval($uid);
				$uids .= ','.$uid;
				$moderation[$action][] = $uid;
			}
		}

		$members = array();
		$uidarray = array(0);
		$query = $db->query("SELECT v.*, m.uid, m.username, m.email, m.regdate FROM {$tablepre}validating v, {$tablepre}members m
			WHERE v.uid IN ($uids) AND m.uid=v.uid AND m.groupid='8'");
		while($member = $db->fetch_array($query)) {
			$members[$member['uid']] = $member;
			$uidarray[] = $member['uid'];
		}

		$uids = implode(',', $uidarray);
		$numdeleted = $numinvalidated = $numvalidated = 0;

		if(!empty($moderation['delete']) && is_array($moderation['delete'])) {
			$deleteuids = '\''.implode('\',\'', $moderation['delete']).'\'';
			$db->query("DELETE FROM {$tablepre}members WHERE uid IN ($deleteuids) AND uid IN ($uids)");
			$numdeleted = $db->affected_rows();

			$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($deleteuids) AND uid IN ($uids)");
			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($deleteuids) AND uid IN ($uids)");
		} else {
			$moderation['delete'] = array();
		}

		if(!empty($moderation['validate']) && is_array($moderation['validate'])) {
			$query = $db->query("SELECT groupid FROM {$tablepre}usergroups WHERE creditshigher<=0 AND 0<creditslower LIMIT 1");
			$newgroupid = $db->result($query, 0);
			$validateuids = '\''.implode('\',\'', $moderation['validate']).'\'';
			$db->query("UPDATE {$tablepre}members SET adminid='0', groupid='$newgroupid' WHERE uid IN ($validateuids) AND uid IN ($uids)");
			$numvalidated = $db->affected_rows();

			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($validateuids) AND uid IN ($uids)");
		} else {
			$moderation['validate'] = array();
		}

		if(!empty($moderation['invalidate']) && is_array($moderation['invalidate'])) {
			foreach($moderation['invalidate'] as $uid) {
				$numinvalidated++;
				$db->query("UPDATE {$tablepre}validating SET moddate='$timestamp', admin='$discuz_user', status='1', remark='".dhtmlspecialchars($remark[$uid])."' WHERE uid='$uid' AND uid IN ($uids)");
			}
		} else {
			$moderation['invalidate'] = array();
		}

		if($sendemail) {
			foreach(array('delete', 'validate', 'invalidate') as $operation) {
				foreach($moderation[$operation] as $uid) {
					if(isset($members[$uid])) {
						$member = $members[$uid];
						$member['regdate'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $member['regdate'] + $_DCACHE['settings']['timeoffset'] * 3600);
						$member['submitdate'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $member['submitdate'] + $_DCACHE['settings']['timeoffset'] * 3600);
						$member['moddate'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);
						$member['operation'] = $lang[$operation];
						$member['remark'] = $remark[$uid] ? dhtmlspecialchars($remark[$uid]) : $lang['none'];

						sendmail($member['email'], 'moderate_member_subject', 'moderate_member_message');
					}
				}
			}
		}

		cpmsg('moderate_members_succeed', "admincp.php?action=modmembers&page=$page");

	} elseif(submitcheck('prunesubmit', 1)) {

		$sql = '1';
		$sql .= $submitmore ? " AND v.submittimes>'$submitmore'" : '';
		$sql .= $regbefore ? " AND m.regdate<'".($timestamp - $regbefore * 86400)."'" : '';
		$sql .= $modbefore ? " AND v.moddate<'".($timestamp - $modbefore * 86400)."'" : '';
		$sql .= $regip ? " AND m.regip LIKE '$regip%'" : '';

		$query = $db->query("SELECT v.uid FROM {$tablepre}validating v, {$tablepre}members m
			WHERE $sql AND m.uid=v.uid AND m.groupid='8'");

		$membernum = $db->num_rows($query);

		if(!$confirmed) {
			cpmsg('members_delete_confirm', "admincp.php?action=modmembers&submitmore=".rawurlencode($submitmore)."&regbefore=".rawurlencode($regbefore)."&regip=".rawurlencode($regip)."&prunesubmit=yes", 'form');
		} else {
			$uids = 0;
			while($member = $db->fetch_array($query)) {
				$uids .= ','.$member['uid'];
			}

			$db->query("DELETE FROM {$tablepre}members WHERE uid IN ($uids)");
			$numdeleted = $db->affected_rows();

			$db->query("DELETE FROM {$tablepre}memberfields WHERE uid IN ($uids)");
			$db->query("DELETE FROM {$tablepre}validating WHERE uid IN ($uids)");

			cpmsg('members_delete_succeed');
		}
	}

} else {

	require_once DISCUZ_ROOT.'./include/forum.func.php';
	require_once DISCUZ_ROOT.'./include/post.func.php';

	$fids = 0;
	if($adminid == 3) {
		$query = $db->query("SELECT fid FROM {$tablepre}moderators WHERE uid='$discuz_uid'");
		while($forum = $db->fetch_array($query)) {
			$fids .= ','.$forum['fid'];
		}
	}
	$fidadd = $fids ? array('fids' => "fid IN ($fids)", 'and' => ' AND ', 't' => 't.', 'p' => 'p.') : array();

}

if($action == 'modthreads') {

	if(!submitcheck('modsubmit')) {

		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

		$page = !ispage($page) ? 1 : $page;
		$start_limit = ($page - 1) * 20;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}threads WHERE $fidadd[fids]$fidadd[and] displayorder='-2'");
		$multipage = multi($db->result($query, 0), $tpp, $page, 'admincp.php?action=modthreads');

		$threads = '';
		$query = $db->query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
			t.tid, t.fid, t.author, t.authorid, t.subject, t.dateline, t.attachment, 
			p.pid, p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff
			FROM {$tablepre}threads t
			LEFT JOIN {$tablepre}posts p ON p.tid=t.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
			WHERE $fidadd[t]$fidadd[fids]$fidadd[and] t.displayorder='-2'
			ORDER BY t.dateline DESC LIMIT $start_limit, 20");

		while($thread = $db->fetch_array($query)) {
			$thread['author'] = $thread['authorid'] ? "<a href=\"viewpro.php?uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>" : $lang['guest'];
			$thread['dateline'] = gmdate("$dateformat\<\b\\r\>$timeformat", $thread['dateline'] + $timeoffset * 3600);
			$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff'], $thread['htmlon'], $thread['allowsmilies'], $thread['allowbbcode'], $thread['allowimgcode'], $thread['allowhtml']);

			$thisbg = $thisbg == ALTBG2 ? ALTBG1 : ALTBG2;
			$threads .= "<tr><td colspan=\"2\" class=\"singleborder\">&nbsp;</td></tr><tr bgcolor=\"$thisbg\"><td rowspan=\"2\" valign=\"top\" width=\"15%\" height=\"100%\">\n".
				"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" height=\"100%\">\n".
				"<tr><td valign=\"top\"><a href=\"viewpro.php?uid=$thread[authorid]\" target=\"_blank\"><b>$thread[author]</b><br>$thread[useip]</td></tr><tr><td valign=\"bottom\" class=\"smalltxt\">\n".
				"<input type=\"radio\" name=\"mod[$thread[tid]]\" value=\"validate\" checked> $lang[validate]<br>\n".
				"<input type=\"radio\" name=\"mod[$thread[tid]]\" value=\"delete\"> $lang[delete]<br>\n".
				"<input type=\"radio\" name=\"mod[$thread[tid]]\" value=\"ignore\"> $lang[ignore]<br><br>\n".
				"$thread[dateline]</td></tr></table></td><td><a href=\"forumdisplay.php?fid=$thread[fid]\" target=\"_blank\">$thread[forumname]</a> <b>&raquo;</b>\n".
				"<b>$thread[subject]</b></td></tr><tr bgcolor=\"$thisbg\"><td><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"table-layout: fixed\"><tr><td>\n".
				"<div style=\"border-style: dotted; border-width: 1; border-color: ".BORDERCOLOR."; padding: 5; overflow: auto; overflow-y: scroll; width: 100%; height:180px\">$thread[message]";

			if($thread['attachment']) {
				require_once DISCUZ_ROOT.'./include/attachment.func.php';

				$queryattach = $db->query("SELECT aid, filename, filetype, filesize FROM {$tablepre}attachments WHERE tid='$thread[tid]'");
				while($attach = $db->fetch_array($queryattach)) {
					$threads .= "<br><br>$lang[attachment]: ".attachtype(fileext($thread['filename'])."\t".$attach['filetype']).
						" $attach[filename] (".sizecount($attach['filesize']).")";
				}
			}
			$threads .= "</div></td></tr></table></td></tr>\n";
		}

		$threads = $threads ? $threads : '<tr><td colspan="2" bgcolor="'.ALTBG1.'"><a href="admincp.php?action=modreplies">'.$lang['moderate_threads_none'].'</a></td></tr>';

?>
<form method="post" action="admincp.php?action=modthreads&page=<?=$page?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" width="95%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr><td colspan="2" class="header"><?=$lang['moderate_threads']?></td></tr>
<tr><td colspan="2" class="category">
<input type="button" value="<?=$lang['moderate_all_validate']?>" onclick="checkalloption(this.form, 'validate')"> &nbsp;
<input type="button" value="<?=$lang['moderate_all_delete']?>" onclick="checkalloption(this.form, 'delete')"> &nbsp;
<input type="button" value="<?=$lang['moderate_all_ignore']?>" onclick="checkalloption(this.form, 'ignore')"></td></tr>
<?=$threads?>
</table>
<table cellspacing="0" cellpadding="0" width="95%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>
<br><center><input type="submit" name="modsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		if(is_array($mod)) {
			foreach($mod as $tid => $action) {
				$moderation[$action][] = intval($tid);
			}
		}

		$threadsmod = 0;

		if($moderation['delete']) {
			$deletetids = '0';
			$query = $db->query("SELECT tid FROM {$tablepre}threads WHERE tid IN ('".implode('\',\'', $moderation['delete'])."') $fidadd[and]$fidadd[fids]");
			while($thread = $db->fetch_array($query)) {
				$deletetids .= ','.$thread['tid'];
			}

			$query = $db->query("SELECT attachment FROM {$tablepre}attachments WHERE tid IN ($deletetids)");
			while($attach = $db->fetch_array($query)) {
				@unlink($attachdir.'/'.$attach['attachment']);
			}

			$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}posts WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}polls WHERE tid IN ($deletetids)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($deletetids)", 'UNBUFFERED');
		}

		if($moderation['validate']) {
			$forums = array();
			$validatetids = '\''.implode('\',\'', $moderation['validate']).'\'';

			$tids = $comma = '';
			$authoridarray = array();
			$query = $db->query("SELECT t.fid, t.tid, t.authorid, ff.postcredits FROM {$tablepre}threads t
				LEFT JOIN {$tablepre}forumfields ff USING(fid)
				WHERE t.tid IN ($validatetids) AND t.displayorder='-2' $fidadd[and]$fidadd[t]$fidadd[fids]");
			while($thread = $db->fetch_array($query)) {
				$tids .= $comma.$thread['tid'];
				$comma = ',';
				if($thread['postcredits']) {
					updatepostcredits('+', $thread['authorid'], unserialize($thread['postcredits']));
				} else {
					$authoridarray[] = $thread['authorid'];
				}
				$forums[] = $thread['fid'];
			}

			if($tids) {

				if($authoridarray) {
					updatepostcredits('+', $authoridarray, $creditspolicy['post']);
				}

				$db->query("UPDATE {$tablepre}posts SET invisible='0' WHERE tid IN ($tids)");
				$db->query("UPDATE {$tablepre}threads SET displayorder='0', moderated='1' WHERE tid IN ($tids)");
				$threadsmod = $db->affected_rows();

				foreach(array_unique($forums) as $fid) {
					updateforumcount($fid);
				}

				updatemodlog($tids, 'MOD');

			}
		}

		cpmsg('moderate_threads_succeed', "admincp.php?action=modthreads&page=$page");

	}

} elseif($action == 'modreplies') {

	if(!submitcheck('modsubmit')) {

		require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

		$page = !ispage($page) ? 1 : $page;
		$start_limit = ($page - 1) * 20;

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE invisible='2' $fidadd[and]$fidadd[fids]");
		$multipage = multi($db->result($query, 0), $tpp, $page, 'admincp.php?action=modreplies');

		$posts = '';
		$query = $db->query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
			p.pid, p.fid, p.tid, p.author, p.authorid, p.subject, p.dateline, p.message, p.useip, p.attachment,
			p.htmlon, p.smileyoff, p.bbcodeoff, t.subject AS tsubject
			FROM {$tablepre}posts p
			LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
			LEFT JOIN {$tablepre}forums f ON f.fid=p.fid
			WHERE p.invisible='2' $fidadd[and]$fidadd[p]$fidadd[fids]
			ORDER BY p.dateline DESC LIMIT $start_limit, 20");

		while($post = $db->fetch_array($query)) {
			$post['dateline'] = gmdate("$dateformat\<\b\\r\>$timeformat", $post['dateline'] + $timeoffset * 3600);
			$post['subject'] = $post['subject'] ? '<b>'.$post['subject'].'</b>' : '<i>'.$lang['nosubject'].'</i>';
			$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'], $post['allowsmilies'], $post['allowbbcode'], $post['allowimgcode'], $post['allowhtml']);

			$thisbg = $thisbg == ALTBG2 ? ALTBG1 : ALTBG2;
			$posts .= "<tr><td colspan=\"2\" class=\"singleborder\">&nbsp;</td></tr><tr bgcolor=\"$thisbg\"><td rowspan=\"2\" valign=\"top\" width=\"15%\" height=\"100%\">\n".
				"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" height=\"100%\">\n".
				"<tr><td valign=\"top\"><span class=\"bold\">$post[author]</span><br>$post[useip]</td></tr><tr><td valign=\"bottom\" class=\"smalltxt\">\n".
				"<input type=\"radio\" name=\"mod[$post[pid]]\" value=\"validate\" checked> $lang[validate]<br>\n".
				"<input type=\"radio\" name=\"mod[$post[pid]]\" value=\"delete\"> $lang[delete]<br>\n".
				"<input type=\"radio\" name=\"mod[$post[pid]]\" value=\"ignore\"> $lang[ignore]<br><br>\n".
				"$post[dateline]</td></tr></table></td><td><a href=\"forumdisplay.php?fid=$post[fid]\" target=\"_blank\">$post[forumname]</a> <b>&raquo;</b> \n".
				"<a href=\"viewthread.php?tid=$post[tid]\" target=\"_blank\">$post[tsubject]</a> <b>&raquo;</b> $post[subject]</a>\n".
				"</td></tr><tr bgcolor=\"$thisbg\"><td><div style=\"border-style: dotted; border-width: 1; border-color: ".BORDERCOLOR."; padding: 5; overflow: auto; overflow-y: scroll; width: 100%; height:180px\">$post[message]";

			if($post['attachment']) {
				require_once DISCUZ_ROOT.'./include/attachment.func.php';

				$queryattach = $db->query("SELECT aid, filename, filetype, filesize FROM {$tablepre}attachments WHERE pid='$post[pid]'");
				while($attach = $db->fetch_array($queryattach)) {
					$posts .= "<br>$lang[attachment]: ".attachtype(fileext($post['filename'])."\t".$attach['filetype']).
						" <a href=\"attachment.php?aid=$attach[aid]\" target=\"_blank\">$attach[filename]</a> (".sizecount($attach['filesize']).")";
				}
			}
			$posts .= "</div></td></tr>\n";
		}

		$posts = $posts ? $posts : '<tr><td colspan="2" bgcolor="'.ALTBG1.'"><a href="admincp.php?action=modthreads">'.$lang['moderate_posts_none'].'</a></td></tr>';

?>
<form method="post" action="admincp.php?action=modreplies&page=<?=$page?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" width="95%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr><td colspan="2" class="header"><?=$lang['moderate_posts']?></td></tr>
<tr><td colspan="2" class="category">
<input type="button" value="<?=$lang['moderate_all_validate']?>" onclick="checkalloption(this.form, 'validate')"> &nbsp;
<input type="button" value="<?=$lang['moderate_all_delete']?>" onclick="checkalloption(this.form, 'delete')"> &nbsp;
<input type="button" value="<?=$lang['moderate_all_ignore']?>" onclick="checkalloption(this.form, 'ignore')"></td></tr>
<?=$posts?>
</table>
<table cellspacing="0" cellpadding="0" width="95%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>
<br><center><input type="submit" name="modsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
		if(is_array($mod)) {
			foreach($mod as $pid => $action) {
				$moderation[$action][] = intval($pid);
			}
		}

		$repliesmod = 0;
		$deletepids = '\''.implode('\',\'', $moderation['delete']).'\'';
		$validatepids = '\''.implode('\',\'', $moderation['validate']).'\'';

		if($moderation['delete']) {
			$query = $db->query("SELECT attachment FROM {$tablepre}attachments WHERE pid IN ($deletepids)");
			while($attach = $db->fetch_array($query)) {
				@unlink($attachdir.'/'.$attach['attachment']);
			}

			$db->query("DELETE FROM {$tablepre}posts WHERE pid IN ($deletepids) $fidadd[and]$fidadd[fids]", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}attachments WHERE pid IN ($deletepids)", 'UNBUFFERED');
		}

		if($moderation['validate']) {
			$forums = $threads = $lastpost = $attachments = array();

			$pidarray = $authoridarray = array();
			$query = $db->query("SELECT t.lastpost, p.pid, p.fid, p.tid, p.authorid, p.author, p.dateline, p.attachment, ff.replycredits
				FROM {$tablepre}posts p
				LEFT JOIN {$tablepre}forumfields ff ON ff.fid=p.fid
				LEFT JOIN {$tablepre}threads t ON t.tid=p.tid
				WHERE p.pid IN ($validatepids) AND p.invisible='2' $fidadd[and]$fidadd[p]$fidadd[fids]");

			while($post = $db->fetch_array($query)) {
				$pidarray[] = $post['pid'];
				if($post['replycredits']) {
					updatepostcredits('+', $post['authorid'], unserialize($post['replycredits']));
				} else {
					$authoridarray[] = $post['authorid'];
				}

				$forums[] = $post['fid'];

				$threads[$post['tid']]['posts']++;
				$threads[$post['tid']]['lastpostadd'] = $post['dateline'] > $post['lastpost'] && $post['dateline'] > $lastpost[$post['tid']] ?
					", lastpost='$post[dateline]', lastposter='$post[author]'" : '';
				$threads[$post['tid']]['attachadd'] = $threads[$post['tid']]['attachadd'] || $post['attachment'] ? ', attachment=\'1\'' : '';
			}

			if($authoridarray) {
				updatepostcredits('+', $authoridarray, $creditspolicy['reply']);
			}

			foreach($threads as $tid => $thread) {
				$db->query("UPDATE {$tablepre}threads SET replies=replies+$thread[posts] $thread[lastpostadd] $thread[attachadd] WHERE tid='$tid'", 'UNBUFFERED');
			}

			foreach(array_unique($forums) as $fid) {
				updateforumcount($fid);
			}

			$db->query("UPDATE {$tablepre}posts SET invisible='0' WHERE pid IN (0,".implode(',', $pidarray).")");
			$repliesmod = $db->affected_rows();

		}

		cpmsg('moderate_replies_succeed', "admincp.php?action=modreplies&page=$page");

	}

}

?>