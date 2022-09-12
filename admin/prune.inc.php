<?php

/*
	[DISCUZ!] prune.inc.php - posts & pm pruning of Discuz! admincp
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005/2/8 01:12
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'prune') {

	require_once DISCUZ_ROOT.'./include/misc.func.php';
	require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

	if(!submitcheck('prunesubmit')) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		if($adminid == 1 || $adminid == 2) {
			$forumselect = '<select name="forums"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
				'<option value="">&nbsp;</option>'.forumselect().'</select>';

			if($forums) {
				$forumselect = preg_replace("/(\<option value=\"$forums\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
			}
		} else {
			$forumselect = $comma = '';
			$query = $db->query("SELECT f.name FROM {$tablepre}moderators m, {$tablepre}forums f WHERE m.uid='$discuz_uid' AND m.fid=f.fid");
			while($forum = $db->fetch_array($query)) {
				$forumselect .= $comma.$forum['name'];
				$comma = ', ';
			}
			$forumselect = $forumselect ? $forumselect : $lang['none'];
		}

		$checkcins = empty($cins) ? '' : 'checked';

		$starttime = !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $starttime) ? gmdate('Y-n-j', $timestamp + $timeoffset * 3600 - 86400 * 7) : $starttime;
		$endtime = $adminid == 3 || !preg_match("/^(0|\d{4}\-\d{1,2}\-\d{1,2})$/", $endtime) ? gmdate('Y-n-j', $timestamp + $timeoffset * 3600) : $endtime;

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['prune_tips']?>
</td></tr></table>

<br><br><form method="post" action="admincp.php?action=prune">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">

<tr>
<td class="header" colspan="2"><?=$lang['prune_search']?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['prune_search_detail']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="checkbox" name="detail" value="1"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['prune_search_forum']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><?=$forumselect?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['prune_search_time']?></td>
<td align="right" bgcolor="<?=ALTBG2?>">
<input type="text" name="starttime" size="10" value="<?=$starttime?>"> - 
<input type="text" name="endtime" size="10" value="<?=dhtmlspecialchars($endtime)?>" <?=($adminid != 1 ? 'disabled' : '')?>>
</td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['prune_search_user']?></td>
<td align="right" bgcolor="<?=ALTBG2?>">
<?=$lang['case_insensitive']?> <input type="checkbox" name="cins" value="1" <?=$checkcins?>>
<br><input type="text" name="users" value="<?=dhtmlspecialchars($users)?>" size="40"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['prune_search_ip']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="useip" value="<?=dhtmlspecialchars($useip)?>" size="40"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['prune_search_keyword']?></td>
<td align="right" bgcolor="<?=ALTBG2?>"><input type="text" name="keywords" value="<?=dhtmlspecialchars($keywords)?>" size="40"></td>
</tr>

</table><br>
<center><input type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$tidsdelete = $pidsdelete = '0';

		$pids = authcode($pids, 'DECODE');
		$pidsadd = is_array($pidarray) ? 'AND pid IN (\''.implode('\',\'', $pidarray).'\')' : '';

		$query = $db->query("SELECT fid, tid, pid, first, authorid FROM {$tablepre}posts WHERE pid IN ($pids) $pidsadd");
		while($post = $db->fetch_array($query)) {
			$prune['forums'][] = $post['fid'];
			$prune['thread'][$post['tid']]++;

			$pidsdelete .= ",$post[pid]";
			$tidsdelete .= $post['first'] ? ",$post[tid]" : '';
		}

		if($pidsdelete) {
			require_once DISCUZ_ROOT.'./include/post.func.php';

			$query = $db->query("SELECT attachment FROM {$tablepre}attachments WHERE pid IN ($pidsdelete) OR tid IN ($tidsdelete)");
			while($attach = $db->fetch_array($query)) {
				@unlink($attachdir.'/'.$attach['attachment']);
			}

			if(!$donotupdatemember) {
				$postsarray = $tuidarray = $ruidarray = array();
				$query1 = $db->query("SELECT pid, first, authorid FROM {$tablepre}posts WHERE pid IN ($pidsdelete)");
				$query2 = $db->query("SELECT pid, first, authorid FROM {$tablepre}posts WHERE tid IN ($tidsdelete)");
				while(($post = $db->fetch_array($query1)) || ($post = $db->fetch_array($query2))) {
					$postsarray[$post['pid']] = $post;
				}
				foreach($postsarray as $post) {
					if($post['first']) {
						$tuidarray[] = $post['authorid'];
					} else {
						$ruidarray[] = $post['authorid'];
					}
				}
				if($tuidarray) {
					updatepostcredits('-', $tuidarray, $creditspolicy['post']);
				}
				if($ruidarray) {
					updatepostcredits('-', $ruidarray, $creditspolicy['reply']);
				}
			}

			$db->query("DELETE FROM {$tablepre}attachments WHERE pid IN ($pidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}threadsmod WHERE tid IN ($tidsdelete)", 'UNBUFFERED');
			$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($tidsdelete)");
			$deletedthreads = $db->affected_rows();
			$db->query("DELETE FROM {$tablepre}posts WHERE pid IN ($pidsdelete)");
			$deletedposts = $db->affected_rows();
			$db->query("DELETE FROM {$tablepre}posts WHERE tid IN ($tidsdelete)");
			$deletedposts += $db->affected_rows();
			$db->query("DELETE FROM {$tablepre}polls WHERE tid IN ($tidsdelete)");

			if(count($prunt['thread']) < 50) {
				foreach($prune['thread'] as $tid => $decrease) {
					updatethreadcount($tid);
				}
			} else {
				$repliesarray = array();
				foreach($prune['thread'] as $tid => $decrease) {
					$repliesarray[$decrease][] = $tid;
				}
				foreach($repliesarray as $decrease => $tidarray) {
					$db->query("UPDATE {$tablepre}threads SET replies=replies-$decrease WHERE tid IN (".implode(',', $tidarray).")");
				}
			}

			if($globalstick) {
				updatecache('globalstick');
			}

			foreach(array_unique($prune['forums']) as $fid) {
				updateforumcount($fid);
			}

		}

		$deletedthreads = intval($deletedthreads);
		$deletedposts = intval($deletedposts);
		cpmsg('prune_succeed');

	}

	if(submitcheck('searchsubmit')) {

		$pids = $postcount = '0';
		$sql = $error = '';

		$keywords = trim($keywords);
		$users = trim($users);
		if(($starttime == '0' && $endtime == '0') || ($keywords == '' && $useip == '' && $users == '')) {
			$error = 'prune_condition_invalid';
		}

		if($adminid == 1 || $adminid == 2) {
			if($forums) {
				$sql .= " AND p.fid='$forums'";
			}
		} else {
			$forums = '0';
			$query = $db->query("SELECT fid FROM {$tablepre}moderators WHERE uid='$discuz_uid'");
			while($forum = $db->fetch_array($query)) {
				$forums .= ','.$forum['fid'];
			}
			$sql .= " AND p.fid IN ($forums)";
		}

		if($users != '') {
			$uids = '-1';
			$query = $db->query("SELECT uid FROM {$tablepre}members WHERE ".(empty($cins) ? 'BINARY' : '')." username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')");
			while($member = $db->fetch_array($query)) {
				$uids .= ",$member[uid]";
			}
			$sql .= " AND p.authorid IN ($uids)";
		}
		if($useip != '') {
			$sql .= " AND p.useip LIKE '".str_replace('*', '%', $useip)."'";
		}
		if($keywords != '') {
			$sqlkeywords = '';
			$or = '';
			$keywords = explode(',', str_replace(' ', '', $keywords));
			for($i = 0; $i < count($keywords); $i++) {
				$sqlkeywords .= " $or p.subject LIKE '%".$keywords[$i]."%' OR p.message LIKE '%".$keywords[$i]."%'";
				$or = 'OR';
			}
			$sql .= " AND ($sqlkeywords)";
		}

		if($starttime != '0') {
			$starttime = strtotime($starttime);
			$sql .= " AND p.dateline>'$starttime'";
		}
		if($adminid == 1 && $endtime != gmdate('Y-n-j', $timestamp + $timeoffset * 3600)) {
			if($endtime != '0') {
				$endtime = strtotime($endtime);
				$sql .= " AND p.dateline<'$endtime'";
			}
		} else {
			$endtime = $timestamp;
		}
		if(($adminid == 2 && $endtime - $starttime > 86400 * 16) || ($adminid == 3 && $endtime - $starttime > 86400 * 8)) {
			$error = 'prune_mod_range_illegal';
		}

		if(!$error) {
			if($detail) {
				$query = $db->query("SELECT p.fid, p.tid, p.pid, p.author, p.authorid, p.dateline, t.subject, p.message FROM {$tablepre}posts p LEFT JOIN {$tablepre}threads t USING(tid) WHERE 1 $sql");
				while($post = $db->fetch_array($query)) {
					$post['dateline'] = gmdate("$dateformat $timeformat", $post['dateline'] + $timeoffset * 3600);
					$post['subject'] = cutstr($post['subject'], 30);
					$post['message'] = dhtmlspecialchars(cutstr($post['message'], 50));

					$posts .= "<tr><td align=\"center\" bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"pidarray[]\" value=\"$post[pid]\" checked>\n".
						"<td bgcolor=\"".ALTBG2."\"><a href=\"viewthread.php?tid=$post[tid]\" target=\"_blank\">$post[subject]</a></td>\n".
						"<td bgcolor=\"".ALTBG1."\">$post[message]</td>\n".
						"<td align=\"center\" bgcolor=\"".ALTBG2."\"><a href=\"forumdisplay.php?fid=$post[fid]\" target=\"_blank\">{$_DCACHE[forums][$post[fid]][name]}</a></td>\n".
						"<td align=\"center\" bgcolor=\"".ALTBG1."\"><a href=\"viewpro.php?uid=$post[authorid]\" target=\"_blank\">$post[author]</a></td>\n".
						"<td align=\"center\" bgcolor=\"".ALTBG2."\">$post[dateline]</td></tr>\n";
					$pids .= ','.$post['pid'];
				}
			} else {
				$query = $db->query("SELECT pid FROM {$tablepre}posts p WHERE 1 $sql");
				while($post = $db->fetch_array($query)) {
					$pids .= ','.$post['pid'];
				}
			}

			$postcount = $db->num_rows($query);
			if(!$postcount) {
				$error = 'prune_post_nonexistence';
			}
		}

?>
<br><br><form method="post" action="admincp.php?action=prune">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="pids" value="<?=authcode($pids, 'ENCODE')?>">

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">

<tr>
<td class="header" colspan="2"><?=$lang['prune_result']?> <?=$postcount?></td>
</tr>
<?

		if($error) {
			echo "<tr><td bgcolor=\"".ALTBG2."\"><b>$lang[discuz_message]: $lang[$error]</b></td></tr>";
		} else {

?>
<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['prune']?></td>
<td bgcolor="<?=ALTBG2?>"><input type="checkbox" name="donotupdatemember" value="1" checked> <?=$lang['prune_no_update_member']?></td>
</tr>
<?

			if($detail) {

?>
</table><br><br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header" align="center"><td>&nbsp;</td><td><?=$lang['subject']?></td><td><?=$lang['message']?></td><td><?=$lang['forum']?></td><td><?=$lang['author']?></td><td><?=$lang['time']?></td></tr>
<?=$posts?>
<?

			}

		}

		echo '</table><br><center><input type="submit" name="prunesubmit" value="'.$lang['submit'].'" '.($error ? 'disabled' : '').'></center></form>';

	}

} elseif($action == 'pmprune') {

	if(!submitcheck('prunesubmit', 1)) {

?>
<br><br><br><form method="post" action="admincp.php?action=pmprune">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr><td class="header" colspan="2"><?=$lang['prune_pm']?></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['prune_pm_ignore_new']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="checkbox" name="ignorenew" value="1"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['prune_pm_day']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="days" size="7"></td></tr>

<tr><td bgcolor="<?=ALTBG1?>"><?=$lang['prune_pm_user']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<?=$lang['case_insensitive']?> <input type="checkbox" name="cins" value="1">
<br><input type="text" name="users" size="40"></td></tr>

</table><br>
<center><input type="submit" name="prunesubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if(!$confirmed || !isset($pmids) || !preg_match("/[\d,]/", $pmids)) {

			if($days == '') {
				cpmsg('prune_pm_range_invalid');
			} else {
				$uids = 0;
				$users = str_replace(',', '\',\'', str_replace(' ', '', $users));
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE ".(empty($cins) ? 'BINARY' : '')." username IN ('$users')");
				while($member = $db->fetch_array($query)) {
					$uids .= ','.$member['uid'];
				}

				$prunedateadd = $days != 0 ? "AND dateline<='".($timestamp - $days * 86400)."'" : '';
				$pruneuseradd = $users ? "AND ((msgfromid IN ($uids) AND folder='outbox') OR (msgtoid IN ($uids) AND folder='inbox'))" : '';
				$prunenewadd = $ignorenew ? "AND new='0'" : '';

				$pmids = 0;
				$query = $db->query("SELECT pmid FROM {$tablepre}pms WHERE 1 $prunedateadd $pruneuseradd $prunenewadd");
				while($pm = $db->fetch_array($query)) {
					$pmids .= ','.$pm['pmid'];
				}

				$pmnum = $db->num_rows($query);
				cpmsg('prune_pm_confirm', "admincp.php?action=pmprune&prunesubmit=yes", 'form', '<input type="hidden" name="pmids" value="'.$pmids.'">');
			}

		} else {

			$db->query("DELETE FROM {$tablepre}pms WHERE pmid IN ($pmids)");
			$num = $db->affected_rows();

			cpmsg('prune_pm_succeed');

		}
	}

}

?>