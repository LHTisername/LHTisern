<?php

/*
	[DISCUZ!] admin/menu.php - menu of administrators' control panel
	This is NOT a freeware, use is subject to license terms

	Version: 3.1.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2003/8/6 04:26
*/

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=CHARSET?>">
<? include template('css'); ?>
</head>

<body leftmargin="3" topmargin="3">

<br><table cellspacing="0" cellpadding="0" border="0" width="100%" align="center" style="table-layout: fixed">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table width="100%" border="0" cellspacing="1" cellpadding="0">
<tr><td bgcolor="#FFFFFF">
<table width="100%" border="0" cellspacing="3" cellpadding="<?=TABLESPACE?>" class="smalltxt">
<tr><td bgcolor="<?=ALTBG1?>" align="center"><a href="admincp.php?action=menu&expand=1_2_3_4_5_6_7_8_9_10">[EXPAND]</a> &nbsp; <a href="admincp.php?action=menu&expand=0">[REDUCE]</a></td></tr>
<?

		if(preg_match("/(^|_)$change($|_)/is", $expand)) {
			$expandlist = explode('_', $expand);
			$expand = $underline = '';
			foreach($expandlist as $count) {
				if($count != $change) {
					$expand .= $underline.$count;
					$underline = '_';
				}
			}
		} else {
			$expand .= isset($expand) ? '_'.$change : $change;
		}

		if($expand || $expand == '0') {
			setcookie('expand_menu', $expand, $timestamp + 2592000, $cookiepath, $cookiedomain);
		} else {
			$expand = $HTTP_COOKIE_VARS['expand_menu'];
		}

		$pluginsarray = array();
		if(is_array($plugins)) {
			foreach($plugins as $plugin) {
				if($plugin['name'] && $plugin['cpurl']) {
					$pluginsarray[] = array('name' => $plugin['name'], 'url' => $plugin['cpurl']);
				}
			}
		}

		$menucount = 0;

		if($adminid == 1) {
			showmenu($lang['menu_home'],	'admincp.php?action=home');
			showmenu($lang['menu_options'],	'admincp.php?action=settings');
			showmenu($lang['menu_forums'],	array(array('name' => $lang['menu_forums_add'], 'url' => 'admincp.php?action=forumadd'),
							array('name' => $lang['menu_forums_edit'], 'url' => 'admincp.php?action=forumsedit'),
							array('name' => $lang['menu_forums_merge'], 'url' => 'admincp.php?action=forumsmerge')));
			showmenu($lang['menu_groups'],	array(array('name' => $lang['menu_admingroups'], 'url' => 'admincp.php?action=admingroups'),
							array('name' => $lang['menu_usergroups'], 'url' => 'admincp.php?action=usergroups'),
							array('name' => $lang['menu_ranks'], 'url' => 'admincp.php?action=ranks')));
			showmenu($lang['menu_members'], array(array('name' => $lang['menu_members_add'], 'url' => 'admincp.php?action=addmember'),
							array('name' => $lang['menu_members_edit'], 'url' => 'admincp.php?action=members'),
							array('name' => $lang['menu_members_profile_fields'], 'url' => 'admincp.php?action=profilefields'),
							array('name' => $lang['menu_members_ipban'], 'url' => 'admincp.php?action=ipban')));
			showmenu($lang['menu_styles'],	array(array('name' => $lang['menu_styles'], 'url' => 'admincp.php?action=styles'),
							array('name' => $lang['menu_styles_templates'], 'url' => 'admincp.php?action=templates')));
			showmenu($lang['menu_posting'],	array(array('name' => $lang['menu_posting_discuzcodes'], 'url' => 'admincp.php?action=discuzcodes'),
							array('name' => $lang['menu_posting_censors'], 'url' => 'admincp.php?action=censor'),
							array('name' => $lang['menu_posting_smilies'], 'url' => 'admincp.php?action=smilies'),
							array('name' => $lang['menu_posting_attachtypes'], 'url' => 'admincp.php?action=attachtypes')));
			showmenu($lang['menu_misc'], 	array(array('name' => $lang['menu_misc_announces'], 'url' => 'admincp.php?action=announcements'),
							array('name' => $lang['menu_misc_onlinelist'], 'url' => 'admincp.php?action=onlinelist'),
							array('name' => $lang['menu_misc_links'], 'url' => 'admincp.php?action=forumlinks')));
			showmenu($lang['menu_database'],array(array('name' => $lang['menu_database_export'], 'url' => 'admincp.php?action=export'),
							array('name' => $lang['menu_database_import'], 'url' => 'admincp.php?action=import'),
							array('name' => $lang['menu_database_query'], 'url' => 'admincp.php?action=runquery'),
							array('name' => $lang['menu_database_optimize'], 'url' => 'admincp.php?action=optimize')));
			showmenu($lang['menu_maint'],	array(array('name' => $lang['menu_maint_attaches'], 'url' => 'admincp.php?action=attachments'),
							array('name' => $lang['menu_maint_moderate'], 'url' => 'admincp.php?action=moderate'),
							array('name' => $lang['menu_maint_prune'], 'url' => 'admincp.php?action=prune'),
							array('name' => $lang['menu_maint_pmprune'], 'url' => 'admincp.php?action=pmprune')));
			showmenu($lang['menu_tools'],	array(array('name' => $lang['menu_tools_news'], 'url' => 'admincp.php?action=newsletter'),
							array('name' => $lang['menu_tools_updatecaches'], 'url' => 'admincp.php?action=updatecache'),
							array('name' => $lang['menu_tools_updatecounters'], 'url' => 'admincp.php?action=counter')));
			showmenu($lang['menu_logs'],	array(array('name' => $lang['menu_logs_login'], 'url' => 'admincp.php?action=illegallog'),
							array('name' => $lang['menu_logs_rating'], 'url' => 'admincp.php?action=karmalog'),
							array('name' => $lang['menu_logs_mod'], 'url' => 'admincp.php?action=modslog'),
							array('name' => $lang['menu_logs_admincp'], 'url' => 'admincp.php?action=cplog')));
			showmenu($lang['menu_plugins'],	$pluginsarray);
			showmenu($lang['menu_logout'],	'admincp.php?action=logout');
		} else {
			showmenu($lang['menu_home'],	'admincp.php?action=home');
			if($allowedituser || $allowbanuser || $allowbanip || $allowpostannounce || $allowcensorword || $allowmassprune) {
				$menuarray = array();
				if($allowedituser || $allowbanuser) {
					$menuarray[] = array('name' => $lang['menu_members_edit'], 'url' => 'admincp.php?action=mod_members');
				}
				if($allowbanip) {
					$menuarray[] = array('name' => $lang['menu_members_ipban'], 'url' => 'admincp.php?action=ipban');
				}
				if($allowpostannounce) {
					$menuarray[] = array('name' => $lang['menu_misc_announces'], 'url' => 'admincp.php?action=announcements');
				}
				if($allowcensorword) {
					$menuarray[] = array('name' => $lang['menu_posting_censors'], 'url' => 'admincp.php?action=censor');
				}
				if($allowmassprune) {
					$menuarray[] = array('name' => $lang['menu_maint_prune'], 'url' => 'admincp.php?action=prune');
				}
				showmenu($lang['menu_moderation'], $menuarray);
				unset($menuarray);
			}
			if($allowviewlog) {
				showmenu($lang['menu_logs'],	array(array('name' => $lang['menu_logs_rating'], 'url' => 'admincp.php?action=karmalog'),
								array('name' => $lang['menu_logs_mod'], 'url' => 'admincp.php?action=modslog')));
			}
			showmenu($lang['menu_logout'],	'admincp.php?action=logout');
		}

?>
</table></td></tr></table></td></tr></table>

</body>
</html>
