<?php

/*
	[DISCUZ!] admin/tools.inc.php - board tools (newsletter, file permissions checking, etc)
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-2-24 11:10
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'newsletter') {

	if(!submitcheck('newslettersubmit')) {

		$count = 0;
		$usergroups = '';
		$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups ORDER BY type, creditslower");
		while($group = $db->fetch_array($query)) {
			$usergroups .= ($count++ % 3 == 0 ? '</tr><tr>' : '').
				"<td width=\"33%\" nowrap><input type=\"checkbox\" name=\"sendto[]\" value=\"$group[groupid]\"> $group[grouptitle]</td>";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['newsletter_tips']?>
</td></tr></table>

<br><br><form method="post" action="admincp.php?action=newsletter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['newsletter']?></td></tr>

<tr>
<td bgcolor="<?=ALTBG1?>" valign="top">
<?=$lang['newsletter_to']?>
<br><input type="checkbox" name="chkall" onclick="checkall(this.form)"> <?=$lang['all']?>
</td><td bgcolor="<?=ALTBG2?>">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
<tr><?=$usergroups?></tr></table>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['subject']?>:</td>
<td bgcolor="<?=ALTBG2?>"><input type="text" name="subject" size="80"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>" valign="top"><?=$lang['message']?>:</td><td bgcolor="<?=ALTBG2?>">
<textarea cols="80" rows="10" name="message"></textarea></td></tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['newsletter_send_via']?></td>
<td bgcolor="<?=ALTBG2?>">
<input type="radio" value="email" name="sendvia"> <?=$lang['email']?>
<input type="radio" value="pm" checked name="sendvia"> <?=$lang['pm']?>
</td></tr>

</table><br>
<center><input type="submit" name="newslettersubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if(is_array($sendto)) {
			$ids = '\''.implode('\',\'', $sendto).'\'';
		} else {
			cpmsg('newsletter_send_to_invalid');
		}

		if(!$subject || !$message) {
			cpmsg('newsletter_sm_invalid');
		}

		$subject = '[Discuz!] '.$subject;

		$emails = '';
		$query = $db->query("SELECT uid, email FROM {$tablepre}members WHERE groupid IN ($ids) AND newsletter='1'");
		while($member = $db->fetch_array($query)) {
			if($sendvia == 'pm') {
				require_once DISCUZ_ROOT.'./include/discuzcode.func.php';
				$subject = dhtmlspecialchars($subject);
				$message = trim(parseurl($message));
				$db->query("INSERT INTO {$tablepre}pms (msgfrom, msgfromid, msgtoid, folder, new, subject, dateline, message)
					VALUES('$discuz_user', '$discuz_uid', '$member[uid]', 'inbox', '1', '$subject', '$timestamp', '$message')");
			} elseif($sendvia == 'email') {
				$emails .= $comma.$member['email'];
				$comma = ',';
			}
		}

		if($sendvia == 'pm') {
			$db->query("UPDATE {$tablepre}members SET newpm='1' WHERE groupid IN ($ids) AND newsletter='1'");
		} elseif($sendvia == 'email') {
			sendmail($emails, $subject, $message);
		}

		cpmsg('newsletter_succeed');

	}

} elseif($action == 'updatecache') {

	$tpl = dir(DISCUZ_ROOT.'./forumdata/templates');
	while($entry = $tpl->read()) {
		if(preg_match("/\.tpl\.php$/", $entry)) {
			@unlink(DISCUZ_ROOT.'./forumdata/templates/'.$entry);
		}
	}
	$tpl->close();

	$js = dir(DISCUZ_ROOT.'./forumdata/cache');
	while($entry = $js->read()) {
		if(preg_match("/^javascript_/", $entry)) {
			@unlink(DISCUZ_ROOT.'./forumdata/cache/'.$entry);
		}
	}
	$js->close();

	updatecache();

	$db->query("DELETE FROM {$tablepre}searchindex");
	$db->query("DELETE FROM {$tablepre}threadsmod WHERE dateline<$timestamp-86400*365");

	cpmsg('update_cache_succeed');

} elseif($action == 'jswizard') {

	/* Threads == Start == */
	$tcheckorderby = array((isset($parameter['orderby']) ? $parameter['orderby'] : 'lastpost') => 'checked');
	for($i = 1; $i <= 4; $i++) {
		$tcheckdigest[$i] = !empty($parameter['digest'][$i]) ? 'checked' : '';
	}

	echo '<form method="post" action="admincp.php?action=jswizard&function=threads#'.$lang['jswizard_threads'].'">';
	showtype('jswizard_threads', 'top');
	if($jssubmit && $function == 'threads') {
		$jsurl = "function=$function".
			($parameter['threads_forums'] && !in_array('all', $parameter['threads_forums'])? '&fids='.jsfids($parameter['threads_forums']) : '').
			"&maxlength=$parameter[maxlength]".
			"&startrow=$parameter[startrow]".
			"&picpre=".rawurlencode($parameter['picpre']).
			"&items=$parameter[items]".
			"&digest=".bindec(intval($parameter['digest'][1]).intval($parameter['digest'][2]).intval($parameter['digest'][3]).intval($parameter['digest'][4])).
			"&newwindow=$parameter[newwindow]".
			"&highlight=$parameter[highlight]".
			"&forum=$parameter[forum]".
			"&author=$parameter[author]".
			"&dateline=$parameter[dateline]".			
			"&orderby=$parameter[orderby]";
		$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
		echo "<tr bgcolor=\"".ALTBG1."\"><td colspan=\"2\">".
			"<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
			dhtmlspecialchars("<script language=\"JavaScript\" src=\"$jsurl\"></script>").
			"</textarea></td></tr>";
	}
	showsetting('jswizard_threads_fids', '', '', jsforumselect('threads'));
	showsetting('jswizard_threads_maxlength', 'parameter[maxlength]', isset($parameter['maxlength']) ? $parameter['maxlength'] : 50, 'text');
	showsetting('jswizard_threads_startrow', 'parameter[startrow]', intval($parameter['startrow']), 'text');
	showsetting('jswizard_threads_items', 'parameter[items]', isset($parameter['items']) ? $parameter['items'] : 10, 'text');
	showsetting('jswizard_threads_picpre', 'parameter[picpre]', $parameter['picpre'], 'text');
	showsetting('jswizard_threads_digest', '', '', '<input type="checkbox" name="parameter[digest][1]" value="1" '.$tcheckdigest[1].'> '.$lang['jswizard_digest_1'].'<br><input type="checkbox" name="parameter[digest][2]" value="1" '.$tcheckdigest[2].'> '.$lang['jswizard_digest_2'].'<br><input type="checkbox" name="parameter[digest][3]" value="1" '.$tcheckdigest[3].'> '.$lang['jswizard_digest_3'].'<br><input type="checkbox" name="parameter[digest][4]" value="1" '.$tcheckdigest[4].'> '.$lang['jswizard_digest_0'].'');
	showsetting('jswizard_threads_newwindow', 'parameter[newwindow]', isset($parameter['newwindow']) ? $parameter['newwindow'] : 1, 'radio');
	showsetting('jswizard_threads_highlight', 'parameter[highlight]', $parameter['highlight'], 'radio');
	showsetting('jswizard_threads_forum', 'parameter[forum]', $parameter['forum'], 'radio');
	showsetting('jswizard_threads_author', 'parameter[author]', $parameter['author'], 'radio');
	showsetting('jswizard_threads_dateline', 'parameter[dateline]', $parameter['dateline'], 'radio');
	showsetting('jswizard_threads_orderby', '', '', '<input type="radio" name="parameter[orderby]" value="lastpost" '.$tcheckorderby['lastpost'].'> '.$lang['jswizard_threads_orderby_lastpost'].'<br><input type="radio" name="parameter[orderby]" value="dateline" '.$tcheckorderby['dateline'].'> '.$lang['jswizard_threads_orderby_dateline'].'<br><input type="radio" name="parameter[orderby]" value="replies" '.$tcheckorderby['replies'].'> '.$lang['jswizard_threads_orderby_replies'].'<br><input type="radio" name="parameter[orderby]" value="views" '.$tcheckorderby['views'].'> '.$lang['jswizard_threads_orderby_views']);
	showtype('', 'bottom');
	echo '<br><center><input type="submit" name="jssubmit" value="'.$lang['submit'].'"></center></form>';
	/* Threads == End == */

	/* Forums == Start == */
	$fcheckorderby = array((isset($parameter['orderby']) ? $parameter['orderby'] : 'displayorder') => 'checked');

	echo '<form method="post" action="admincp.php?action=jswizard&function=forums#'.$lang['jswizard_forums'].'">';
	showtype('jswizard_forums', 'top');
	if($jssubmit && $function == 'forums') {
		$jsurl = "function=$function".
			($parameter['forums_forums'] && !in_array('all', $parameter['forums_forums'])? '&fups='.jsfids($parameter['forums_forums']) : '').
			"&startrow=$parameter[startrow]".
			"&items=$parameter[items]".
			"&newwindow=$parameter[newwindow]".
			"&orderby=$parameter[orderby]";
		$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
		echo "<tr bgcolor=\"".ALTBG1."\"><td colspan=\"2\">".
			"<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
			dhtmlspecialchars("<script language=\"JavaScript\" src=\"$jsurl\"></script>").
			"</textarea></td></tr>";
	}
	showsetting('jswizard_forums_fups', '', '', jsforumselect('forums'));
	showsetting('jswizard_forums_startrow', 'parameter[startrow]', intval($parameter['startrow']), 'text');
	showsetting('jswizard_forums_items', 'parameter[items]', intval($parameter['items']), 'text');
	showsetting('jswizard_forums_newwindow', 'parameter[newwindow]', isset($parameter['newwindow']) ? $parameter['newwindow'] : 1, 'radio');
	showsetting('jswizard_forums_orderby', '', '', '<input type="radio" name="parameter[orderby]" value="displayorder" '.$fcheckorderby['displayorder'].'> '.$lang['jswizard_forums_orderby_displayorder'].'<br><input type="radio" name="parameter[orderby]" value="threads" '.$fcheckorderby['threads'].'> '.$lang['jswizard_forums_orderby_threads'].'<br><input type="radio" name="parameter[orderby]" value="posts" '.$fcheckorderby['posts'].'> '.$lang['jswizard_forums_orderby_posts']);
	showtype('', 'bottom');
	echo '<br><center><input type="submit" name="jssubmit" value="'.$lang['submit'].'"></center></form>';
	/* Forums == End == */

	/* Member Rank == Start == */
	$mcheckorderby = array((isset($parameter['orderby']) ? $parameter['orderby'] : 'credits') => 'checked');

	echo '<form method="post" action="admincp.php?action=jswizard&function=memberrank#'.$lang['jswizard_memberrank'].'">';
	showtype('jswizard_memberrank', 'top');
	if($jssubmit && $function == 'memberrank') {
		$jsurl = "function=$function".
			"&startrow=$parameter[startrow]".
			"&items=$parameter[items]".
			"&newwindow=$parameter[newwindow]".
			"&orderby=$parameter[orderby]";
		$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
		echo "<tr bgcolor=\"".ALTBG1."\"><td colspan=\"2\">".
			"<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
			dhtmlspecialchars("<script language=\"JavaScript\" src=\"$jsurl\"></script>").
			"</textarea></td></tr>";
	}
	showsetting('jswizard_memberrank_startrow', 'parameter[startrow]', intval($parameter['startrow']), 'text');
	showsetting('jswizard_memberrank_items', 'parameter[items]', isset($parameter['items']) ? $parameter['items'] : 10, 'text');
	showsetting('jswizard_memberrank_newwindow', 'parameter[newwindow]', isset($parameter['newwindow']) ? $parameter['newwindow'] : 1, 'radio');
	showsetting('jswizard_memberrank_orderby', '', '', '<input type="radio" name="parameter[orderby]" value="credits" '.$mcheckorderby['credits'].'> '.$lang['jswizard_memberrank_orderby_credits'].'<br><input type="radio" name="parameter[orderby]" value="posts" '.$mcheckorderby['posts'].'> '.$lang['jswizard_memberrank_orderby_posts'].'<br><input type="radio" name="parameter[orderby]" value="digestposts" '.$mcheckorderby['digestposts'].'> '.$lang['jswizard_memberrank_orderby_digestposts'].'<br><input type="radio" name="parameter[orderby]" value="regdate" '.$mcheckorderby['regdate'].'> '.$lang['jswizard_memberrank_orderby_regdate'].'<br><input type="radio" name="parameter[orderby]" value="todayposts" '.$mcheckorderby['todayposts'].'> '.$lang['jswizard_memberrank_orderby_todayposts']);
	showtype('', 'bottom');
	echo '<br><center><input type="submit" name="jssubmit" value="'.$lang['submit'].'"></center></form>';
	/* Member Rank == End == */

	/* Stats == Start == */
	$predefined = array('forums', 'threads', 'posts', 'members', 'online', 'onlinemembers');
	echo '<form method="post" action="admincp.php?action=jswizard&function=stats#'.$lang['jswizard_stats'].'"><a name="'.$lang['jswizard_stats'].'"></a>'.
		'<table cellspacing="'.INNERBORDERWIDTH.'" cellpadding="'.TABLESPACE.'" width="90%" align="center" class="tableborder">'.
		'<tr class="header"><td colspan="4">'.$lang['jswizard_stats'].'</td></tr>';
	if($jssubmit && $function == 'stats') {
		$jsurl = "function=$function";
		asort($displayorder);
		foreach($displayorder as $key => $order) {
			if($parameter[$key]['display']) {
				$jsurl .= "&info[$key]=".rawurlencode($parameter[$key]['title']);
			}
		}
		$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
		echo "<tr bgcolor=\"".ALTBG1."\"><td colspan=\"4\">".
			"<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
			dhtmlspecialchars("<script language=\"JavaScript\" src=\"$jsurl\"></script>").
			"</textarea></td></tr>";
	}
	echo '<tr class="category" align="center"><td>'.$lang['jswizard_stats_display'].'</td><td>'.$lang['jswizard_stats_display_title'].'</td><td>'.$lang['jswizard_stats_display_name'].'</td><td>'.$lang['display_order'].'</td></tr>';

	$order = 0;
	foreach($predefined as $key) {
		echo '<tr align="center"><td bgcolor="'.ALTBG1.'"><input type="checkbox" name="parameter['.$key.'][display]" value="1" '.(!isset($parameter[$key]) || $parameter[$key]['display'] ? 'checked' : '').'></td>'.
			'<td bgcolor="'.ALTBG2.'">'.$lang['jswizard_stats_'.$key].'</td>'.
			'<td bgcolor="'.ALTBG1.'"><input type="text" name="parameter['.$key.'][title]" size="20" value="'.($parameter[$key]['title'] ? $parameter[$key]['title'] : $lang['jswizard_stats_'.$key].':').'"></td>'.
			'<td bgcolor="'.ALTBG1.'"><input type="text" name="displayorder['.$key.']" size="3" value="'.(isset($displayorder[$key]) ? intval($displayorder[$key]) : ++$order).'"></td></tr>';
	}
	echo '</table><br><center><input type="submit" name="jssubmit" value="'.$lang['submit'].'"></center></form>';
	/* Stats == End == */

	/* Images == Start == */
	for($i = 1; $i <= 4; $i++) {
		$icheckdigest[$i] = !empty($parameter['digest'][$i]) ? 'checked' : '';
	}

	echo '<form method="post" action="admincp.php?action=jswizard&function=images#'.$lang['jswizard_images'].'">';
	showtype('jswizard_images', 'top');
	if($jssubmit && $function == 'images') {
		$jsurl = "function=$function".
			($parameter['images_forums'] && !in_array('all', $parameter['images_forums'])? '&fids='.jsfids($parameter['images_forums']) : '').
			"&maxwidth=$parameter[maxwidth]".
			"&maxheight=$parameter[maxheight]".
			"&startrow=$parameter[startrow]".
			"&items=$parameter[items]".
			"&digest=".bindec(intval($parameter['digest'][1]).intval($parameter['digest'][2]).intval($parameter['digest'][3]).intval($parameter['digest'][4])).
			"&newwindow=$parameter[newwindow]";
		$jsurl = "{$boardurl}api/javascript.php?$jsurl&verify=".md5($authkey.$jsurl);
		echo "<tr bgcolor=\"".ALTBG1."\"><td colspan=\"2\">".
			"<textarea rows=\"3\" style=\"width: 100%; word-break: break-all\" onMouseOver=\"this.focus()\" onFocus=\"this.select()\">".
			dhtmlspecialchars("<script language=\"JavaScript\" src=\"$jsurl\"></script>").
			"</textarea></td></tr>";
	}
	showsetting('jswizard_images_fids', '', '', jsforumselect('images'));
	showsetting('jswizard_images_maxwidth', 'parameter[maxwidth]', isset($parameter['maxwidth']) ? $parameter['maxwidth'] : 200, 'text');
	showsetting('jswizard_images_maxheight', 'parameter[maxheight]', isset($parameter['maxheight']) ? $parameter['maxheight'] : 200, 'text');
	showsetting('jswizard_images_startrow', 'parameter[startrow]', intval($parameter['startrow']), 'text');
	showsetting('jswizard_images_items', 'parameter[items]', isset($parameter['items']) ? $parameter['items'] : 5, 'text');
	showsetting('jswizard_images_digest', '', '', '<input type="checkbox" name="parameter[digest][1]" value="1" '.$icheckdigest[1].'> '.$lang['jswizard_digest_1'].'<br><input type="checkbox" name="parameter[digest][2]" value="1" '.$icheckdigest[2].'> '.$lang['jswizard_digest_2'].'<br><input type="checkbox" name="parameter[digest][3]" value="1" '.$icheckdigest[3].'> '.$lang['jswizard_digest_3'].'<br><input type="checkbox" name="parameter[digest][4]" value="1" '.$icheckdigest[4].'> '.$lang['jswizard_digest_0']);
	showsetting('jswizard_images_newwindow', 'parameter[newwindow]', isset($parameter['newwindow']) ? $parameter['newwindow'] : 1, 'radio');
	showtype('', 'bottom');
	echo '<br><center><input type="submit" name="jssubmit" value="'.$lang['submit'].'"></center></form>';
	/* Images == End == */

} elseif($action == 'fileperms') {

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['fileperms_tips']?>
</td></tr></table><br><br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['fileperms_check']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>"><br><ul>
<?

	$entryarray	= array	(
				'attachments',
				'forumdata',
				'customavatars',
				'forumdata/viewcount.log',
				'forumdata/dberror.log',
				'forumdata/errorlog.php',
				'forumdata/ratelog.php',
				'forumdata/cplog.php',
				'forumdata/modslog.php',
				'forumdata/illegallog.php'
				);

	foreach(array('templates', 'forumdata/cache', 'forumdata/templates') as $directory) {
		getdirentry($directory);
	}

	$fault = 0;
	foreach($entryarray as $entry) {
		$fullentry = DISCUZ_ROOT.'./'.$entry;
		if(!is_dir($fullentry) && !file_exists($fullentry)) {
			continue;
		} else {
			if(!is_writeable($fullentry)) {
				echo '<li style="color: FF0000">'.(is_dir($fullentry) ? $lang['fileperms_dir'] : $lang['fileperms_file'])." ./$entry $lang[fileperms_unwritable]";
				$fault = 1;
			}
		}
	}
	echo ($fault ? '' : '<li>'.$lang['fileperms_check_ok']).'</ul></td></tr></table>';

}

