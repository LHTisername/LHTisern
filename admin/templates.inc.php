<?php

/*
	[DISCUZ!] admin/templates.inc.php - add or edit templates
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2004/12/12 12:08
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

cpheader();

if($action == 'templates') {

	if(!$edit) {

		if(!submitcheck('tplsubmit')) {

			$templates = '';
			$query = $db->query("SELECT * FROM {$tablepre}templates");
			while($tpl = $db->fetch_array($query)) {
				$templates .= "<tr align=\"center\"><td bgcolor=\"".ALTBG1."\"><input type=\"checkbox\" name=\"delete[]\" value=\"$tpl[templateid]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\"><input type=\"text\" size=\"8\" name=\"namenew[$tpl[templateid]]\" value=\"$tpl[name]\"></td>\n".
					"<td bgcolor=\"".ALTBG1."\"><input type=\"text\" size=\"20\" name=\"directorynew[$tpl[templateid]]\" value=\"$tpl[directory]\"></td>\n".
					"<td bgcolor=\"".ALTBG2."\">$tpl[copyright]</td>\n".
					"<td bgcolor=\"".ALTBG1."\"><a href=\"admincp.php?action=templates&edit=$tpl[templateid]\">[$lang[detail]]</a></td></tr>\n";
			}

?>
<form method="post" action="admincp.php?action=templates">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="95%" align="center" class="tableborder">
<tr class="header" align="center">
<td width="48"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td><?=$lang['templates_name']?></td><td><?=$lang['templates_directory']?></td><td><?=$lang['copyright']?></td><td><?=$lang['edit']?></td></tr>
<?=$templates?>
<tr><td colspan="76" class="singleborder">&nbsp;</td></tr>
<tr align="center"><td bgcolor="<?=ALTBG1?>"><?=$lang['add_new']?></td>
<td bgcolor="<?=ALTBG2?>"><input type="text" size="8" name="newname"></td>
<td bgcolor="<?=ALTBG1?>"><input type="text" size="20" name="newdirectory"></td>
<td bgcolor="<?=ALTBG2?>"><input type="text" size="25" name="newcopyright"></td>
<td bgcolor="<?=ALTBG1?>">&nbsp;</td>
</tr></table><br>
<center><input type="submit" name="tplsubmit" value="<?=$lang['submit']?>"></center></form>
<?

		} else {

			if($newname) {
				if(!$newdirectory) {
					cpmsg('templates_new_directory_invalid');
				} elseif(!tpldir_valid($newdirectory)) {
					$directory = $newdirectory;
					cpmsg('templates_directory_invalid');
				}
				$db->query("INSERT INTO {$tablepre}templates (name, directory, copyright)
					VALUES ('$newname', '$newdirectory', '$newcopyright')", 'UNBUFFERED');
			}

			foreach($directorynew as $id => $directory) {
				if(!$delete || ($delete && !in_array($id, $delete))) {
					if(!tpldir_valid($directory)) {
						cpmsg('templates_directory_invalid');
					} elseif($id == 1 && $directory != './templates/default') {
						cpmsg('templates_default_directory_invalid');
					}
					$db->query("UPDATE {$tablepre}templates SET name='$namenew[$id]', directory='$directorynew[$id]' WHERE templateid='$id'", 'UNBUFFERED');
				}
			}

			if(is_array($delete)) {
				if(in_array('1', $delete)) {
					cpmsg('templates_delete_invalid');
				}
				$ids = $comma = '';
				foreach($delete as $id) {
					$ids .= "$comma'$id'";
					$comma = ', ';
				}
				$db->query("DELETE FROM {$tablepre}templates WHERE templateid IN ($ids) AND templateid<>'1'", 'UNBUFFERED');
				$db->query("UPDATE {$tablepre}styles SET templateid='1' WHERE templateid IN ($ids)", 'UNBUFFERED');
			}

			updatecache('styles');
			cpmsg('templates_update_succeed', 'admincp.php?action=templates');

		}

	} else {


		$query = $db->query("SELECT * FROM {$tablepre}templates WHERE templateid='$edit'");
		if(!$template = $db->fetch_array($query)) {
			cpmsg('undefined_action');
		} elseif(!tpldir_valid($template['directory'])) {
			$directory = $template['directory'];
			cpmsg('templates_directory_invalid');
		}

		$warning = $template['templateid'] == 1 ?
				$lang['templates_edit_default_comment'] :
				$lang['templates_edit_nondefault_comment'];
		if($keyword) {
			$keywordadd = " - $lang[templates_keyword] <i>".htmlspecialchars(stripslashes($keyword))."</i> - <a href=\"admincp.php?action=templates&edit=$edit\" style=\"color: ".HEADERTEXT."\">[ $lang[templates_view_all] ]</a>";
			$keywordenc = rawurlencode($keyword);
		}

		$tpldir = dir(DISCUZ_ROOT.'./'.$template['directory']);
		$tplarray = $langarray = array();
		while($entry = $tpldir->read()) {
			$extension = strtolower(fileext($entry));
			if($extension == 'htm') {
				$tplname = substr($entry, 0, -4);
				$pos = strpos($tplname, '_');
				if($keyword) {
					if(!stristr(implode("\n", file(DISCUZ_ROOT."./$template[directory]/$entry")), $keyword)) {
						continue;
					}
				}
				if(!$pos) {
					$tplarray[$tplname][] = $tplname;
				} else {
					$tplarray[substr($tplname, 0, $pos)][] = $tplname;
				}
			} elseif($extension == 'php') {
				$langarray[] = substr($entry, 0, -9);
			}
		}
		$tpldir->close();

		ksort($tplarray);
		ksort($langarray);
		$templates = $languages = '';

		foreach($tplarray as $tpl => $subtpls) {
			$templates .= "<ul><li><b>$tpl</b><ul>\n";
			foreach($subtpls as $subtpl) {
				$filename = "$subtpl.htm";
				$templates .= "<li>$subtpl &nbsp; <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$filename&keyword=$keywordenc\">[$lang[edit]]</a> ".
					"<a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$filename&delete=yes\">[$lang[delete]]</a>";
			}
			$templates .= "</ul></ul>\n";
		}
		foreach($langarray as $langpack) {
			$languages .= "<ul><li>$langpack &nbsp; <a href=\"admincp.php?action=tpledit&templateid=$template[templateid]&fn=$langpack.lang.php\">[$lang[edit]]</a></ul>\n";
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header"><td colspan="3"><?=$lang['templates_maint']?></td></tr>

<form method="post" action="admincp.php?action=tpladd&edit=<?=$edit?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<tr bgcolor="<?=ALTBG2?>"><td width="25%"><?=$lang['templates_maint_new']?></td>
<td width="55%"><input type="text" name="name" size="40" maxlength="40"></td>
<td width="20%"><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

<form method="get" action="admincp.php">
<input type="hidden" name="action" value="templates">
<input type="hidden" name="edit" value="<?=$edit?>">
<tr bgcolor="<?=ALTBG1?>"><td><?=$lang['templates_maint_search']?></td><td><input type="text" name="keyword" size="40"></td>
<td><input type="submit" value="<?=$lang['submit']?>"></td></tr></form>

</table><br><br>

<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="85%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['templates_select']?><?=$keywordadd?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td><br><center><b><?=$warning?></b></center><br>
<ul><li><b>Discuz! <?=$lang['templates_language_pack']?></b><?=$languages?></ul>
<ul><li><b>Discuz! <?=$lang['templates_html']?></b><?=$templates?></ul>
</td></tr></table>
<?

	}
			
} elseif($action == 'tpledit') {

	$query = $db->query("SELECT * FROM {$tablepre}templates WHERE templateid='$templateid'");
	if(!$template = $db->fetch_array($query)) {
		cpmsg('templates_edit_nonexistence');
	}

	$fn = str_replace(array('..', '/', '\\'), array('', '', ''), $fn);
	$filename = DISCUZ_ROOT."./$template[directory]/$fn";
	if(!is_writeable($filename)) {
		cpmsg('templates_edit_invalid');
	}

	if(!submitcheck('editsubmit') && $delete != 'yes') {

		$keywordenc = rawurlencode($keyword);

		$fp = fopen($filename, 'rb');
		$content = fread($fp, filesize($filename));
		fclose($fp);

		$content = str_replace("\\'", "'", htmlspecialchars($content));

?>
<script language="JavaScript">
var n = 0;
function displayHTML(obj) {
	win = window.open(" ", 'popup', 'toolbar = no, status = no, scrollbars=yes');
	win.document.write("" + obj.value + "");
}
function HighlightAll(obj) {
	obj.focus();
	obj.select();
	if (document.all) {
		obj.createTextRange().execCommand("Copy");
		window.status = "<?=$lang['templates_edit_clickboard']?>";
		setTimeout("window.status=''", 1800);
	}
}
function findInPage(obj, str) {
	var txt, i, found;
	if (str == "") {
		return false;
	}
	if (document.layers) {
		if (!obj.find(str)) {
			while(obj.find(str, false, true)) {
				n++;
			}
		} else {
			n++;
		}
		if (n == 0) {
			alert("<?=$lang['templates_edit_keyword_not_found']?>");
		}
	}
	if (document.all) {
		txt = obj.createTextRange();
		for (i = 0; i <= n && (found = txt.findText(str)) != false; i++) {
			txt.moveStart('character', 1);
			txt.moveEnd('textedit');
		}
		if (found) {
			txt.moveStart('character', -1);
			txt.findText(str);
			txt.select();
			txt.scrollIntoView();
			n++;
		} else {
			if (n > 0) {
				n = 0;
				findInPage(str);
			} else {
				alert("<?=$lang['templates_edit_keyword_not_found']?>");
			}
		}
	}
	return false;
}
</script>
<form method="post" action="admincp.php?action=tpledit&templateid=<?=$templateid?>&fn=<?=$fn?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="keyword" value="<?=$keywordenc?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="60%" align="center" class="tableborder">
<tr><td class="header"><?=$lang['templates_edit']?> - <?=$fn?></td></tr>
<tr><td bgcolor="<?=ALTBG1?>" align="center">
<textarea cols="100" rows="25" name="templatenew"><?=$content?></textarea><br><br>
<input name="search" type="text" accesskey="t" size="20" onChange="n=0;">
<input type="button" value="<?=$lang['search']?>" accesskey="f" onClick="findInPage(this.form.templatenew, this.form.search.value)">&nbsp;&nbsp;&nbsp;
<input type="button" value="<?=$lang['return']?>" accesskey="e" onClick="history.go(-1)">
<input type="button" value="<?=$lang['preview']?>" accesskey="p" onClick="displayHTML(this.form.templatenew)">
<input type="button" value="<?=$lang['copy']?>" accesskey="c" onClick="HighlightAll(this.form.templatenew)">&nbsp;&nbsp;&nbsp;
<input type="submit" name="editsubmit" value="<?=$lang['submit']?>">
</td></tr></table>
</form>

<?

	} elseif($delete == 'yes') {

		if(!$confirmed) {
			cpmsg('templates_delete_confirm', "admincp.php?action=tpledit&templateid=$templateid&fn=$fn&delete=yes", 'form');
		} else {
			if(@unlink($filename)) {
				cpmsg('templates_delete_succeed', "admincp.php?action=templates&edit=$templateid");
			} else {
				cpmsg('templates_delete_fail');
			}
		}

	} else {

		$fp = fopen($filename, 'wb');
		flock($fp, 2);
		fwrite($fp, stripslashes(str_replace("\x0d\x0a", "\x0a", $templatenew)));
		fclose($fp);

		cpmsg('templates_edit_succeed', "admincp.php?action=templates&edit=$templateid&keyword=$keyword");

	}

} elseif($action == 'tpladd') {

	$query = $db->query("SELECT * FROM {$tablepre}templates WHERE templateid='$edit'");
	if(!$template = $db->fetch_array($query)) {
		cpmsg('templates_add_invalid');
	} elseif(!tpldir_valid($template['directory'])) {
		$directory = $template['directory'];
		cpmsg('templates_directory_invalid');
	} elseif(file_exists(DISCUZ_ROOT."./$template[directory]/$name.htm")) {
		cpmsg('templates_add_duplicate');
	} elseif(!@$fp = fopen(DISCUZ_ROOT."./$template[directory]/$name.htm", 'wb')) {
		cpmsg('templates_add_file_invalid');
	}

	@fclose($fp);
	cpmsg('templates_add_succeed', "admincp.php?action=tpledit&templateid=1&fn=$name.htm");
	
}	

?>