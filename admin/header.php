<?php

/*
	[DISCUZ!] admin/header.php - menu of administrator's control panel
	This is NOT a freeware, use is subject to license terms

	Version: 1.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2003/3/10 9:35
*/

@header('Content-Type: text/html; charset='.CHARSET);

?>
<html>
<head>
<? include template('css'); ?>
</head>

<body leftmargin="0" topmargin="0">
<table cellspacing="0" cellpadding="2" border="0" width="100%" height="100%" bgcolor="<?=ALTBG2?>">
<tr valign="middle" class="smalltxt">
<td width="33%"><a href="http://www.crossday.com" target="_blank">Discuz! <?=$version?> <?=$lang['admincp']?></a></td>
<td width="33%" align="center"><a href="http://www.Discuz.net" target="_blank"><?=$lang['header_offical']?></a></td>
<td width="34%" align="right"><a href="index.php" target="_blank"><?=$lang['header_home']?></a></TD>
</tr>
</table>
</body></html>
