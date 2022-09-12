<?php

/*
	[DISCUZ!] admin/menu.inc.php - menu of administrators' control panel
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-3-30 10:41
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$charset?>">
<? include template('css'); ?>
</head>

<body leftmargin="3" topmargin="3">

<br><table cellspacing="0" cellpadding="0" border="0" width="100%" align="center" style="table-layout: fixed">
<tr><td bgcolor="<?=BORDERCOLOR?>">
<table width="100%" border="0" cellspacing="1" cellpadding="0">
<tr><td bgcolor="#FFFFFF">
<table width="100%" border="0" cellspacing="3" cellpadding="<?=TABLESPACE?>" class="smalltxt">
<tr><td bgcolor="<?=ALTBG1?>" align="center"><b><a href="admincp.php?action=menu&collapse=0">[+]</a> &nbsp; <a href="admincp.php?action=menu&collapse=1_2_3_4_5_6_7_8_9_10_11_12_13_14_15_16_17_18_19_20">[-]</a></b></td></tr>
<?

		if(preg_match("/(^|_)$change($|_)/", $collapse)) {
			$collapsedlist = array();
			foreach(explode('_', $collapse) as $collapsed) {
				if($collapsed && $collapsed != $change) {
					$collapsedlist[] = $collapsed;
				}
			}
			$collapse = $collapsedlist ? implode('_', $collapsedlist) : '0';
		} else {
			$collapse .= isset($collapse) && substr($collapse, -1) != '_' ? '_'.$change : $change;
		}

		if($collapse || $collapse == '0') {
			dsetcookie('cpcollapsed', $collapse, 2592000);
		} else {
			$collapse = $_DCOOKIE['cpcollapsed'];
		}

		$menucount = 0;

		if($adminid == 1) {
			showmenu($lang['menu_home'],	'admincp.php?action=home');
			showmenu($lang['menu_settings'],array(array('name' => $lang['settings_general'], 'url' => 'admincp.php?action=settings#'.$lang['settings_general']),
							array('name' => $lang['settings_access'], 'url' => 'admincp.php?action=settings#'.$lang['settings_access']),
							array('name' => $lang['settings_styles'], 'url' => 'admincp.php?action=settings#'.$lang['settings_styles']),
							array('name' => $lang['settings_seo'], 'url' => 'admincp.php?action=settings#'.$lang['settings_seo']),
							array('name' => $lang['settings_functions'], 'url' => 'admincp.php?action=settings#'.$lang['settings_functions']),
							array('name' => $lang['settings_credits'], 'url' => 'admincp.php?action=settings#'.$lang['settings_credits']),
							array('name' => $lang['settings_security'], 'url' => 'admincp.php?action=settings#'.$lang['settings_security']),
							array('name' => $lang['settings_periods'], 'url' => 'admincp.php?action=settings#'.$lang['settings_periods']),
							array('name' => $lang['settings_permissions'], 'url' => 'admincp.php?action=settings#'.$lang['settings_permissions']),
							array('name' => $lang['settings_attachments'], 'url' => 'admincp.php?action=settings#'.$lang['settings_attachments']),
							array('name' => $lang['settings_javascript'], 'url' => 'admincp.php?action=settings#'.$lang['settings_javascript']),
							array('name' => $lang['settings_wap'], 'url' => 'admincp.php?action=settings#'.$lang['settings_wap']),
							array('name' => $lang['settings_misc'], 'url' => 'admincp.php?action=settings#'.$lang['settings_misc'])));
			showmenu($lang['menu_passport'],array(array('name' => $lang['menu_passport_settings'], 'url' => 'admincp.php?action=passport'),
							array('name' => $lang['menu_passport_siteengine'], 'url' => 'admincp.php?action=siteengine'),
							array('name' => $lang['menu_passport_shopex'], 'url' => 'admincp.php?action=shopex')));
			showmenu($lang['menu_forums'],	array(array('name' => $lang['menu_forums_add'], 'url' => 'admincp.php?action=forumadd'),
							array('name' => $lang['menu_forums_edit'], 'url' => 'admincp.php?action=forumsedit'),
							array('name' => $lang['menu_forums_merge'], 'url' => 'admincp.php?action=forumsmerge'),
							array('name' => $lang['menu_forums_threadtypes'], 'url' => 'admincp.php?action=threadtypes')));
			showmenu($lang['menu_groups'],	array(array('name' => $lang['menu_admingroups'], 'url' => 'admincp.php?action=admingroups'),
							array('name' => $lang['menu_usergroups'], 'url' => 'admincp.php?action=usergroups'),
							array('name' => $lang['menu_ranks'], 'url' => 'admincp.php?action=ranks')));
			showmenu($lang['menu_members'], array(array('name' => $lang['menu_members_add'], 'url' => 'admincp.php?action=memberadd'),
							array('name' => $lang['menu_members_edit'], 'url' => 'admincp.php?action=members'),
							array('name' => $lang['menu_members_merge'], 'url' => 'admincp.php?action=membersmerge'),
							array('name' => $lang['menu_members_profile_fields'], 'url' => 'admincp.php?action=profilefields'),
							array('name' => $lang['menu_members_ipban'], 'url' => 'admincp.php?action=ipban')));
			showmenu($lang['menu_moderate'],array(array('name' => $lang['menu_moderate_modmembers'], 'url' => 'admincp.php?action=modmembers'),
							array('name' => $lang['menu_moderate_modthreads'], 'url' => 'admincp.php?action=modthreads'),
							array('name' => $lang['menu_moderate_modreplies'], 'url' => 'admincp.php?action=modreplies'),
							array('name' => $lang['menu_moderate_recyclebin'], 'url' => 'admincp.php?action=recyclebin')));
			showmenu($lang['menu_posting'],	array(array('name' => $lang['menu_posting_discuzcodes'], 'url' => 'admincp.php?action=discuzcodes'),
							array('name' => $lang['menu_posting_censors'], 'url' => 'admincp.php?action=censor'),
							array('name' => $lang['menu_posting_smilies'], 'url' => 'admincp.php?action=smilies'),
							array('name' => $lang['menu_posting_attachtypes'], 'url' => 'admincp.php?action=attachtypes')));
			showmenu($lang['menu_ecommerce'],array(array('name' => $lang['menu_ecommerce_alipay'], 'url' => 'admincp.php?action=alipay'),
							array('name' => $lang['menu_ecommerce_orders'], 'url' => 'admincp.php?action=orders')));
			showmenu($lang['menu_styles'],	array(array('name' => $lang['menu_styles'], 'url' => 'admincp.php?action=styles'),
							array('name' => $lang['menu_styles_templates'], 'url' => 'admincp.php?action=templates')));
			showmenu($lang['menu_avatarshow'],array(array('name' => $lang['menu_avatarshow_config'], 'url' => 'admincp.php?action=avatarshow_config'),
							array('name' => $lang['menu_avatarshow_register'], 'url' => 'admincp.php?action=avatarshow_register')));
			showmenu($lang['menu_misc'], 	array(array('name' => $lang['menu_misc_announces'], 'url' => 'admincp.php?action=announcements'),
							array('name' => $lang['menu_misc_medals'], 'url' => 'admincp.php?action=medals'),
							array('name' => $lang['menu_misc_onlinelist'], 'url' => 'admincp.php?action=onlinelist'),
							array('name' => $lang['menu_misc_advertisements'], 'url' => 'admincp.php?action=adv'),
							array('name' => $lang['menu_misc_links'], 'url' => 'admincp.php?action=forumlinks')));
			showmenu($lang['menu_database'],array(array('name' => $lang['menu_database_export'], 'url' => 'admincp.php?action=export'),
							array('name' => $lang['menu_database_import'], 'url' => 'admincp.php?action=import'),
							array('name' => $lang['menu_database_query'], 'url' => 'admincp.php?action=runquery'),
							array('name' => $lang['menu_database_optimize'], 'url' => 'admincp.php?action=optimize')));
			showmenu($lang['menu_maint'],	array(array('name' => $lang['menu_maint_attaches'], 'url' => 'admincp.php?action=attachments'),
							array('name' => $lang['menu_maint_threads'], 'url' => 'admincp.php?action=threads'),
							array('name' => $lang['menu_maint_prune'], 'url' => 'admincp.php?action=prune'),
							array('name' => $lang['menu_maint_pmprune'], 'url' => 'admincp.php?action=pmprune')));
			showmenu($lang['menu_tools'],	array(array('name' => $lang['menu_tools_news'], 'url' => 'admincp.php?action=newsletter'),
							array('name' => $lang['menu_tools_updatecaches'], 'url' => 'admincp.php?action=updatecache'),
							array('name' => $lang['menu_tools_updatecounters'], 'url' => 'admincp.php?action=counter'),
							array('name' => $lang['menu_tools_javascript'], 'url' => 'admincp.php?action=jswizard'),
							array('name' => $lang['menu_tools_fileperms'], 'url' => 'admincp.php?action=fileperms')));
			showmenu($lang['menu_plugins'],	array(array('name' => $lang['menu_plugins_edit'], 'url' => 'admincp.php?action=plugins'),
							array('name' => $lang['menu_plugins_config'], 'url' => 'admincp.php?action=pluginsconfig')));
			showmenu($lang['menu_logs'],	array(array('name' => $lang['menu_logs_login'], 'url' => 'admincp.php?action=illegallog'),
							array('name' => $lang['menu_logs_rating'], 'url' => 'admincp.php?action=ratelog'),
							array('name' => $lang['menu_logs_credit'], 'url' => 'admincp.php?action=creditslog'),
							array('name' => $lang['menu_logs_mod'], 'url' => 'admincp.php?action=modslog'),
							array('name' => $lang['menu_logs_medal'], 'url' => 'admincp.php?action=medalslog'),
							array('name' => $lang['menu_logs_ban'], 'url' => 'admincp.php?action=banlog'),
							array('name' => $lang['menu_logs_admincp'], 'url' => 'admincp.php?action=cplog'),
							array('name' => $lang['menu_logs_error'], 'url' => 'admincp.php?action=errorlog')));
		} else {
			showmenu($lang['menu_home'],	'admincp.php?action=home');
			if($allowedituser || $allowbanuser || $allowbanip || $allowpostannounce || $allowcensorword || $allowmassprune) {
				$menuarray = array();
				if($allowedituser || $allowbanuser) {
					$menuarray[] = array('name' => $lang['menu_members_edit'], 'url' => 'admincp.php?action=editmember');
				}
				$menuarray[] = array('name' => $lang['menu_forums_rules'], 'url' => 'admincp.php?action=forumrules');
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
			if($allowmoduser || $allowmodpost) {
				$menuarray = array();
				if($allowmoduser) {
					$menuarray[] = array('name' => $lang['menu_moderate_modmembers'], 'url' => 'admincp.php?action=modmembers');
				}
				if($allowmodpost) {
					$menuarray[] = array('name' => $lang['menu_moderate_modthreads'], 'url' => 'admincp.php?action=modthreads');
					$menuarray[] = array('name' => $lang['menu_moderate_modreplies'], 'url' => 'admincp.php?action=modreplies');
				}
				showmenu($lang['menu_moderate'], $menuarray);
				unset($menuarray);
			}

			showmenu($lang['menu_plugins'],	array(array('name' => $lang['menu_plugins'], 'url' => 'admincp.php?action=plugins')));

			if($allowviewlog) {
				showmenu($lang['menu_logs'],	array(array('name' => $lang['menu_logs_rating'], 'url' => 'admincp.php?action=ratelog'),
								array('name' => $lang['menu_logs_mod'], 'url' => 'admincp.php?action=modslog'),
								array('name' => $lang['menu_logs_ban'], 'url' => 'admincp.php?action=banlog')));
			}

		}
		showmenu($lang['menu_logout'],	'admincp.php?action=logout');

?>
</table></td></tr></table></td></tr></table>

</body>
</html>
