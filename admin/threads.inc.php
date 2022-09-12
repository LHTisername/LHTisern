<?php

/*
	[DISCUZ!] threads.inc.php - mass moderation of threads of Discuz! admincp
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2004/12/21 10:58
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./include/post.func.php';

cpheader();

if(!$operation) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		$forumselect = '<select name="inforum"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
			'<option value="">&nbsp;</option>'.forumselect().'</select>';
		if(isset($inforum)) {
			$forumselect = preg_replace("/(\<option value=\"$inforum\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
		}

		$typeselect = '<select name="intype"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
			'<option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['threads_search_type_none'].'</option>';
		$query = $db->query("SELECT * FROM {$tablepre}threadtypes ORDER BY displayorder");
		while($type = $db->fetch_array($query)) {
			$typeselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].($type['description'] ? ' ('.$type['description'].')' : '').'</option>';
		}
		$typeselect .= '</select>';
		if(isset($intype)) {
			$typeselect = preg_replace("/(\<option value=\"$intype\")(\>)/", "\\1 selected=\"selected\" \\2", $typeselect);
		}

		$checkcins	= empty($cins) ? '' : 'checked';
		$checksticky	= array(intval($sticky) => 'checked');
		$checkdigest	= array(intval($digest) => 'checked');
		$checkattach	= array(intval($attach) => 'checked');
		$checkblog	= array(intval($blog) => 'checked');

?>
<br><form method="post" action="admincp.php?action=threads">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">

<tr>
<td class="header" colspan="2"><?=$lang['threads_search']?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_detail']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="checkbox" name="detail" value="1"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_forum']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><?=$forumselect?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_type']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><?=$typeselect?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_viewless']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="viewsless" size="40" value="<?=dhtmlspecialchars($viewsless)?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_viewmore']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="viewsmore" size="40" value="<?=dhtmlspecialchars($viewsmore)?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_replyless']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="repliesless" size="40" value="<?=dhtmlspecialchars($repliesless)?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_replymore']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="repliesmore" size="40" value="<?=dhtmlspecialchars($repliesmore)?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_readpermmore']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="readpermmore" size="40" value="<?=dhtmlspecialchars($readpermmore)?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_pricemore']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="pricemore" size="40" value="<?=dhtmlspecialchars($pricemore)?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_time']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<input type="text" name="starttime" size="10" value="<?=dhtmlspecialchars($starttime)?>"> - 
<input type="text" name="endtime" size="10" value="<?=dhtmlspecialchars($endtime)?>">
</td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_noreplyday']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="noreplydays" size="40" value="<?=dhtmlspecialchars($noreplydays)?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_user']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<?=$lang['case_insensitive']?> <input type="checkbox" name="cins" value="1" <?=$checkcins?>>
<br><input type="text" name="users" size="40" value="<?=$users?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_keyword']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="keywords" size="40" value="<?=dhtmlspecialchars($keywords)?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_sticky']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<input type="radio" name="sticky" value="0" <?=$checksticky[0]?>> <?=$lang['unlimited']?>&nbsp;
<input type="radio" name="sticky" value="1" <?=$checksticky[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input type="radio" name="sticky" value="2" <?=$checksticky[2]?>> <?=$lang['threads_search_include_no']?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_digest']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<input type="radio" name="digest" value="0" <?=$checkdigest[0]?>> <?=$lang['unlimited']?>&nbsp;
<input type="radio" name="digest" value="1" <?=$checkdigest[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input type="radio" name="digest" value="2" <?=$checkdigest[2]?>> <?=$lang['threads_search_include_no']?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_blog']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<input type="radio" name="blog" value="0" <?=$checkblog[0]?>> <?=$lang['unlimited']?>&nbsp;
<input type="radio" name="blog" value="1" <?=$checkblog[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input type="radio" name="blog" value="2" <?=$checkblog[2]?>> <?=$lang['threads_search_include_no']?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['threads_search_attach']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<input type="radio" name="attach" value="0" <?=$checkattach[0]?>> <?=$lang['unlimited']?>&nbsp;
<input type="radio" name="attach" value="1" <?=$checkattach[1]?>> <?=$lang['threads_search_include_yes']?>&nbsp;
<input type="radio" name="attach" value="2" <?=$checkattach[2]?>> <?=$lang['threads_search_include_no']?></td>
</tr>

</table><br>
<center><input type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

} else {

	if(!isset($tids)) {
		$tids = implode(',', $tidarray);
	}

	if($operation == 'moveforum') {

		$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE type<>'group' AND fid='$toforum'");
		if(!$db->result($query, 0)) {
			cpmsg('threads_move_invalid');
		}

		$db->query("UPDATE {$tablepre}threads SET fid='$toforum' WHERE tid IN ($tids)");
		$db->query("UPDATE {$tablepre}posts SET fid='$toforum' WHERE tid IN ($tids)");

		foreach(explode(',', $fids.','.$toforum) as $fid) {
			updateforumcount(intval($fid));
		}

		cpmsg('threads_succeed');

	} elseif($operation == 'movetype') {

		if($totype != 0) {
			$query = $db->query("SELECT typeid FROM {$tablepre}threadtypes WHERE typeid='$totype'");
			if(!$db->result($query, 0)) {
				cpmsg('threads_move_invalid');
			}
		}

		$db->query("UPDATE {$tablepre}threads SET typeid='$totype' WHERE tid IN ($tids)");

		cpmsg('threads_succeed');

	} elseif($operation == 'delete') {

		$query = $db->query("SELECT attachment FROM {$tablepre}attachments WHERE tid IN ($tids)");
		while($attach = $db->fetch_array($query)) {
			@unlink($attachdir.'/'.$attach['attachment']);
		}

		if(!$donotupdatemember) {
			$tuidarray = $ruidarray = array();
			$query = $db->query("SELECT first, authorid FROM {$tablepre}posts WHERE tid IN ($tids)");
			while($post = $db->fetch_array($query)) {
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

		$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($tids)");
		$db->query("DELETE FROM {$tablepre}posts WHERE tid IN ($tids)");
		$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($tids)");
		$db->query("DELETE FROM {$tablepre}polls WHERE tid IN ($tids)");
		$db->query("DELETE FROM {$tablepre}threadsmod WHERE tid IN ($tids)");

		if($globalstick) {
			updatecache('globalstick');
		}

		foreach(explode(',', $fids) as $fid) {
			updateforumcount(intval($fid));
		}

		cpmsg('threads_succeed');

	} elseif($operation == 'deleteattach') {

		$query = $db->query("SELECT attachment FROM {$tablepre}attachments WHERE tid IN ($tids)");
		while($attach = $db->fetch_array($query)) {
			@unlink($attachdir.'/'.$attach['attachment']);
		}
		$db->query("UPDATE {$tablepre}threads SET attachment='0' WHERE tid IN ($tids)");
		$db->query("UPDATE {$tablepre}posts SET attachment='0' WHERE tid IN ($tids)");

		cpmsg('threads_succeed');

	} elseif($operation == 'stick') {

		$db->query("UPDATE {$tablepre}threads SET displayorder='$stick_level' WHERE tid IN ($tids)");
		if($globalstick) {
			updatecache('globalstick');
		}

		cpmsg('threads_succeed');

	} elseif($operation == 'adddigest') {

		$query = $db->query("SELECT tid, authorid, digest FROM {$tablepre}threads WHERE tid IN ($tids)");
		while($thread = $db->fetch_array($query)) {
			updatecredits($thread['authorid'], $creditspolicy['digest'], $digest_level - $thread['digest'], 'digestposts=digestposts-1');
		}
		$db->query("UPDATE {$tablepre}threads SET digest='$digest_level' WHERE tid IN ($tids)");

		cpmsg('threads_succeed');

	}

}

if(submitcheck('searchsubmit')) {

	$sql = '';

	if($inforum != '' && $inforum != 'all') {
		$sql .= " AND fid='$inforum'";
	}

	if($intype != '' && $intype != 'all') {
		$sql .= " AND typeid='$intype'";
	}

	if($viewsless != '') {
		$sql .= " AND views<'$viewsless'";
	}
	if($viewsmore != '') {
		$sql .= " AND views>'$viewsmore'";
	}

	if($repliesless != '') {
		$sql .= " AND replies<'$repliesless'";
	}
	if($repliesmore != '') {
		$sql .= " AND replies>'$repliesmore'";
	}

	if($readpermmore != '') {
		$sql .= " AND readperm>'$readpermmore'";
	}

	if($pricemore != '') {
		$sql .= " AND price>'$pricemore'";
	}

	if($beforedays != '') {
		$sql .= " AND dateline<'$timestamp'-'$beforedays'*86400";
	}
	if($noreplydays != '') {
		$sql .= " AND lastpost<'$timestamp'-'$noreplydays'*86400";
	}

	if($starttime != '') {
		$starttime = strtotime($starttime);
		$sql .= " AND dateline>'$starttime'";
	}

	if($endtime) {
		$endtime = strtotime($endtime);
		$sql .= " AND dateline<='$endtime'";
	}

	if(trim($keywords)) {
		$sqlkeywords = '';
		$or = '';
		$keywords = explode(',', str_replace(' ', '', $keywords));
		for($i = 0; $i < count($keywords); $i++) {
			$sqlkeywords .= " $or subject LIKE '%".$keywords[$i]."%'";
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	if(trim($users)) {
		$sql .= " AND ".(empty($cins) ? 'BINARY' : '')." author IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')";
	}

	if($sticky == 1) {
		$sql .= " AND displayorder>'0'";
	} elseif($sticky == 2) {
		$sql .= " AND displayorder='0'";
	}
	if($digest == 1) {
		$sql .= " AND digest>'0'";
	} elseif($digest == 2) {
		$sql .= " AND digest='0'";
	}
	if($blog == 1) {
		$sql .= " AND blog>'0'";
	} elseif($blog == 2) {
		$sql .= " AND blog='0'";
	}
	if($attach == 1) {
		$sql .= " AND attachment>'0'";
	} elseif($attach == 2) {
		$sql .= " AND attachment='0'";
	}

	$fids = array();
	$tids = $threadcount = '0';
	if($sql) {
		if($detail) {
			$threads = '';
			$query = $db->query("SELECT fid, tid, readperm, price, subject, authorid, author, views, replies, lastpost FROM {$tablepre}threads WHERE displayorder>='0' $sql");
			while($thread = $db->fetch_array($query)) {
				$thread['lastpost'] = gmdate("$dateformat $timeformat", $thread['lastpost'] + $timeoffset * 3600);
				$threads .= "<tr><td align=\"center\" bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"tidarray[]\" value=\"$thread[tid]\" checked>\n".
					"<td bgcolor=\"".ALTBG2."\"><a href=\"viewthread.php?tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>".($thread['readperm'] ? " - [$lang[threads_readperm] $thread[readperm]]" : '').($thread['price'] ? " - [$lang[threads_price] $thread[price]]" : '')."</td>\n".
					"<td align=\"center\" bgcolor=\"".ALTBG1."\"><a href=\"forumdisplay.php?fid=$thread[fid]\" target=\"_blank\">{$_DCACHE[forums][$thread[fid]][name]}</a></td>\n".
					"<td align=\"center\" bgcolor=\"".ALTBG2."\"><a href=\"viewpro.php?uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a></td>\n".
					"<td align=\"center\" bgcolor=\"".ALTBG1."\">$thread[replies]</td>\n".
					"<td align=\"center\" bgcolor=\"".ALTBG2."\">$thread[views]</td>\n".
					"<td align=\"center\" bgcolor=\"".ALTBG1."\">$thread[lastpost]</td></tr>\n";
			}
		} else {
			$query = $db->query("SELECT fid, tid FROM {$tablepre}threads WHERE displayorder>='0' $sql");
			while($thread = $db->fetch_array($query)) {
				$fids[] = $thread['fid'];
				$tids .= ','.$thread['tid'];
			}
		}
		$threadcount = $db->num_rows($query);
	}
	$fids = implode(',', array_unique($fids));

?>
<br><form method="post" action="admincp.php?action=threads">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">

<tr>
<td class="header" colspan="2"><?=$lang['threads_result']?> <?=$threadcount?></td>
</tr>
<?

	if(!$threadcount) {

		echo '<tr><td bgcolor="'.ALTBG2.'" colspan="2">'.$lang['threads_thread_nonexistence'].'</td></tr>';

	} else {

		$typeselect = '';
		$query = $db->query("SELECT * FROM {$tablepre}threadtypes ORDER BY displayorder");
		while($type = $db->fetch_array($query)) {
			$typeselect .= '<option value="'.$type['typeid'].'">&nbsp;&nbsp;> '.$type['name'].($type['description'] ? ' ('.$type['description'].')' : '').'</option>';
		}

		if(!$detail) {
			echo '<input type="hidden" name="tids" value="'.$tids.'">';
		}
		echo '<input type="hidden" name="fids" value="'.$fids.'">';

?>
<tr>
<td bgcolor="<?=ALTBG1?>"><input type="radio" name="operation" value="moveforum" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_move_forum']?></td>
<td bgcolor="<?=ALTBG2?>"><select name="toforum"><?=forumselect()?></select></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><input type="radio" name="operation" value="movetype" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_move_type']?></td>
<td bgcolor="<?=ALTBG2?>"><select name="totype"><option value="0">&nbsp;&nbsp;> <?=$lang['threads_search_type_none']?></option><?=$typeselect?></select></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><input type="radio" name="operation" value="delete" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_delete']?></td>
<td bgcolor="<?=ALTBG2?>"><input type="checkbox" name="donotupdatemember" value="1" checked> <?=$lang['threads_delete_no_update_member']?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><input type="radio" name="operation" value="stick" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_stick']?></td>
<td bgcolor="<?=ALTBG2?>">
<input type="radio" name="stick_level" value="0" checked> <?=$lang['remove']?> &nbsp; &nbsp; 
<input type="radio" name="stick_level" value="1"> <img src="<?=IMGDIR?>/star_level1.gif"> &nbsp; &nbsp; 
<input type="radio" name="stick_level" value="2"> <img src="<?=IMGDIR?>/star_level1.gif"><img src="<?=IMGDIR?>/star_level1.gif"> &nbsp; &nbsp; 
<input type="radio" name="stick_level" value="3"> <img src="<?=IMGDIR?>/star_level1.gif"><img src="<?=IMGDIR?>/star_level1.gif"><img src="<?=IMGDIR?>/star_level1.gif"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><input type="radio" name="operation" value="adddigest" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_add_digest']?></td>
<td bgcolor="<?=ALTBG2?>">
<input type="radio" name="digest_level" value="0" checked> <?=$lang['remove']?> &nbsp; &nbsp; 
<input type="radio" name="digest_level" value="1"> <img src="<?=IMGDIR?>/star_level1.gif"> &nbsp; &nbsp; 
<input type="radio" name="digest_level" value="2"> <img src="<?=IMGDIR?>/star_level1.gif"><img src="<?=IMGDIR?>/star_level1.gif"> &nbsp; &nbsp; 
<input type="radio" name="digest_level" value="3"> <img src="<?=IMGDIR?>/star_level1.gif"><img src="<?=IMGDIR?>/star_level1.gif"><img src="<?=IMGDIR?>/star_level1.gif"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><input type="radio" name="operation" value="deleteattach" onclick="this.form.modsubmit.disabled=false;"> <?=$lang['threads_delete_attach']?></td>
<td bgcolor="<?=ALTBG2?>">&nbsp;</td>
</tr>
<?

		if($detail) {

?>
</table><br><br>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header" align="center"><td>&nbsp;</td><td><?=$lang['subject']?></td><td><?=$lang['forum']?></td><td><?=$lang['author']?></td><td nowrap><?=$lang['threads_replies']?></td><td nowrap><?=$lang['threads_views']?></td><td><?=$lang['threads_lastpost']?></td></tr>
<?=$threads?>
<?

		}

	}

	echo '</table><br><center><input type="submit" name="modsubmit" value="'.$lang['submit'].'" disabled></center></form>';

}

?>