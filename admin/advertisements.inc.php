<?php

/*
	[DISCUZ!] admin/advertisements.inc.php - advertisements management
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Web: http://www.comsenz.com
	Copyright: 2001-2005 Comsenz Technology Ltd.
	Last Modified: 2005-8-13 11:10
*/

if(!defined('IN_DISCUZ') || !isset($PHP_SELF) || !preg_match("/[\/\\\\]admincp\.php$/", $PHP_SELF)) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

cpheader();

if($action == 'adv') {

	if(!submitcheck('advsubmit')) {

		$advs = '';
		$query = $db->query("SELECT * FROM {$tablepre}advertisements ORDER BY type, displayorder, targets DESC");
		while($adv = $db->fetch_array($query)) {
			$adv['type'] = $lang['advertisements_type_'.$adv['type']];

			$adv['starttime'] = $adv['starttime'] ? gmdate($dateformat, $adv['starttime'] + $timeoffset * 3600) : $lang['unlimited'];
			$adv['endtime'] = $adv['endtime'] ? gmdate($dateformat, $adv['endtime'] + $timeoffset * 3600) : $lang['unlimited'];

			if($adv['targets'] == '') {
				$adv['targets'] = $lang['all'];
			} else {
				$targetsarray = array();
				foreach(explode("\t", $adv['targets']) as $target) {
					$targetsarray[] = $target ? '<a href="forumdisplay.php?fid='.$target.'" target="_blank">'.$_DCACHE['forums'][$target]['name'].'</a>' : '<a href="index.php" target="_blank">'.$lang['home'].'</a>';
				}
				$adv['targets'] = implode(', ', $targetsarray);
			}

			$adv['parameters'] = unserialize($adv['parameters']);

			$advs .= "<tr align=\"center\"><td class=\"altbg1\"><input type=\"checkbox\" name=\"delete[]\" value=\"$adv[advid]\"></td>".
				"<td class=\"altbg2\"><input type=\"checkbox\" name=\"availablenew[$adv[advid]]\" value=\"1\" ".($adv['available'] ? 'checked' : '')."></td>".
				"<td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"displayordernew[$adv[advid]]\" value=\"$adv[displayorder]\"></td>".
				"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"titlenew[$adv[advid]]\" value=\"".dhtmlspecialchars($adv['title'])."\"></td>".
				"<td class=\"altbg1\">$adv[type]</td>".
				"<td class=\"altbg2\">".$lang['advertisements_style_'.$adv['parameters']['style']]."</td>".
				"<td class=\"altbg1\">$adv[starttime]</td>".
				"<td class=\"altbg2\">$adv[endtime]</td>".
				"<td class=\"altbg1\">$adv[targets]</td>".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=advedit&advid=$adv[advid]\">[$lang[detail]]</a></td></tr>";
		}

?>
<form method="post" action="admincp.php?action=adv">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr><td colspan="2" class="header"><?=$lang['advertisements_add']?></td></tr>
<tr><td colspan="2" class="category">
<input type="button" value="<?=$lang['advertisements_type_headerbanner']?>" onclick="window.location='admincp.php?action=advadd&type=headerbanner';"> &nbsp;
<input type="button" value="<?=$lang['advertisements_type_footerbanner']?>" onclick="window.location='admincp.php?action=advadd&type=footerbanner';"> &nbsp;
<input type="button" value="<?=$lang['advertisements_type_text']?>" onclick="window.location='admincp.php?action=advadd&type=text';"> &nbsp;
<input type="button" value="<?=$lang['advertisements_type_thread']?>" onclick="window.location='admincp.php?action=advadd&type=thread';"> &nbsp;
<input type="button" value="<?=$lang['advertisements_type_float']?>" onclick="window.location='admincp.php?action=advadd&type=float';"> &nbsp;
<input type="button" value="<?=$lang['advertisements_type_couplebanner']?>" onclick="window.location='admincp.php?action=advadd&type=couplebanner';"></td></tr>
</table><br>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="98%" align="center" class="tableborder">
<tr align="center" class="header"><td width="48"><input type="checkbox" name="chkall" class="header" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td width="5%"><?=$lang['available']?></td>
<td width="8%"><?=$lang['display_order']?></td>
<td width="15%"><?=$lang['subject']?></td>
<td width="10%"><?=$lang['type']?></td>
<td width="5%"><?=$lang['advertisements_style']?></td>
<td width="8%"><?=$lang['start_time']?></td>
<td width="8%"><?=$lang['end_time']?></td>
<td width="30%"><?=$lang['advertisements_targets']?></td>
<td width="6%"><?=$lang['edit']?></td></tr>
<?=$advs?>
</table>

<br><center><input type="submit" name="advsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if(is_array($delete) && $delete) {
			$advids = implode('\',\'', $delete);
			$db->query("DELETE FROM {$tablepre}advertisements WHERE advid IN ('$advids')");
		}

		if(is_array($titlenew)) {
			foreach($titlenew as $advid => $title) {
				$db->query("UPDATE {$tablepre}advertisements SET available='$availablenew[$advid]', displayorder='$displayordernew[$advid]', title='".cutstr($titlenew[$advid], 50)."' WHERE advid='$advid'", 'UNBUFFERED');
			}
		}

		updatecache('settings');

		cpmsg('advertisements_update_succeed', 'admincp.php?action=adv');

	}

} elseif($action == 'advadd' && in_array($type, array('headerbanner', 'footerbanner', 'text', 'thread', 'float', 'couplebanner')) || ($action == 'advedit' && $advid)) {

	if(!submitcheck('advsubmit')) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		if($action == 'advedit') {
			$query = $db->query("SELECT * FROM {$tablepre}advertisements WHERE advid='$advid'");
			if(!$adv = $db->fetch_array($query)) {
				cpmsg('undefined_action');
			}
			$adv['parameters'] = unserialize($adv['parameters']);
		} else {
			$adv = array('type' => $type, 'parameters' => array('style' => 'code'), 'starttime' => $timestamp);
		}

		$adv['targets'] = $adv['targets'] != '' ? explode("\t", $adv['targets']) : array('all');

		$targetsselect = '<select name="advnew[targets][]" size="10" multiple="multiple"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
			'<option value="">&nbsp;</option><option value="0">&nbsp;&nbsp;> '.$lang['home'].'</option>'.
			'<option value="">&nbsp;</option>'.forumselect().'</select>';

		foreach($adv['targets'] as $target) {
			$targetsselect = preg_replace("/(\<option value=\"$target\")(\>)/", "\\1 selected=\"selected\" \\2", $targetsselect);
		}

		$adv['starttime'] = $adv['starttime'] ? gmdate('Y-n-j', $adv['starttime'] + $timeoffset * 3600) : '';
		$adv['endtime'] = $adv['endtime'] ? gmdate('Y-n-j', $adv['endtime'] + $timeoffset * 3600) : '';

		$styleselect = array($adv['parameters']['style'] => 'selected');

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="90%" align="center" class="tableborder">
<tr class="header"><td><?=$lang['tips']?></td></tr>
<tr bgcolor="<?=ALTBG1?>"><td>
<br><?=$lang['advertisements_type_'.$adv['type'].'_tips']?>
</td></tr></table>

<br><form method="post" name="settings" action="admincp.php?action=<?=$action.($action == 'advadd' ? '&type='.$type : '&advid='.$advid)?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		if($action == 'advadd') {
			$title = $lang['advertisements_add'].' - '.$lang['advertisements_type_'.$type];
		} else {
			$title = $lang['advertisements_edit'].' - '.$lang['advertisements_type_'.$adv['type']].' - '.$adv['title'];
		}

		showtype($title, 'top');
		showsetting('advertisements_edit_style', '', '', '<select name="advnew[style]" onchange="var styles, key;styles=new Array(\'code\',\'text\',\'image\',\'flash\'); for(key in styles) {var obj=findobj(\'style_\'+styles[key]); obj.style.display=styles[key]==this.options[this.selectedIndex].value?\'\':\'none\';}"><option value="code" '.$styleselect['code'].'> '.$lang['advertisements_style_code'].'</option><option value="text" '.$styleselect['text'].'> '.$lang['advertisements_style_text'].'</option><option value="image" '.$styleselect['image'].'> '.$lang['advertisements_style_image'].'</option><option value="flash" '.$styleselect['flash'].'> '.$lang['advertisements_style_flash'].'</option></select>');
		showsetting('advertisements_edit_title', 'advnew[title]', $adv['title'], 'text');
		showsetting('advertisements_edit_targets', '', '', $targetsselect);
		showsetting('advertisements_edit_starttime', 'advnew[starttime]', $adv['starttime'], 'text');
		showsetting('advertisements_edit_endtime', 'advnew[endtime]', $adv['endtime'], 'text');

		echo '<div>';
		showadvtype('code', $adv['parameters']['style']);
		showsetting('advertisements_edit_style_code_html', 'advnew[code][html]', $adv['parameters']['html'], 'textarea');

		showadvtype('text', $adv['parameters']['style']);
		showsetting('advertisements_edit_style_text_title', 'advnew[text][title]', $adv['parameters']['title'], 'text');
		showsetting('advertisements_edit_style_text_link', 'advnew[text][link]', $adv['parameters']['link'], 'text');
		showsetting('advertisements_edit_style_text_size', 'advnew[text][size]', $adv['parameters']['size'], 'text');

		showadvtype('image', $adv['parameters']['style']);
		showsetting('advertisements_edit_style_image_url', 'advnew[image][url]', $adv['parameters']['url'], 'text');
		showsetting('advertisements_edit_style_image_link', 'advnew[image][link]', $adv['parameters']['link'], 'text');
		showsetting('advertisements_edit_style_image_width', 'advnew[image][width]', $adv['parameters']['width'], 'text');
		showsetting('advertisements_edit_style_image_height', 'advnew[image][height]', $adv['parameters']['height'], 'text');
		showsetting('advertisements_edit_style_image_alt', 'advnew[image][alt]', $adv['parameters']['alt'], 'text');

		showadvtype('flash', $adv['parameters']['style']);
		showsetting('advertisements_edit_style_flash_url', 'advnew[flash][url]', $adv['parameters']['url'], 'text');
		showsetting('advertisements_edit_style_flash_width', 'advnew[flash][width]', $adv['parameters']['width'], 'text');
		showsetting('advertisements_edit_style_flash_height', 'advnew[flash][height]', $adv['parameters']['height'], 'text');

		showtype('', 'bottom');

		echo '</div><br><br><center><input type="submit" name="advsubmit" value="'.$lang['submit'].'"></center></form>';

	} else {

		$advnew['starttime'] = $advnew['starttime'] ? strtotime($advnew['starttime']) : 0;
		$advnew['endtime'] = $advnew['endtime'] ? strtotime($advnew['endtime']) : 0;

		if(!$advnew['title']) {
			cpmsg('advertisements_title_invalid');
		} elseif($advnew['endtime'] && ($advnew['endtime'] <= $timestamp || $advnew['endtime'] <= $advnew['starttime'])) {
			cpmsg('advertisements_endtime_invalid');
		} elseif(($advnew['style'] == 'code' && !$advnew['code']['html'])
			|| ($advnew['style'] == 'text' && (!$advnew['text']['title'] || !$advnew['text']['link']))
			|| ($advnew['style'] == 'image' && (!$advnew['image']['url'] || !$advnew['image']['link']))
			|| ($advnew['style'] == 'flash' && (!$advnew['flash']['url'] || !$advnew['flash']['width'] || !$advnew['flash']['height']))) {
			cpmsg('advertisements_parameter_invalid');
		}

		if($action == 'advadd') {
			$db->query("INSERT INTO {$tablepre}advertisements (available, type)
				VALUES ('1', '$type')");
			$advid = $db->insert_id();
		} else {
			$query = $db->query("SELECT type FROM {$tablepre}advertisements WHERE advid='$advid'");
			$type = $db->result($query, 0);
		}

		foreach($advnew[$advnew['style']] as $key => $val) {
			$advnew[$advnew['style']][$key] = stripslashes($val);
		}

		$targetsarray = array();
		if(is_array($advnew['targets'])) {
			foreach($advnew['targets'] as $target) {
				if($target == 'all') {
					$targetsarray = array();
					break;
				} elseif(preg_match("/^\d+$/", $target) && ($target == 0 || in_array($_DCACHE['forums'][$target]['type'], array('forum', 'sub')))) {
					$targetsarray[] = $target;
				}
			}
		}
		$advnew['targets'] = implode("\t", $targetsarray);

		switch($advnew['style']) {
			case 'code':
				$advnew['code'] = $advnew['code']['html'];
				break;
			case 'text':
				$advnew['code'] = '<a href="'.$advnew['text']['link'].'" target="_blank" '.($advnew['text']['size'] ? 'style="font-size: '.$advnew['text']['size'].'"' : '').'>'.$advnew['text']['title'].'</a>';
				break;
			case 'image':
				$advnew['code'] = '<a href="'.$advnew['image']['link'].'" target="_blank"><img src="'.$advnew['image']['url'].'"'.($advnew['image']['height'] ? ' height="'.$advnew['image']['height'].'"' : '').($advnew['image']['width'] ? ' width="'.$advnew['image']['width'].'"' : '').($advnew['image']['alt'] ? ' alt="'.$advnew['image']['alt'].'"' : '').' border="0"></a>';
				break;
			case 'flash':
				$advnew['code'] = '<embed width="'.$advnew['flash']['width'].'" height="'.$advnew['flash']['height'].'" src="'.$advnew['flash']['url'].'" type="application/x-shockwave-flash"></embed>';
				break;
		}

		if($type == 'float') {
			$advnew['code'] = 'theFloaters.addItem(\'floatAdv\',6,\'document.body.clientHeight-80\',\''.addslashes($advnew['code']).'\');';
		} elseif($type == 'couplebanner') {
			$advnew['code'] = addslashes($advnew['code'].'<br><img src="images/common/advclose.gif" onMouseOver="this.style.cursor=\'hand\'" onClick="closeBanner();">');
			$advnew['code'] = 'theFloaters.addItem(\'coupleBannerAdv\',0,0,\'<div style="position: absolute; left: 6px; top: 6px;">'.$advnew['code'].'</div><div style="position: absolute; right: 6px; top: 6px;">'.$advnew['code'].'</div>\');';
		}

		if($advnew['style'] == 'code') {
			$advnew['parameters'] = addslashes(serialize(array_merge(array('style' => $advnew['style']), array('html' => $advnew['code']))));
		} else {
			$advnew['parameters'] = addslashes(serialize(array_merge(array('style' => $advnew['style']), $advnew[$advnew['style']], array('html' => $advnew['code']))));
		}
		$advnew['code'] = addslashes($advnew['code']);

		$query = $db->query("UPDATE {$tablepre}advertisements SET title='$advnew[title]', targets='$advnew[targets]', parameters='$advnew[parameters]', code='$advnew[code]', starttime='$advnew[starttime]', endtime='$advnew[endtime]' WHERE advid='$advid'");

		updatecache('settings');

		cpmsg('advertisements_succeed', "admincp.php?action=adv");

	}

}

function showadvtype($type, $curtype) {
	echo '</table></div><div id="style_'.$type.'" style="'.($type != $curtype ? 'display: none' : '').'"><br><br><table cellspacing="'.INNERBORDERWIDTH.'"'.
		' cellpadding="'.TABLESPACE.'" width="90%" align="center" class="tableborder">'.
		'<tr class="header"><td colspan="2">'.$GLOBALS['lang']['advertisements_edit_style_'.$type].'</td></tr>';
}

?>