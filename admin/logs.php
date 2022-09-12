<?php

/*
	[DISCUZ!] admin/logs.php - view board logs
	This is NOT a freeware, use is subject to license terms

	Version: 3.1.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2003/8/30 10:36
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

cpheader();

$logs = array();
$logspan = $timestamp - 86400 * 15;
$lpp = empty($lpp) ? 50 : $lpp;

if(!in_array($action, array('illegallog', 'karmalog', 'modslog', 'cplog'))) {
	cpmsg('undefined_action');
}

$filename = DISCUZ_ROOT.'./forumdata/'.$action.'.php';
@$logfile = file($filename);
@$fp = fopen($filename, 'w');
@flock($fp, 2);
@fwrite($fp, "<?PHP exit('Access Denied'); ?>\n");

foreach($logfile as $logrow) {
	if(intval($logrow) > $logspan && strpos($logrow, "\t")) {
		$logs[] = $logrow;
		@fwrite($fp, $logrow."\n");
	}
}
@fclose($fp);

if(!$page) {
	$page = 1;
}

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



$lognames = array('illegallog' => 'logs_passwd', 'karmalog' => 'logs_karma', 'modslog' => 'logs_moderate', 'cplog' => 'logs_cp');

?>
<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="0" width="100%">
<tr><td>
<table border="0" cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td colspan="3"><?=$lang[$lognames[$action]]?></td></tr>

<form method="post" action="admincp.php?action=<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG2?>"><td width="25%"><?=$lang['logs_lpp']?></td>
<td width="55%"><input type="text" name="lpp" size="40" maxlength="40" value="<?=$lpp?>"></td>
<td width="20%"><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="post" action="admincp.php?action=<?=$action?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['logs_search']?></td><td><input type="text" name="keyword" size="40" value="<?=htmlspecialchars($keyword)?>"></td>
<td><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

</table></td></tr></table></td></tr></table><br><br>

<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr class="multi"><td><?=$multipage?></td></tr>
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<?

if(in_array($action, array('karmalog', 'modslog', 'cplog'))) {
	$usergroup = array();
	$query = $db->query("SELECT groupid, grouptitle FROM $table_usergroups");
	while($group = $db->fetch_array($query)) {
		$usergroup[$group['groupid']] = $group['grouptitle'];
	}
}

if($action == 'illegallog') {

	echo "<tr class=\"header\" align=\"center\"><td>$lang[logs_passwd_username]</td><td>$lang[logs_passwd_password]</td><td>$lang[logs_passwd_security]</td><td>$lang[ip]</td><td>$lang[time]</td></tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[1] = stripslashes($log[1]);
		if(strtolower($log[1]) == strtolower($discuz_userss)) {
			$log[1] = "<b>$log[1]</b>";
		}

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\">$log[1]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[4]</td><td bgcolor=\"".ALTBG1."\">$log[0]</td></tr>\n";
	}

} elseif($action == 'karmalog') {

	echo "<tr class=\"header\" align=\"center\"><td width=\"15%\">$lang[username]</td><td width=\"12%\">$lang[usergroup]</td><td width=\"18%\">$lang[time]</td><td width=\"15%\">$lang[logs_karma_username]</td><td width=\"8%\">$lang[logs_karma_rating]</td><td width=\"28%\">$lang[subject]</td></tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[1] = "<a href=\"viewpro.php?username=".rawurlencode($log[1])."\" target=\"_blank\">$log[1]";
		$log[2] = $usergroup[$log[2]];
		$log[3] = "<a href=\"viewpro.php?username=".rawurlencode($log[3])."\" target=\"_blank\">$log[3]</a>";
		if($log[3] == $discuz_userss) {
			$log[3] = "<b>$log[3]</b>";
		}
		$log[4] = $log[4] < 0 ? "<b>$log[4]</b>" : $log[4];
		$log[6] = "<a href=\"./viewthread.php?tid=$log[5]\" target=\"_blank\" title=\"$log[6]\">".cutstr($log[6], 20)."</a>";

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\">$log[1]</a></td><td bgcolor=\"".ALTBG2."\">$log[2]</td>\n".
			"<td bgcolor=\"".ALTBG1."\">$log[0]</td><td bgcolor=\"".ALTBG2."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG1."\">$log[4]</td><td bgcolor=\"".ALTBG2."\">$log[6]</td></tr>\n";
	}

} elseif($action == 'modslog') {

	echo "<tr class=\"header\" align=\"center\"><td width=\"10%\">$lang[operator]</td><td width=\"15%\">$lang[usergroup]</td><td width=\"10%\">$lang[ip]</td><td width=\"18%\">$lang[time]</td><td width=\"15%\">$lang[forum]</td><td width=\"19%\">$lang[thread]</td><td width=\"13%\">$lang[action]</td></tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[1] = stripslashes($log[1]);
		if($log[1] != $discuz_user) {
			$log[1] = "<b>$log[1]</b>";
		}
		$log[2] = $usergroup[$log[2]];
		$log[5] = "<a href=\"./forumdisplay.php?fid=$log[4]\" target=\"_blank\">$log[5]</a>";
		$log[7] = "<a href=\"./viewthread.php?tid=$log[6]\" target=\"_blank\" title=\"$log[7]\">".cutstr($log[7], 15)."</a>";

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\">$log[1]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[0]</td><td bgcolor=\"".ALTBG1."\">$log[5]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[7]</td><td bgcolor=\"".ALTBG1."\">$log[8]</td></tr>\n";
	}

} elseif($action == 'cplog') {

	echo "<tr class=\"header\" align=\"center\"><td width=\"10%\">$lang[operator]</td><td width=\"10%\">$lang[usergroup]</td><td width=\"10%\">$lang[ip]</td><td width=\"18%\">$lang[time]</td><td width=\"15%\">$lang[action]</td><td width=\"37%\">$lang[other]</td></tr>\n";

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		$log[0] = gmdate('y-n-j H:i', $log[0] + $timeoffset * 3600);
		$log[1] = stripslashes($log[1]);
		if($log[1] != $discuz_user) {
			$log[1] = "<b>$log[1]</b>";
		}
		$log[2] = $usergroup[$log[2]];

		echo "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\">$log[1]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[2]</td><td bgcolor=\"".ALTBG1."\">$log[3]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[0]</td><td bgcolor=\"".ALTBG1."\">$log[4]</td>\n".
			"<td bgcolor=\"".ALTBG2."\">$log[5]</td></tr>\n";
	}

}

?>
</table></td></tr><tr class="multi"><td><?=$multipage?></td></tr>
</table>