function getdirentry($directory) {
	global $entryarray;
	$dir = dir(DISCUZ_ROOT.'./'.$directory);
	while($entry = $dir->read()) {
		if($entry != '.' && $entry != '..') {
			if(is_dir(DISCUZ_ROOT.'./'.$directory.'/'.$entry)) {
				$entryarray[] = $directory.'/'.$entry;
				getdirentry($directory."/".$entry);
			} else {
				$entryarray[] = $directory.'/'.$entry;
			}
		}
	}
	$dir->close();
}

function jsforumselect($function) {
	global $parameter, $lang, $db, $tablepre;
	if(empty($function) || in_array($function, array('forums', 'threads', 'images'))) {
		$forumselect = '<select name="parameter['.$function.'_forums][]" size="5" multiple="multiple">'.
			'<option value="all" '.(is_array($parameter[$function.'_forums']) && in_array('all', $parameter[$function.'_forums']) ? 'selected="selected"' : '').'> '.$lang['jswizard_all_forums'].'</option>'.
			'<option value="">&nbsp;</option>';
		if($function == 'forums') {
			$query = $db->query("SELECT fid, name FROM {$tablepre}forums WHERE type='group' AND status='1' ORDER BY displayorder");
			while($category = $db->fetch_array($query)) {
				$forumselect .= '<option value="'.$category['fid'].'">'.strip_tags($category['name']).'</option>';
			}
		} else {
			require_once DISCUZ_ROOT.'./include/forum.func.php';
			$forumselect .= forumselect();
		}
		$forumselect .= '</select>';

		if(is_array($parameter[$function.'_forums'])) {
			foreach($parameter[$function.'_forums'] as $key => $value) {
				if(!$value) {
					unset($parameter[$function.'_forums'][$key]);
				}
			}
			if(!in_array('all', $parameter[$function.'_forums'])) {
				$forumselect = preg_replace("/(\<option value=\"(".implode('|', $parameter[$function.'_forums']).")\")(\>)/", "\\1 selected=\"selected\"\\3", $forumselect);
			}
		}
		return $forumselect;
	}
}

function jsfids($fidarray) {
	foreach($fidarray as $key => $val) {
		if(empty($val)) {
			unset($fidarray[$key]);
		}
	}
	return implode('_', $fidarray);
}

?>