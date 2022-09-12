<?php

/*
	[DISCUZ!] archiver/include/thread.inc.php - thread module for Discuz! Archiver
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Comsenz Technology Ltd (www.comsenz.com)
	Last Modified: 2005-3-10 9:09
*/


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$query = $db->query("SELECT * FROM {$tablepre}threads t
	LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
	LEFT JOIN {$tablepre}forumfields ff ON ff.fid=f.fid
	WHERE t.tid='$tid' AND t.readperm='0' AND t.price<='0' AND t.displayorder>='0'
	AND f.status='1' AND ff.password=''");

$thread = $db->fetch_array($query);
$page = empty($page) ? 1 : $page;

$navtitle = ($thread['type'] == 'sub' ? ' - '.strip_tags($_DCACHE['forums'][$thread['fup']]['name']) : '').
	' - '.strip_tags($thread['name']).' - '.$thread['subject'].'('.$lang['page'].' '.$page.')';

require_once './include/header.inc.php';

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="<?=TABLEWIDTH?>" align="center" class="tableborder">
<?

if(!$thread || !(!$thread['viewperm'] || ($thread['viewperm'] && forumperm($thread['viewperm'])))) {

?>
<tr><td bgcolor="<?=ALTBG1?>" class="bold"><a href="archiver/"><?=$_DCACHE['settings']['bbname']?></a></td></tr></table><br><br>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="<?=TABLEWIDTH?>" align="center" class="tableborder">
<tr><td bgcolor="<?=ALTBG2?>"><br><?=$lang['thread_nonexistence']?><br><br></td></tr></table>
<?

} else {

	$navsub = $thread['type'] == 'sub' ? " <a href=\"archiver/{$qm}fid-$thread[fup].html\">{$_DCACHE[forums][$thread[fup]][name]}</a> <b>&raquo;</b> ": ' ';
	$fullversion = array('title' => $thread['subject'], 'link' => "viewthread.php?tid=$tid");

	$ppp = $_DCACHE['settings']['postperpage'] * 2;
	$start = ($page - 1) * $ppp;

?>
<tr><td bgcolor="<?=ALTBG1?>" class="bold"><a href="archiver/"><?=$_DCACHE['settings']['bbname']?></a> <b>&raquo;</b><?=$navsub?><a href="archiver/<?=$qm?>fid-<?=$thread['fid']?>.html"><?=$thread['name']?></a> <b>&raquo;</b> <?=$thread[subject]?></td></tr></table>
<table cellspacing="0" cellpadding="0" width="<?=TABLEWIDTH?>" align="center"><tr><td align="center"><br><?=multi($thread['replies'] + 1, $page, $ppp, "{$qm}tid-$tid")?><br><br></td></tr></table>
<?

	$query = $db->query("SELECT author, dateline, subject, message
		FROM {$tablepre}posts
		WHERE tid='$tid' AND invisible='0'
		ORDER BY dateline LIMIT $start, $ppp");

	while($post = $db->fetch_array($query)) {
		$post['dateline'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $post['dateline'] + $_DCACHE['timeoffset'] * 3600);
		$post['message'] = ($post['subject'] ? '<b>'.$post['subject'].'</b><br><br>' : '').nl2br(preg_replace(array('/&amp;(#\d{3,5};)/', "/\[hide=?\d*\](.+?)\[\/hide\]/is"),
			array('&\\1', '<b>**** Hidden Message *****</b>'),
			str_replace(array('&', '"', '<', '>', "\t", '   ', '  '),
			array('&amp;', '&quot;', '&lt;', '&gt;', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'),
			$post['message'])));
		if($thread['jammer']) {
			$post['message'] =  preg_replace("/\<br \/\>/e", "jammer()", $post['message']);
		}

?>
<table cellspacing="<?=INNERBORDERWIDTH?>" cellpadding="<?=TABLESPACE?>" width="<?=TABLEWIDTH?>" align="center" class="tableborder">
<tr><td bgcolor="<?=ALTBG1?>"><table cellspacing="0" cellpadding="0" width="100%"><td class="bold"><?=$post['author']?></td><td align="right"><?=$post['dateline']?></td></tr></table></td></tr>
<tr><td bgcolor="<?=ALTBG2?>" class="smalltxt"><?=$post['message']?></td></tr>
</table><br>
<?

	}

}

function jammer() {
	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(0, 59)).chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<font style="font-size:0px;color:'.ALTBG2.'">'.$randomstr.'</font><br />' :
		'<br /><span style="display:none">'.$randomstr.'</span>';
}

?>