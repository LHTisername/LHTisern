<?php

/*
	[DISCUZ!] archiver/index.php - Discuz! Archiver
	This is NOT a freeware, use is subject to license terms

	Version: 4.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Comsenz Technology Ltd (www.comsenz.com)
	Last Modified: 2005-2-23 16:12
*/

/*** ATTENTION: Do NOT modify the following parameter unless you know what you are doing ***/
$rewritestatus		= 0;
			// request rewriting status of http server, 0=disabled, 1=enabled

$kw_spiders		= 'Bot|Crawl|Spider';
			// keywords regular expression of search engine spiders

$kw_browsers		= 'MSIE|Netscape|Opera|Konqueror|Mozilla';
			// keywords regular expression of Internet browsers

$kw_searchengines	= 'google|yahoo|msn|baidu|yisou|sogou|iask|zhongsou|sohu|sina|163';
			// keywords regular expression of search engine names	
/*******************************************************************************************/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

ob_start();

$runtime = explode (' ', microtime());
$starttime = $runtime[1] + $runtime[0];

define('DISCUZ_ROOT', '../');
define('IN_DISCUZ', TRUE);

require_once '../forumdata/cache/cache_settings.php';

if(!$_DCACHE['settings']['archiverstatus']) {
	exit('Sorry, Discuz! Archiver is not available.');
} elseif($_DCACHE['settings']['bbclosed']) {
	exit('Sorry, the bulletin board has been closed temporarily.');
}

$_SERVER = empty($_SERVER) ? $HTTP_SERVER_VARS : $_SERVER;

require_once '../config.inc.php';
require_once '../include/db_'.$database.'.class.php';
require_once '../templates/default/archiver.lang.php';
require_once '../forumdata/cache/cache_forums.php';
require_once '../forumdata/cache/style_'.$_DCACHE['settings']['styleid'].'.php';

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
$db->select_db($dbname);
unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$boardurl = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF, 0, strrpos($PHP_SELF, 'archiver/'));

$groupid = 7;
$extgroupids = '';

$navtitle = '';
$fid = $page = $tid = 0;
$qm = $rewritestatus ? '' : '?';
$fullversion = array('title' => $_DCACHE['settings']['bbname'], 'link' => 'index.php');
$querystring = preg_replace("/\.html$/i", '', trim($_SERVER['QUERY_STRING']));

if($querystring) {
	$queryparts = explode('-', $querystring);
	foreach($queryparts as $querypart) {
		if(empty($lastpart)) {
			$lastpart = in_array($querypart, array('fid', 'page', 'tid')) ? $querypart : '';
		} else {
			$$lastpart = intval($querypart);
			$lastpart = '';
		}
	}
}

if($tid) {
	$action = 'thread';
	$forward = 'viewthread.php?tid='.$tid;
} elseif($fid) {
	$action = 'forum';
	$forward = 'forumdisplay.php?fid='.$fid;
} else {
	$action = 'index';
	$forward = 'index.php';
}

if($_DCACHE['settings']['archiverstatus'] != 1 && !preg_match("/($kw_spiders)/i", $_SERVER['HTTP_USER_AGENT']) &&
	(($_DCACHE['settings']['archiverstatus'] == 2 && preg_match("/($kw_searchengines)/", $_SERVER['HTTP_REFERER'])) ||
	($_DCACHE['settings']['archiverstatus'] == 3 && preg_match("/($kw_browsers)/", $_SERVER['HTTP_USER_AGENT'])))) {
	header("Location: $boardurl$forward");
	exit;
}

require_once "./include/$action.inc.php";

$runtime = explode(' ', microtime());
$totaltime = round(($runtime[1] + $runtime[0] - $starttime), 6);

?>
<br><table cellspacing="0" cellpadding="0" width="<?=TABLEWIDTH?>" align="center">
<tr><td class="bold"><font color="<?=TEXT?>"><?=$lang['full_version']?>: </font><a href="<?=$fullversion['link']?>" target="_blank" style="color: <?=TEXT?>"><?=$fullversion['title']?></a><br><br>
</td></tr></table>

<br><center><span style="font: 11px Tahoma, Arial; color: <?=TEXT?>">
Powered by <a href="http://www.discuz.net" target="_blank" style="color: <?=TEXT?>"><b>Discuz! Archiver</b></a> <b style="color:#FF9900"><?=$_DCACHE['settings']['version']?></b></a>&nbsp;
&copy; 2001-2005 <a href="http://www.comsenz.com" target="_blank" style="color: <?=TEXT?>">Comsenz Technology Ltd</a>
<?=($_DCACHE['settings']['debug'] ? '<br>Processed in '.$totaltime.' second(s), '.$db->querynum.' queries' : '')?>
</td></tr><tr><td <?=MAINTABLEBGCODE?> style="padding: <?=BORDERWIDTH?>">
<table cellspacing="0" cellpadding="<?=TABLESPACE?>" width="100%" class="smalltxt">
<tr style="font-size: 0px; line-height: 0px; spacing: 0px; padding: 0px; <?=HEADERBGCODE?>"><td>&nbsp;</td></tr>
</table>

</td></tr></table><br>
</body></html>
<?

function multi($total, $page, $perpage, $link) {
	$pages = @ceil($total / $perpage) + 1;
	$pagelink = '';
	if($pages > 1) {
		$pagelink .= "<b>{$GLOBALS[lang][page]}: </b>\n";
		for($i = 1; $i < $pages; $i++) {
			$pagelink .= ($i == $page ? "<b>[$i]</b>" : "<a href=archiver/$link-page-$i.html>$i</a>")." \n";
		}
	}
	return $pagelink;
}

function forumperm($viewperm) {
	return (empty($viewperm) || ($viewperm && strstr($viewperm, "\t7\t")));
}

?>