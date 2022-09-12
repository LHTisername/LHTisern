<?php

/*
	[DISCUZ!] admin/newsletter.php - send board newsletter by P.M. or email
	This is NOT a freeware, use is subject to license terms

	Version: 2.5.0 beta
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2003/5/12 03:35
*/

if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
}

cpheader();

if(!submitcheck('newslettersubmit')) {

	$count = 0;
	$usergroups = '';
	$query = $db->query("SELECT groupid, grouptitle FROM $table_usergroups ORDER BY type, creditslower");
	while($group = $db->fetch_array($query)) {
		$usergroups .= ($count++ % 3 == 0 ? '</tr><tr>' : '').
			"<td width=\"33%\" nowrap><input type=\"checkbox\" name=\"sendto[]\" value=\"$group[groupid]\"> $group[grouptitle]</td>";
	}

?>
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['newsletter_tips']?>
</td></tr></table></td></tr></table>

<br><br><form method="post" action="admincp.php?action=newsletter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table border="0" cellspacing="<?=BORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="100%">
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

</table></td></tr></table><br>
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
	$query = $db->query("SELECT uid, email FROM $table_members WHERE groupid IN ($ids) AND newsletter='1'");
	while($member = $db->fetch_array($query)) {
		if($sendvia == 'pm') {
			$db->query("INSERT INTO $table_pms (msgfrom, msgfromid, msgtoid, folder, new, subject, dateline, message)
				VALUES('$discuz_user', '$discuz_uid', '$member[uid]', 'inbox', '1', '$subject', '$timestamp', '$message')");
		} elseif($sendvia == 'email') {
			$emails .= $comma.$member['email'];
			$comma = ',';
		}
	}

	if($sendvia == 'pm') {
		$db->query("UPDATE $table_members SET newpm='1' WHERE groupid IN ($ids) AND newsletter='1'");
	} elseif($sendvia == 'email') {
		sendmail($emails, $subject, $message);
	}

	cpmsg('newsletter_succeed');

}

?>