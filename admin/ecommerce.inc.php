<?php

/*
	[DISCUZ!] admin/ecommerce.inc.php - e-commerce configuration
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-8-1 21:28
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'alipay') {

	$settings = array();
	$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable LIKE 'ec_%'");
	while($setting = $db->fetch_array($query)) {
		$settings[$setting['variable']] = $setting['value'];
	}

	$settings['ec_securitycode'] = authcode($settings['ec_securitycode'], 'DECODE', $authkey);

	if(!submitcheck('alipaysubmit')) {

		if(strlen($settings['ec_securitycode']) >= 32) {
			$settings['ec_securitycode'] = substr($settings['ec_securitycode'], 0, 8).'************************';
		} else {
			$settings['ec_securitycode'] = '';
		}

?>
<form method="post" name="settings" action="admincp.php?action=alipay">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['alipay_tips']?>
</td></tr></table><br>
<?

		showtype('alipay', 'top');
		showsetting('alipay_ratio', 'settingsnew[ec_ratio]', $settings['ec_ratio'], 'text');
		showsetting('alipay_account', 'settingsnew[ec_account]', $settings['ec_account'], 'text');
		showsetting('alipay_securitycode', 'settingsnew[ec_securitycode]', $settings['ec_securitycode'], 'text');
		showsetting('alipay_mincredits', 'settingsnew[ec_mincredits]', $settings['ec_mincredits'], 'text');
		showsetting('alipay_maxcredits', 'settingsnew[ec_maxcredits]', $settings['ec_maxcredits'], 'text');
		showsetting('alipay_maxcreditspermonth', 'settingsnew[ec_maxcreditspermonth]', $settings['ec_maxcreditspermonth'], 'text');
		showtype('', 'bottom');

		echo '<br><center><input type="submit" name="alipaysubmit" value="'.$lang['submit'].'"></form>';

	} else {

		if($settingsnew['ec_ratio']) {
			if(preg_replace("/^([a-z0-9]{8})(\*{24})$/", "\\1", $settingsnew['ec_securitycode']) == substr($settings['ec_securitycode'], 0, 8)) {
				$settingsnew['ec_securitycode'] = $settings['ec_securitycode'];
			}
			if($settingsnew['ec_ratio'] < 0) {
				cpmsg('alipay_ratio_invalid');
			} elseif(!isemail($settingsnew['ec_account'])) {
				cpmsg('alipay_account_invalid');
			} elseif(!preg_match("/^[a-z0-9]{32}$/", $settingsnew['ec_securitycode'])) {
				cpmsg('alipay_securitycode_invalid');
			}
			$settingsnew['ec_securitycode'] = authcode($settingsnew['ec_securitycode'], 'ENCODE', $authkey);
		} else {
			$settingsnew['ec_account'] = $settingsnew['ec_securitycode'] = '';
			$settingsnew['ec_mincredits'] = $settingsnew['ec_maxcredits'] = 0;
		}

		foreach(array('ec_ratio', 'ec_mincredits', 'ec_maxcredits', 'ec_maxcreditspermonth') as $key) {
			$settingsnew[$key] = intval($settingsnew[$key]);
		}		

		if(is_array($settingsnew)) {
			foreach($settingsnew as $variable => $value) {
				$db->query("UPDATE {$tablepre}settings SET value='$value' WHERE variable='$variable'");
			}
		}
		updatecache('settings');

		cpmsg('alipay_succeed');

	}

} elseif($action == 'orders') {

	if(!$creditstrans || !$ec_ratio) {
		cpmsg('orders_disabled');
	}

	if(!submitcheck('ordersubmit')) {

		$statusselect = array(($orderstatus = intval($orderstatus)) => 'selected');

		$orderid = dhtmlspecialchars($orderid);
		$users = dhtmlspecialchars($users);
		$buyer = dhtmlspecialchars($buyer);
		$admin = dhtmlspecialchars($admin);
		$sstarttime = dhtmlspecialchars($sstarttime);
		$sendtime = dhtmlspecialchars($sendtime);
		$cstarttime = dhtmlspecialchars($cstarttime);
		$cendtime = dhtmlspecialchars($cendtime);

?>
<br><form method="post" action="admincp.php?action=orders">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['orders_tips']?>
</td></tr></table><br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">

<tr>
<td class="header" colspan="2"><?=$lang['orders_search']?></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['orders_search_status']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<select name="orderstatus">
<option value="0" <?=$statusselect[0]?>> <?=$lang['orders_search_status_all']?></option>
<option value="1" <?=$statusselect[1]?>> <?=$lang['orders_search_status_pending']?></option>
<option value="2" <?=$statusselect[2]?>> <?=$lang['orders_search_status_auto_finished']?></option>
<option value="3" <?=$statusselect[3]?>> <?=$lang['orders_search_status_manual_finished']?></option>
</select>
</td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>" width="60%"><?=$lang['orders_search_id']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="orderid" size="40" value="<?=$orderid?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['orders_search_users']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="users" size="40" value="<?=$users?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['orders_search_buyer']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="buyer" size="40" value="<?=$buyer?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['orders_search_admin']?></td>
<td bgcolor="<?=ALTBG2?>" align="right"><input type="text" name="admin" size="40" value="<?=$admin?>"></td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['orders_search_submit_date']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<input type="text" name="sstarttime" size="10" value="<?=$sstarttime?>"> - 
<input type="text" name="sendtime" size="10" value="<?=$sendtime?>"
</td>
</tr>

<tr>
<td bgcolor="<?=ALTBG1?>"><?=$lang['orders_search_confirm_date']?></td>
<td bgcolor="<?=ALTBG2?>" align="right">
<input type="text" name="cstarttime" size="10" value="<?=$cstarttime?>"> - 
<input type="text" name="cendtime" size="10" value="<?=$cendtime?>"
</td>
</tr>

</table><br>
<center><input type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		$numvalidate = 0;
		if($validate) {
			$orderids = $comma = '';
			$confirmdate = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);

			$query = $db->query("SELECT * FROM {$tablepre}orders WHERE orderid IN ('".implode('\',\'', $validate)."') AND status='1'");
			while($order = $db->fetch_array($query)) {
				$db->query("UPDATE {$tablepre}members SET extcredits$creditstrans=extcredits$creditstrans+'$order[amount]' WHERE uid='$order[uid]'");
				$orderids .= "$comma'$order[orderid]'";
				$comma = ',';

				$submitdate = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $order['submitdate'] + $_DCACHE['settings']['timeoffset'] * 3600);
				sendpm($order['uid'], 'addfunds_subject', 'addfunds_message', $fromid = '0', $from = 'System Message');
			}
			if($numvalidate = $db->num_rows($query)) {
				$db->query("UPDATE {$tablepre}orders SET status='3', admin='$discuz_user', confirmdate='$timestamp' WHERE orderid IN ($orderids)");
			}
		}

		cpmsg('orders_validate_succeed', "admincp.php?action=orders&searchsubmit=yes&orderstatus=$orderstatus&orderid=$orderid&users=$users&buyer=$buyer&admin=$admin&sstarttime=$sstarttime&sendtime=$sendtime&cstarttime=$cstarttime&cendtime=$cendtime");

	}

	if(submitcheck('searchsubmit', 1)) {

		$page = !ispage($page) ? 1 : $page;
		$start_limit = ($page - 1) * $tpp;

		$sql = '';
		$sql .= $orderstatus != ''	? " AND o.status='$orderstatus'" : '';
		$sql .= $orderid != ''		? " AND o.orderid='$orderid'" : '';
		$sql .= $users != ''		? " AND m.username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $users))."')" : '';
		$sql .= $buyer != ''		? " AND o.buyer='$buyer'" : '';
		$sql .= $admin != ''		? " AND o.admin='$admin'" : '';
		$sql .= $sstarttime != ''	? " AND o.submitdate>='".(strtotime($sstarttime) - $timeoffset * 3600)."'" : '';
		$sql .= $sendtime != ''		? " AND o.submitdate<'".(strtotime($sendtime) - $timeoffset * 3600)."'" : '';
		$sql .= $cstarttime != ''	? " AND o.confirmdate>='".(strtotime($cstarttime) - $timeoffset * 3600)."'" : '';
		$sql .= $cendtime != ''		? " AND o.confirmdate<'".(strtotime($cendtime) - $timeoffset * 3600)."'" : '';

		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}orders o, {$tablepre}members m WHERE m.uid=o.uid $sql");
		$ordercount = $db->result($query, 0);

		$multipage = multi($ordercount, $tpp, $page, "admincp.php?action=orders&searchsubmit=yes&orderstatus=$orderstatus&orderid=$orderid&users=$users&buyer=$buyer&admin=$admin&sstarttime=$sstarttime&sendtime=$sendtime&cstarttime=$cstarttime&cendtime=$cendtime");

		$orders = '';
		$query = $db->query("SELECT o.*, m.username
			FROM {$tablepre}orders o, {$tablepre}members m
			WHERE m.uid=o.uid $sql ORDER BY o.submitdate DESC
			LIMIT $start_limit, $tpp");

		while($order = $db->fetch_array($query)) {
			switch($order['status']) {
				case 1: $order['orderstatus'] = $lang['orders_search_status_pending']; break;
				case 2: $order['orderstatus'] = '<b>'.$lang['orders_search_status_auto_finished'].'</b>'; break;
				case 3: $order['orderstatus'] = '<b>'.$lang['orders_search_status_manual_finished'].'</b><br>(<a href="viewpro.php?username='.rawurlencode($order['admin']).'" target="_blank">'.$order['admin'].'</a>)'; break;
			}
			$order['submitdate'] = gmdate("$dateformat $timeformat", $order['submitdate'] + $timeoffset * 3600);
			$order['confirmdate'] = $order['confirmdate'] ? gmdate("$dateformat $timeformat", $order['confirmdate'] + $timeoffset * 3600) : 'N/A';

			$orders .= "<tr align=\"center\" class=\"smalltxt\"><td class=\"altbg1\"><input type=\"checkbox\" name=\"validate[]\" value=\"$order[orderid]\" ".($order['status'] != 1 ? 'disabled' : '')."></td>\n".
				"<td class=\"altbg2\">$order[orderid]</td>\n".
				"<td class=\"altbg1\">$order[orderstatus]</td>\n".
				"<td class=\"altbg2\"><a href=\"viewpro.php?uid=$order[uid]\" target=\"_blank\">$order[username]</a></td>\n".
				"<td class=\"altbg1\"><a href=\"mailto:$order[buyer]\">$order[buyer]</a></td>\n".
				"<td class=\"altbg2\">{$extcredits[$creditstrans]['title']} $order[amount] {$extcredits[$creditstrans]['unit']}</td>\n".
				"<td class=\"altbg1\">$lang[rmb] $order[price] $lang[rmb_yuan]</td>\n".
				"<td class=\"altbg2\">$order[submitdate]</td>\n".
				"<td class=\"altbg1\">$order[confirmdate]</td></tr>\n";
		}

?>
<form method="post" action="admincp.php?action=orders">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr align="center" class="header">
<td><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['orders_validate']?></td>
<td><?=$lang['orders_id']?></td><td><?=$lang['orders_status']?></td><td><?=$lang['orders_username']?></td><td><?=$lang['orders_buyer']?></td>
<td><?=$lang['orders_amount']?></td><td><?=$lang['orders_price']?></td><td><?=$lang['orders_submitdate']?></td><td><?=$lang['orders_confirmdate']?></td></tr>
<?=$orders?>
</table>

<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr><td class="multi"><?=$multipage?></td></tr></table><br><center>
<input type="submit" name="ordersubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	}

}


?>