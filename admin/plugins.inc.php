<?php

/*
	[DISCUZ!] admin/plugins.inc.php - add, edit, export/import plugins, etc
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005/10/1 03:53
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

if($action == 'pluginsconfig' && $export) {

	$query = $db->query("SELECT * FROM {$tablepre}plugins WHERE pluginid='$export'");
	if(!$plugin = $db->fetch_array($query)) {
		cpheader();
		cpmsg('undefined_action');
	}

	unset($plugin['pluginid']);

	$pluginarray = array();
	$pluginarray['plugin'] = $plugin;
	$pluginarray['version'] = strip_tags($version);

	$time = gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600);

	$query = $db->query("SELECT * FROM {$tablepre}pluginvars WHERE pluginid='$export'");
	while($var = $db->fetch_array($query)) {
		unset($var['pluginvarid'], $var['pluginid']);
		$pluginarray['vars'][] = $var ;
	}

	$plugin_export = "# Discuz! Plugin Dump\n".
		"# Version: Discuz! $version\n".
		"# Time: $time  \n".
		"# From: $bbname ($boardurl) \n".
		"#\n".
		"# Discuz! Community: http://www.Discuz.net\n".
		"# Please visit our website for latest news about Discuz!\n".
		"# --------------------------------------------------------\n\n\n".
		wordwrap(base64_encode(serialize($pluginarray)), 60, "\n", 1);

	ob_end_clean();
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	header('Content-Encoding: none');
	header('Content-Length: '.strlen($plugin_export));
	header('Content-Disposition: attachment; filename=discuz_plugin_'.$plugin['identifier'].'.txt');
	header('Content-Type: '.(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? 'application/octetstream' : 'application/octet-stream'));
	echo $plugin_export;
	dexit();

}

cpheader();

if($action == 'plugins') {

	if(!$edit && !$identifier) {

		$plugins = '';
		$query = $db->query("SELECT p.*, pv.pluginvarid FROM {$tablepre}plugins p
			LEFT JOIN {$tablepre}pluginvars pv USING(pluginid)
			GROUP BY p.pluginid
			ORDER BY p.available DESC, p.pluginid");

		while($plugin = $db->fetch_array($query)) {
			if(!$plugin['adminid'] || $plugin['adminid'] >= $adminid) {
				$plugin['disabled'] = '';
				$plugin['edit'] = $plugin['pluginvarid'] ? "<a href=\"admincp.php?action=plugins&edit=$plugin[pluginid]\">[$lang[plugins_settings]]</a> " : '';
				if(is_array($plugin['modules'] = unserialize($plugin['modules']))) {
					foreach($plugin['modules'] as $module) {
						if($module['type'] == 3 && (!$module['adminid'] || $module['adminid'] >= $adminid)){
							$plugin['edit'] .= "<a href=\"admincp.php?action=plugins&identifier=$plugin[identifier]&mod=$module[name]\">[$lang[plugins_settings_module]: $module[menu]]</a> ";
						}
					}
				}
			} else {
				$plugin['disabled'] = 'disabled';
				$plugin['edit'] = "[$lang[detail]]";
			}
			$plugins .= "<table cellspacing=\"".INNERBORDERWIDTH."\" cellpadding=\"".TABLESPACE."\" width=\"80%\" align=\"center\" class=\"tableborder\" $plugin[disabled]>\n".
				"<tr class=\"header\"><td colspan=\"2\">$plugin[name]".(!$plugin['available'] ? ' ('.$lang['plugins_unavailable'].')' : '')."</td></tr>\n".
				"<tr><td width=\"20%\" class=\"altbg1\">$lang[description]:</td><td class=\"altbg2\">$plugin[description]</td></tr>\n".
				"<tr><td width=\"20%\" class=\"altbg1\">$lang[copyright]:</td><td class=\"altbg2\">$plugin[copyright]</td></tr>\n".
				"<tr><td width=\"20%\" class=\"altbg1\">$lang[edit]:</td><td class=\"altbg2\">$plugin[edit]</td></tr>\n".
				"</table><br>";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="80%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['plugins_tips']?>
</td></tr></table><br><br>
<?=$plugins?>
<?
	} else {

		$query = $db->query("SELECT * FROM {$tablepre}plugins WHERE ".($identifier ? "identifier='$identifier'" : "pluginid='$edit'"));
		if(!$plugin = $db->fetch_array($query)) {
			cpmsg('undefined_action');
		} else {
			$edit = $plugin['pluginid'];
		}

		$pluginvars = array();
		$query = $db->query("SELECT * FROM {$tablepre}pluginvars WHERE pluginid='$edit' ORDER BY displayorder");
		while($var = $db->fetch_array($query)) {
			$pluginvars[$var['variable']] = $var;
		}
	
		if(empty($mod)) {
	
			if(($plugin['adminid'] && $adminid > $plugin['adminid']) || !$pluginvars) {
				cpmsg('noaccess');
			}

			if(!submitcheck('editsubmit')) {

				echo "<form method=\"post\" action=\"admincp.php?action=plugins&edit=$pluginid&edit=$edit&formhash=".FORMHASH."\">\n";

				showtype($lang['plugins_settings'].' - '.$plugin['name'], 'top');

				foreach($pluginvars as $var) {
					$var['variable'] = 'varsnew['.$var['variable'].']';
					if($var['type'] == 'number') {
						$var['type'] = 'text';
					} elseif($var['type'] == 'select') {
						$var['type'] = "<select name=\"$var[variable]\">\n";
						foreach(explode("\n", $var['extra']) as $key => $option) {
							$var['type'] .= "<option value=\"".($option = trim($option))."\" ".($var['value'] == $option ? 'selected' : '').">$option</option>\n";
						}
						$var['type'] .= "</select>\n";
						$var['variable'] = $var['value'] = '';
					}
					$var['title'] = '</b><b>'.(isset($lang[$var['title']]) ? $lang[$var['title']] : $var['title']).'</b><br>'.
						(isset($lang[$var['description']]) ? $lang[$var['description']] : $var['description']);

					showsetting($var['title'], $var['variable'], $var['value'], $var['type']);
				}
				
				showtype('', 'bottom');
	
				echo "<br><center><input type=\"submit\" name=\"editsubmit\" value=\"$lang[submit]\"></center></form>";

			} else {

				if(is_array($varsnew)){
					foreach($varsnew as $variable => $value) {
						if(isset($pluginvars[$variable])) {
							if($pluginvars[$variable]['type'] == 'number') {
								$value = (float)$value;
							}
							$db->query("UPDATE {$tablepre}pluginvars SET value='$value' WHERE pluginid='$edit' AND variable='$variable'");
						}
					}
				}

				updatecache('plugins');
				cpmsg('plugins_settings_succeed', 'admincp.php?action=plugins');

			}
		
		} else {
	
			$modfile = '';
			if(is_array($plugin['modules'] = unserialize($plugin['modules']))) {
				foreach($plugin['modules'] as $module){
					if($module['type'] == 3 && $module['name'] == $mod && (!$module['adminid'] || $module['adminid'] >= $adminid)){
						$modfile = './plugins/'.$plugin['directory'].$module['name'].'.inc.php';
						break;	
					}
				}
			}
	
			if($modfile) {
				if(!@include DISCUZ_ROOT.$modfile) {
					cpmsg('plugins_settings_module_nonexistence');
				} else {
					dexit();
				}
			} else {
				cpmsg('undefined_action');
			}

		}		

	}

} elseif($action == 'pluginsconfig') {

	if(!submitcheck('configsubmit') && !submitcheck('importsubmit')) {

		$plugins = '';
		$query = $db->query("SELECT * FROM {$tablepre}plugins");
		while($plugin = $db->fetch_array($query)) {
			$plugins .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$plugin[pluginid]\"></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><b>$plugin[name]</b></td>\n".
				"<td bgcolor=\"".ALTBG1."\">$plugin[identifier]</td>\n".
				"<td bgcolor=\"".ALTBG2."\">$plugin[description]</td>\n".
				"<td bgcolor=\"".ALTBG1."\">$plugin[directory]</td>\n".
				"<td bgcolor=\"".ALTBG2."\"><input type=\"checkbox\" name=\"availablenew[$plugin[pluginid]]\" value=\"1\" ".(!$plugin['name'] || !$plugin['identifier'] ? 'disabled' : ($plugin['available'] ? 'checked' : ''))."></td>\n".
				"<td bgcolor=\"".ALTBG1."\"><a href=\"admincp.php?action=pluginsconfig&export=$plugin[pluginid]\">[$lang[download]]</a></td>\n".
				"<td bgcolor=\"".ALTBG2."\"><a href=\"admincp.php?action=pluginsedit&pluginid=$plugin[pluginid]\">[$lang[detail]]</a></td></tr>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['plugins_config_tips']?>
</td></tr></table><br><br>

<form method="post" action="admincp.php?action=pluginsconfig">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header" align="center">
<td width="48"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form, 'delete')"><?=$lang['del']?></td>
<td width="15%"><?=$lang['plugins_name']?></td>
<td width="10%"><?=$lang['plugins_identifier']?></td>
<td width="31%"><?=$lang['description']?></td>
<td width="15%"><?=$lang['plugins_directory']?></td>
<td width="8%"><?=$lang['available']?></td>
<td width="8%"><?=$lang['export']?></td>
<td width="8%"><?=$lang['edit']?></td></tr>
<?=$plugins?>
<tr><td colspan="7" class="singleborder">&nbsp;</td></tr>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type='text' name="newname" size="12"></td>
<td><input type='text' name="newidentifier" size="8"></td>
<td colspan="6">&nbsp;</td>
</tr></table><br>
<center><input type="submit" name="configsubmit" value="<?=$lang['submit']?>"></center></form>

<br><form method="post" action="admincp.php?action=pluginsconfig">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['plugins_import']?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>" align="center"><textarea  name="plugindata" cols="80" rows="8"></textarea><br>
<input type="checkbox" name="ignoreversion" value="1"> <?=$lang['plugins_import_ignore_version']?></td></tr>
</table><br><center><input type="submit" name="importsubmit" value="<?=$lang['submit']?>"></center></form>
<?

	} elseif(submitcheck('configsubmit')) {

		$db->query("UPDATE {$tablepre}plugins SET available='0'");
		if(is_array($availablenew)) {
			foreach($availablenew as $id => $available) {
				$db->query("UPDATE {$tablepre}plugins SET available='$available' WHERE pluginid='$id'");
			}
		}

		if(is_array($delete)) {
			$ids = $comma = '';
			foreach($delete as $id) {
				$ids .= "$comma'$id'";
				$comma  = ',';
			}

			$db->query("DELETE FROM {$tablepre}plugins WHERE pluginid IN ($ids)");
			$db->query("DELETE FROM {$tablepre}pluginvars WHERE pluginid IN ($ids)");
		}

		if(($newname = trim($newname)) || ($newidentifier = trim($newidentifier))) {
			if(!$newname) {
				cpmsg('plugins_edit_name_invalid');
			}
			$query = $db->query("SELECT pluginid FROM {$tablepre}plugins WHERE identifier='$newidentifier' LIMIT 1");
			if($db->num_rows($query) || !$newidentifier || !is_key($newidentifier)) {
				cpmsg('plugins_edit_identifier_invalid');
			}
			$db->query("INSERT INTO {$tablepre}plugins (name, identifier, available) VALUES ('".dhtmlspecialchars(trim($newname))."', '$newidentifier', '0')");
		}

		updatecache('plugins');
		updatecache('settings');
		cpmsg('plugins_edit_succeed', 'admincp.php?action=pluginsconfig');

	} elseif(submitcheck('importsubmit')) {

		$plugindata = preg_replace("/(#.*\s+)*/", '', $plugindata);
		$pluginarray = daddslashes(unserialize(base64_decode($plugindata)), 1);

		if(!is_array($pluginarray) || !is_array($pluginarray['plugin'])) {
			cpmsg('plugins_import_data_invalid');
		} elseif(empty($ignoreversion) && strip_tags($pluginarray['version']) != strip_tags($version)) {
			cpmsg('plugins_import_version_invalid');
		}

		$query = $db->query("SELECT pluginid FROM {$tablepre}plugins WHERE identifier='{$pluginarray['plugin']['identifier']}' LIMIT 1");
		if($db->num_rows($query)) {
			cpmsg('plugins_import_identifier_duplicated');
		}

		$sql1 = $sql2 = $comma = '';
		foreach($pluginarray['plugin'] as $key => $val) {
			$sql1 .= $comma.$key;
			$sql2 .= $comma.'\''.$val.'\'';
			$comma = ',';
		}
		$db->query("INSERT INTO {$tablepre}plugins ($sql1) VALUES ($sql2)");
		$pluginid = $db->insert_id();

		if(is_array($pluginarray['vars'])) {
			foreach($pluginarray['vars'] as $var) {
				$sql1 = 'pluginid';
				$sql2 = '\''.$pluginid.'\'';
				foreach($var as $key => $val) {
					$sql1 .= ','.$key;
					$sql2 .= ',\''.$val.'\'';
				}
				$db->query("INSERT INTO {$tablepre}pluginvars ($sql1) VALUES ($sql2)");
			}
		}

		updatecache('plugins');
		updatecache('settings');
		cpmsg('plugins_import_succeed', 'admincp.php?action=pluginsconfig');

	}

} elseif($action == 'pluginsedit' && $pluginid) {

	$query = $db->query("SELECT * FROM {$tablepre}plugins WHERE pluginid='$pluginid'");
	if(!$plugin = $db->fetch_array($query)) {
		cpmsg('undefined_action');
	}

	$plugin['modules'] = unserialize($plugin['modules']);

	if(!submitcheck('editsubmit')) {

		$modules = '';
		if(is_array($plugin['modules'])) {
			foreach($plugin['modules'] as $moduleid => $module) {
				$module['type']	= $module['type'] ? $lang["plugins_edit_module_type_".$module['type']] : '';
				$adminidselect = array($module['adminid'] => 'selected');
				$includecheck = empty($val['include']) ? $lang['no'] : $lang['yes'];
		
				$modules .= "<tr class=\"altbg1\" align=\"center\"><td class=\"altbg1\"><input type=\"checkbox\" name=\"delete[$moduleid]\"></td>\n".
					"<td class=\"altbg2\">$module[name]</td>\n".
					"<td class=\"altbg1\">$module[menu]</td>\n".
					"<td class=\"altbg2\">$module[url]</td>\n".
					"<td class=\"altbg1\">$module[type]</td>\n".
					"<td class=\"altbg2\"><select name=\"adminidnew[$moduleid]\">\n".
					"<option value=\"0\" $adminidselect[0]>$lang[usergroups_system_0]</option>\n".
					"<option value=\"1\" $adminidselect[1]>$lang[usergroups_system_1]</option>\n".
					"<option value=\"2\" $adminidselect[2]>$lang[usergroups_system_2]</option>\n".
					"<option value=\"3\" $adminidselect[3]>$lang[usergroups_system_3]</option>\n".
					"</select></td></tr>\n";
			}
		}

		$vars = '';
		$query = $db->query("SELECT * FROM {$tablepre}pluginvars WHERE pluginid='$plugin[pluginid]' ORDER BY displayorder");
		while($var = $db->fetch_array($query)) {
			$var['type'] = $lang['plugins_edit_vars_type_'. $var['type']];
			$var['title'] .= isset($lang[$var['title']]) ? '<br>'.$lang[$var['title']] : '';
			$vars .= "<tr class=\"altbg1\" align=\"center\"><td class=\"altbg1\"><input type=\"checkbox\" name=\"delete[$var[pluginvarid]]\"></td>\n".
				"<td class=\"altbg2\">$var[title]</td>\n".
				"<td class=\"altbg1\">$var[variable]</td>\n".
				"<td class=\"altbg2\">$var[type]</td>\n".
				"<td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"displayordernew[$var[pluginvarid]]\" value=\"$var[displayorder]\"></td>\n".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=pluginvars&pluginid=$plugin[pluginid]&pluginvarid=$var[pluginvarid]\">[$lang[detail]]</a></td></tr>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['plugins_edit_tips']?>
</td></tr></table><br><br>

<a name="common"></a>
<form method="post" action="admincp.php?action=pluginsedit&type=common&pluginid=<?=$pluginid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		$adminidselect = array($plugin['adminid'] => 'selected');

		showtype($lang['plugins_edit'].' - '.$plugin['name'], 'top');
		showsetting('plugins_edit_name', 'namenew', $plugin['name'], 'text');
		if(!$plugin['copyright']) {
			showsetting('plugins_edit_copyright', 'copyrightnew', $plugin['copyright'], 'text');
		}
		showsetting('plugins_edit_identifier', 'identifiernew', $plugin['identifier'], 'text');
		showsetting('plugins_edit_adminid', '', '', '<select name="adminidnew"><option value="1" '.$adminidselect[1].'>'.$lang['usergroups_system_1'].'</option><option value="2" '.$adminidselect[2].'>'.$lang['usergroups_system_2'].'</option><option value="3" '.$adminidselect[3].'>'.$lang['usergroups_system_3'].'</option></select>');

		showsetting('plugins_edit_directory', 'directorynew', $plugin['directory'], 'text');
		showsetting('plugins_edit_datatables', 'datatablesnew', $plugin['datatables'], 'text');
		showsetting('plugins_edit_description', 'descriptionnew', $plugin['description'], 'textarea');
		showtype('', 'bottom');

?>
<br><center><input type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</form><br>

<a name="modules"></a>
<form method="post" action="admincp.php?action=pluginsedit&type=modules&pluginid=<?=$pluginid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['plugins_edit_module']?></td></tr>
<tr class="category" align="center"><td width="45"><input type="checkbox" name="chkall" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td><?=$lang['plugins_edit_module_name']?></td><td><?=$lang['plugins_edit_module_menu']?></td><td><?=$lang['plugins_edit_module_menu_url']?></td>
<td><?=$lang['plugins_edit_module_type']?></td><td><?=$lang['plugins_edit_module_adminid']?></td></tr>
<?=$modules?>
<tr><td colspan="6" class="singleborder">&nbsp;</td></tr>
<tr class="altbg1" align="center"><td><?=$lang['add_new']?></td><td><input type="text" size="15" name="newname"></td>
<td><input type="text" size="15" name="newmenu"></td>
<td><input type="text" size="15" name="newurl"></td>
<td><select name="newtype">
<option value="1"><?=$lang['plugins_edit_module_type_1']?></option>
<option value="2"><?=$lang['plugins_edit_module_type_2']?></option>
<option value="3"><?=$lang['plugins_edit_module_type_3']?></option>
<option value="4"><?=$lang['plugins_edit_module_type_4']?></option>
</select></td><td class="altbg2"><select name="newadminid">
<option value="0"><?=$lang['usergroups_system_0']?></option>
<option value="1" selected><?=$lang['usergroups_system_1']?></option>
<option value="2"><?=$lang['usergroups_system_2']?></option>
<option value="3"><?=$lang['usergroups_system_3']?></option>
</select></td></tr>
</table><br><center><input type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</form><br>

<a name="vars"></a>
<form method="post" action="admincp.php?action=pluginsedit&type=vars&pluginid=<?=$pluginid?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td colspan="6"><?=$lang['plugins_edit_vars']?></td></tr>
<tr class="category" align="center"><td width="45"><input type="checkbox" name="chkall" class="category" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['plugins_vars_title']?></td><td><?=$lang['plugins_vars_variable']?></td><td><?=$lang['plugins_vars_type']?></td><td><?=$lang['display_order']?></td><td><?=$lang['edit']?></td></tr>
<?=$vars?>
<tr><td colspan="6" class="singleborder">&nbsp;</td></tr>
<tr align="center" class="altbg1"><td><?=$lang['add_new']?></td>
<td><input type="text" size="15" name="newtitle"></td>
<td><input type="text" size="15" name="newvariable"></td>
<td><select name="newtype">
<option value="number"><?=$lang['plugins_edit_vars_type_number']?></option>
<option value="text" selected><?=$lang['plugins_edit_vars_type_text']?></option>
<option value="textarea"><?=$lang['plugins_edit_vars_type_textarea']?></option>
<option value="radio"><?=$lang['plugins_edit_vars_type_radio']?></option>
<option value="select"><?=$lang['plugins_edit_vars_type_select']?></option>
<option value="color"><?=$lang['plugins_edit_vars_type_color']?></option>
</seletc></td><td><input type="text" size="2" name="newdisplayorder" value="0"></td>
<td>&nbsp;</td></tr>
</table><br><center><input type="submit" name="editsubmit" value="<?=$lang['submit']?>"></center>
</form><br>
<?

	} else {

		if($type == 'common') {

			$namenew	= dhtmlspecialchars(trim($namenew));
			$directorynew	= dhtmlspecialchars(safedir($directorynew));
			$identifiernew	= trim($identifiernew);
			$datatablesnew	= dhtmlspecialchars(trim($datatablesnew));
			$descriptionnew	= dhtmlspecialchars($descriptionnew);
			$copyrightnew	= $plugin['copyright'] ? addslashes($plugin['copyright']) : dhtmlspecialchars($copyrightnew);
			$adminidnew	= ($adminidnew > 0 && $adminidnew <= 3) ? $adminidnew : 1;

			if(!$namenew) {
				cpmsg('plugins_edit_name_invalid');
			} elseif($identifiernew != $plugin['identifier']) {
				$query = $db->query("SELECT pluginid FROM {$tablepre}plugins WHERE identifier='$identifiernew' LIMIT 1");
				if($db->num_rows($query) || !is_key($identifiernew)) {
					cpmsg('plugins_edit_identifier_invalid');
				}
			}

			$db->query("UPDATE {$tablepre}plugins SET adminid='$adminidnew', name='$namenew', identifier='$identifiernew', description='$descriptionnew', datatables='$datatablesnew', directory='$directorynew', copyright='$copyrightnew' WHERE pluginid='$pluginid'");

		} elseif($type == 'modules') {

			$modulesnew = array();
			$newname = trim($newname);
			if(is_array($plugin['modules'])) {
				foreach($plugin['modules'] as $moduleid => $module) {
					if(!isset($delete[$moduleid])) {
						$module['adminid'] = ($adminidnew[$moduleid] >= 0 && $adminidnew[$moduleid] <= 3) ? $adminidnew[$moduleid] : $module['adminid'];
						$modulesnew[] = $module;
						if($newname == $module['name']) {
							cpmsg('plugins_edit_module_duplicated');
						}
					}
				}
			}

			$newmodule = array();
			if(!empty($newname)) {
				if(!is_key($newname)) {
					cpmsg('plugins_edit_module_name_invalid');
				}

				$newadminid = intval($newadminid);
				$newmenu = trim($newmenu);
				$newurl = trim($newurl);

				switch($newtype) {
					case 1:
						if(empty($newurl)) {
							cpmsg('plugins_edit_module_url_invalid');
						}
						$newmodule = array('name' => $newname, 'menu' => $newmenu, 'url' => $newurl);
						break;
					case 2:
					case 3:
						if(empty($newmenu)) {
							cpmsg('plugins_edit_module_menu_invalid');
						}
						$newmodule = array('name' => $newname, 'menu' => $newmenu);
						break;
					case 4:
						$newmodule = array('name' => $newname);
						break;
					default:
						cpmsg('undefined_action');
				}
				
				$newmodule['type'] = $newtype;
				$newmodule['adminid'] = $newadminid >= 0 && $newadminid <= 3 ? $newadminid : 1 ;
			}

			if($newmodule) {
				$modulesnew[] = $newmodule;
			}
			
			$db->query("UPDATE {$tablepre}plugins SET modules='".addslashes(serialize($modulesnew))."' WHERE pluginid='$pluginid'");

		} elseif($type == 'vars') {

			if(is_array($delete)) {
				$ids = $comma = '';
				foreach($delete as $id => $val) {
					$ids .= "$comma'$id'";
					$comma = ',';
				}
				$db->query("DELETE from {$tablepre}pluginvars WHERE pluginid='$pluginid' AND pluginvarid IN ($ids)");
			};

			if(is_array($displayordernew)) {
				foreach($displayordernew as $id => $displayorder) {
					$db->query("UPDATE {$tablepre}pluginvars SET displayorder='$displayorder' WHERE pluginid='$pluginid' AND pluginvarid='$id'");
				}
			}

			$newtitle = dhtmlspecialchars(trim($newtitle));
			$newvariable = trim($newvariable);
			if($newtitle && $newvariable) {
				$query = $db->query("SELECT pluginvarid FROM {$tablepre}pluginvars WHERE pluginid='$pluginid' AND variable='$newvariable' LIMIT 1");
				if($db->num_rows($query) || strlen($newvariable) > 40 || !is_key($newvariable)) {
					cpmsg('plugins_edit_var_invalid');
				}
				
				$db->query("INSERT INTO {$tablepre}pluginvars (pluginid, displayorder, title, variable, type)
					VALUES ('$pluginid', '$newdisplayorder', '$newtitle', '$newvariable', '$newtype')");
			}

		}

		updatecache('plugins');
		updatecache('settings');
		cpmsg('plugins_edit_succeed', "admincp.php?action=pluginsedit&pluginid=$pluginid#$type");

	}

} elseif($action == 'pluginvars' && $pluginid && $pluginvarid) {

	$query = $db->query("SELECT * FROM {$tablepre}plugins p, {$tablepre}pluginvars pv WHERE p.pluginid='$pluginid' AND pv.pluginid=p.pluginid AND pv.pluginvarid='$pluginvarid'");
	if(!$pluginvar = $db->fetch_array($query)) {
		cpmsg('undefined_action');
	}

	if(!submitcheck('varsubmit')) {

		$typeselect = '<select name="typenew">';
		foreach(array('number', 'text', 'radio', 'textarea', 'select', 'color') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($pluginvar['type'] == $type ? 'selected' : '').'>'.$lang['plugins_edit_vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		echo "<form method=\"post\" action=\"admincp.php?action=pluginvars&pluginid=$pluginid&pluginvarid=$pluginvarid&formhash=".FORMHASH."\">\n";

		showtype($lang['plugins_edit_vars'].' - '.$pluginvar['title'], 'top');
		showsetting('plugins_edit_vars_title', 'titlenew', $pluginvar['title'], 'text');
		showsetting('plugins_edit_vars_description', 'descriptionnew', $pluginvar['description'], 'textarea');
		showsetting('plugins_edit_vars_type', '', '', $typeselect);
		showsetting('plugins_edit_vars_variable', 'variablenew', $pluginvar['variable'], 'text');
		showsetting('plugins_edit_vars_extra', 'extranew',  $pluginvar['extra'], 'textarea');
		showtype('', 'bottom');

		echo "<br><center><input type=\"submit\" name=\"varsubmit\" value=\"$lang[submit]\"></center></form>\n<br>";

	} else {

		$titlenew	= cutstr(dhtmlspecialchars(trim($titlenew)), 25);
		$descriptionnew	= cutstr(dhtmlspecialchars(trim($descriptionnew)), 255);
		$variablenew	= trim($variablenew);
		$extranew	= dhtmlspecialchars(trim($extranew));

		if(!$titlenew) {
			cpmsg('plugins_edit_var_title_invalid');
		} elseif($variablenew != $pluginvar['variable']) {
			$query = $db->query("SELECT pluginvarid FROM {$tablepre}pluginvars WHERE variable='$variablenew'");
			if($db->num_rows($query) || !$variablenew || strlen($variablenew) > 40 || !is_key($variablenew)) {
				cpmsg('plugins_edit_var_invalid');
			}
		}
		
		$db->query("UPDATE {$tablepre}pluginvars SET title='$titlenew', description='$descriptionnew', type='$typenew', variable='$variablenew', extra='$extranew' WHERE pluginid='$pluginid' AND pluginvarid='$pluginvarid'");

		updatecache('plugins');
		cpmsg('plugins_edit_var_succeed', "admincp.php?action=pluginsedit&pluginid=$pluginid");
	}

}

//debug!!! REPLACE THESE TWO FUNCTIONS!
function is_key( $str ){
	return ereg("^[a-zA-Z]+[a-zA-Z0-9_]+$", $str);
}

function safedir($dir){
	$dir = str_replace( '.', '', $dir );
	$dir = str_replace( ' ', '', $dir );

	if (empty($dir)) return '';
	$dir .=  (substr( $dir, -1 ) !='/') ? '/' :'';
	return (substr( $dir, 0, 1 )=='/') ? safedir(substr( $dir, 1 )) : $dir;
}

?>