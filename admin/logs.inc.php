<?php

/*
	[DISCUZ!] admin/logs.inc.php - view board logs
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-5-10 14:51
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

$logs = array();
$logspan = $timestamp - 86400 * 15;
$lpp = empty($lpp) ? 50 : $lpp;

if(!in_array($action, array('illegallog', 'ratelog', 'modslog', 'medalslog', 'banlog', 'cplog', 'errorlog'))) {
	cpmsg('undefined_action');
}

$filename = DISCUZ_ROOT.'./forumdata/'.$action.'.php';
@$logfile = file($filename);
@$fp = fopen($filename, 'w');
@flock($fp, 2);
@fwrite($fp, "<?PHP exit('Access Denied'); ?>\n");

if(is_array($logfile)) {
	foreach($logfile as $logrow) {
		if(intval($logrow) > $logspan && strpos($logrow, "\t")) {
			$logs[] = $logrow;
			@fwrite($fp, trim($logrow)."\n");
		}
	}
}
@fclose($fp);

$page = !ispage($page) ? 1 : $page;
$start = ($page - 1) * $lpp;
$logs = array_reverse($logs);

if(empty($keyword)) {
	$num = count($logs);
	$multipage = multi($num, $lpp, $page, "admincp.php?action=$action&lpp=$lpp");

	for($i = 0; $i < $start; $i++) {
		unset($logs[$i]);
	}
	for($i = $start + $lpp; $i < $num; $i++) {
		unset($logs[$i]);
	}
} else {
	foreach($logs as $key => $value) {
		if(strpos($value, $keyword) === FALSE) {
			unset($logs[$key]);
		}
	}
	$multipage = '';
}



$lognames = array
	(
	'illegallog'	=> 'logs_passwd',
	'ratelog'	=> 'logs_rating',
	'modslog'	=> 'logs_moderate',
	'medalslog'	=> 'logs_medal',
	'banlog'	=> 'logs_banned',
	'cplog'		=> 'logs_cp',
	'errorlog'	=> 'logs_error'
	);

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="0" width="98%" align="center" class="tableborder">
<tr><td><table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="3"><?=$lang[$lognames[$action]]?></td></tr>


<form method="post" action="admincp.php?action=<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG2?>"><td width="25%"><?=$lang['logs_lpp']?></td>
<td width="55%"><input type="text" name="lpp" size="40" maxlength="40" value="<?=$lpp?>"></td>
<td width="20%"><input type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

<form method="post" action="admincp.php?action=<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['logs_search']?></td><td><input type="text" name="keyword" size="40" value="<?=dhtmlspecialchars($keyword)?>"></td>
<td><input type="submit" value="<?=$lang['submit']?>"></td></tr>
</form>

</table></td></tr></table><br><br>

<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<?

$usergroup = array();
if(in_array($action, array('ratelog', 'modslog', 'banlog', 'cplog'))) {
	$query = $db->query("SELECT groupid, grouptitle FROM {$tablepre}usergroups");
	while($group = $db->fetch_array($query)) {
		$usergroup[$group['groupid']] = $group['grouptitle'];
	}
}

if($action == 'illegallog') {

	echo "<tr class=\"header\" align=\"center\">".
		"<td>$lang[logs_passwd_username]</td>".
		"<td>$lang[logs_passwd_password]</td>".
		"<td>$lang[logs_passwd_security]</td>".
		"<td>$lang[ip]</td>".
		"<td>$lang[time]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		if(strtolower($log[1]) == strtolower($discuz_userss)) {
			$log[1] = "<b>$log[1]</b>";
		}
		$log[4] = $allowviewip ? $log[4] : '-';

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\">$log[1]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[4]</td><td bgcolor=\"".ALTBG1."\">$log[0]</td></tr>\n";
	}

} elseif($action == 'ratelog') {

	echo "<tr class=\"header\" align=\"center\">".
		"<td width=\"13%\">$lang[username]</td>".
		"<td width=\"12%\">$lang[usergroup]</td>".
		"<td width=\"12%\">$lang[time]</td>".
		"<td width=\"13%\">$lang[logs_rating_username]</td>".
		"<td width=\"14%\">$lang[logs_rating_rating]</td>".
		"<td width=\"23%\">$lang[subject]</td>".
		"<td width=\"13%\">$lang[reason]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[1] = "<a href=\"viewpro.php?username=".rawurlencode($log[1])."\" target=\"_blank\">$log[1]</a>";
		$log[2] = $usergroup[$log[2]];
		if($log[3] == $discuz_userss) {
			$log[3] = "<b>$log[3]</b>";
		}
		$log[3] = "<a href=\"viewpro.php?username=".rawurlencode($log[3])."\" target=\"_blank\">$log[3]</a>";
		$log[5] = $extcredits[$log[4]]['title'].' '.($log[5] < 0 ? "<b>$log[5]</b>" : "+$log[5]").' '.$extcredits[$log[4]]['unit'];
		$log[6] = $log[6] ? "<a href=\"./viewthread.php?tid=$log[6]\" target=\"_blank\" title=\"$log[7]\">".cutstr($log[7], 20)."</a>" : "<i>$lang[logs_rating_manual]</i>";

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\">$log[1]</a></td><td bgcolor=\"".ALTBG2."\">$log[2]</td>\n".
			"<td bgcolor=\"".ALTBG1."\">$log[0]</td><td bgcolor=\"".ALTBG2."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG1."\">$log[5]</td><td bgcolor=\"".ALTBG2."\">$log[6]</td>\n".
			"<td bgcolor=\"".ALTBG1."\">$log[8]</td></tr>\n";
	}

} elseif($action == 'modslog') {

	include language('modactions');

	echo "<tr class=\"header\" align=\"center\">".
		"<td width=\"13%\">$lang[operator]</td>".
		"<td width=\"10%\">$lang[usergroup]</td>".
		"<td width=\"10%\">$lang[ip]</td>".
		"<td width=\"16%\">$lang[time]</td>".
		"<td width=\"12%\">$lang[forum]</td>".
		"<td width=\"19%\">$lang[thread]</td>".
		"<td width=\"10%\">$lang[action]</td>".
		"<td width=\"10%\">$lang[reason]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[1] = stripslashes($log[1]);
		$log[2] = $usergroup[$log[2]];
		$log[3] = $allowviewip ? $log[3] : '-';
		$log[5] = "<a href=\"./forumdisplay.php?fid=$log[4]\" target=\"_blank\">$log[5]</a>";
		$log[7] = "<a href=\"./viewthread.php?tid=$log[6]\" target=\"_blank\" title=\"$log[7]\">".cutstr($log[7], 15)."</a>";
		$log[8] = $modactioncode[trim($log[8])];

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><a href=\"viewpro.php?username=".rawurlencode($log[1])."\" target=\"_blank\">".($log[1] != $discuz_userss ? "<b>$log[1]</b>" : $log[1])."</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[0]</td><td bgcolor=\"".ALTBG1."\">$log[5]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[7]</td><td bgcolor=\"".ALTBG1."\">$log[8]</td>\n".
			"<td bgcolor=\"".ALTBG1."\">$log[9]</td></tr>\n";
	}

} elseif($action == 'medalslog') {

	$medalsarray = array();
	$query = $db->query("SELECT * FROM {$tablepre}medals WHERE available>'0'");
	while($medal = $db->fetch_array($query)) {
		$medalsarray[$medal['medalid']] = "<img src=\"images/common/$medal[image]\" border=\"0\" align=\"absmiddle\"> $medal[name]";
	}

	echo "<tr class=\"header\" align=\"center\">".
		"<td width=\"13%\">$lang[operator]</td>".
		"<td width=\"13%\">$lang[ip]</td>".
		"<td width=\"13%\">$lang[time]</td>".
		"<td width=\"13%\">$lang[username]</td>".
		"<td width=\"7%\">$lang[action]</td>".
		"<td width=\"18%\">$lang[logs_medal_name]</td>".
		"<td width=\"23%\">$lang[reason]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[2] = $allowviewip ? $log[2] : '-';
		$log[3] = "<a href=\"viewpro.php?username=".rawurlencode($log[3])."\" target=\"_blank\">$log[3]</a>";
		$log[4] = isset($medalsarray[$log[4]]) ? $medalsarray[$log[4]] : $lang['members_edit_medal_unavailable'];
		$log[5] = $lang['members_edit_medal_'.$log[5]];

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><a href=\"viewpro.php?username=".rawurlencode($log[1])."\" target=\"_blank\">".($log[1] != $discuz_userss ? "<b>$log[1]</b>" : $log[1])."</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[0]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[3]</td><td bgcolor=\"".ALTBG1."\">$log[5]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[4]</td><td bgcolor=\"".ALTBG2."\">$log[6]</td></tr>\n";
	}

} elseif($action == 'banlog') {

	echo "<tr class=\"header\" align=\"center\">".
		"<td width=\"10%\">$lang[operator]</td>".
		"<td width=\"10%\">$lang[usergroup]</td>".
		"<td width=\"10%\">$lang[ip]</td>".
		"<td width=\"10%\">$lang[time]</td>".
		"<td width=\"10%\">$lang[username]</td>".
		"<td width=\"5%\">$lang[operation]</td>".
		"<td width=\"20%\">$lang[logs_banned_group]</td>".
		"<td width=\"8%\">$lang[validity]</td>".
		"<td width=\"17%\">$lang[reason]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[3] = $allowviewip ? $log[3] : '-';
		$log[2] = $usergroup[$log[2]];
		$log[7] = trim($log[7]) ? gmdate('y-n-j', $log[7] + $timeoffset * 3600) : '';

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><a href=\"viewpro.php?username=".rawurlencode($log[1])."\" target=\"_blank\">$log[1]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[0]</td><td bgcolor=\"".ALTBG1."\"><a href=\"viewpro.php?username=".rawurlencode($log[4])."\" target=\"_blank\">$log[4]</a></td>\n".
			"<td bgcolor=\"".ALTBG2."\">".(in_array($log[5], array(4, 5)) && !in_array($log[6], array(4, 5)) ? '<i>'.$lang['logs_banned_unban'].'</i>' : '<b>'.$lang['logs_banned_ban'].'</b>')."</td>".
			"<td bgcolor=\"".ALTBG1."\">{$usergroup[$log[5]]} / {$usergroup[$log[6]]}</td><td bgcolor=\"".ALTBG1."\">$log[7]</td>\n".
			"<td bgcolor=\"".ALTBG1."\">$log[8]</td></tr>\n";
	}

} elseif($action == 'cplog') {

	echo "<tr class=\"header\" align=\"center\">".
		"<td width=\"10%\">$lang[operator]</td>".
		"<td width=\"10%\">$lang[usergroup]</td>".
		"<td width=\"10%\">$lang[ip]</td>".
		"<td width=\"18%\">$lang[time]</td>".
		"<td width=\"15%\">$lang[action]</td>".
		"<td width=\"37%\">$lang[other]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[1] = stripslashes($log[1]);
		$log[2] = $usergroup[$log[2]];
		$log[3] = $allowviewip ? $log[3] : '-';

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><a href=\"viewpro.php?username=".rawurlencode($log[1])."\" target=\"_blank\">".($log[1] != $discuz_userss ? "<b>$log[1]</b>" : $log[1])."</a></td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[0]</td><td bgcolor=\"".ALTBG1."\">$log[4]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[5]</td></tr>\n";
	}

} elseif($action == 'errorlog') {

	echo "<tr class=\"header\" align=\"center\">".
		"<td width=\"8%\">$lang[type]</td>".
		"<td width=\"15%\">$lang[username]</td>".
		"<td width=\"15%\">$lang[time]</td>".
		"<td width=\"62%\">$lang[message]</td>".
		"</tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\">$log[1]</td>\n".
			"<td bgcolor=\"".ALTBG2."\"><a href=\"viewpro.php?username=".rawurlencode($log[2])."\" target=\"_blank\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[0]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[3]</td></tr>\n";
	}

}
?>
</table>

<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>