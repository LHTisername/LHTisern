<?php
//-----------------------------------------------------------------------------
//    Discuz! Board 1.0 Standard - Discuz! ������̳ (PHP & MySQL) 1.0 ��׼��
//-----------------------------------------------------------------------------
//    Copyright(C) Dai Zhikang, Crossday Studio, 2002. All rights reserved
//
//    Crossday ������ www.crossday.com    *Discuz! ����֧�� www.Discuz.net
//-----------------------------------------------------------------------------
//  ����ϸ�Ķ� Discuz! ��ȨЭ��,�鿴��ʹ�� Discuz! ���κβ�����ζ����ȫͬ��
//  Э���е�ȫ������,�����֮��֧�ֹ��������ҵ,�Ͻ�һ��Υ��Э�����Ȩ��Ϊ.
//-----------------------------------------------------------------------------
// Discuz! רע���ṩ��Чǿ�����̳�������,��������ҵ��;,�����빺��ʹ����Ȩ!
//-----------------------------------------------------------------------------


require "./header.php";
$tplnames = "css,header,footer";

if($showoldetails == "no") {
	setcookie("onlinedetail", 0, $timestamp + (86400 * 365), $cookiepath, $cookiedomain);
	$CDB_SESSION_VARS[onlinedetail] = 0;
} elseif($showoldetails == "yes") {
	setcookie("onlinedetail", 1, $timestamp - (86400 * 365), $cookiepath, $cookiedomain);
	$CDB_SESSION_VARS[onlinedetail] = 1;
}

if($gid) {
	$whosonlinestatus = 0;
	$query = $db->query("SELECT name FROM $table_forums WHERE fid='$gid' AND type='group'");
	$cat = $db->fetch_array($query);
	$navigation ="&raquo; $cat[name]";
	$navtitle = " - $cat[name]";
}

$query = $db->query("SELECT COUNT(*) FROM $table_members");
$members = $db->result($query, 0);

$encodemember = rawurlencode($lastmember);
$memhtml = $members ? "<a href=\"member.php?action=viewpro&username=$encodemember\"><span class=\"bold\">$lastmember</span></a>" : "<span class=\"bold\">δ֪</span>";

if($status == "��̳����Ա") {
	$statusicon = "online_admin.gif";
} elseif($status == "��������" || $online[status] == "����") {
	$statusicon = "online_moderator.gif";
} elseif($status == "�ο�") {
	$statusicon = "online_guest.gif";
} else {
	$statusicon = "online_member.gif";
}

if($CDB_CACHE_VARS[announcements]) {
	$announcements = $space = "";
	foreach($CDB_CACHE_VARS[announcements] as $announcement) {
		if($timestamp >= $announcement[starttime] && ($timestamp <= $announcement[endtime] || !$announcement[endtime])) {
			$announcements .= "$space<a href=\"misc.php?action=announcements&id=$announcement[id]#$announcement[id]\"><span class=\"bold\">$announcement[subject]</span> ".
				"(".gmdate("$dateformat", $announcement[starttime] + $timeoffset * 3600).")</a>";
			$space = "&nbsp; &nbsp; &nbsp; ";
		}
	}
	if(strlen($announcements) > 200) {
		$announcements = "<marquee width=\"100%\" scrollamount=\"3\" onMouseOver=\"this.stop();\" onMouseOut=\"this.start();\">$announcements</marquee>";
	}
} else {
	$announcements = "��ֹ�� ".gmdate("$dateformat $timeformat", $timestamp + $timeoffset * 3600)." û�й���";
}

if(!$gid) {

	preloader("index_news,index_forumlink_row,index_forumlink_noimg_row,index_forumlink,index_whosonline,index_forum,index_forum_lastpost,index_category,index");
	$useraction = "�����{$bbname}����ҳ";

	$newthreads = $timestamp - $lastvisit;
	if($cdbuser) {
		$mytopicslink = "| <a href=\"misc.php?action=search&srchuname=".rawurlencode($cdbuserss)."&srchfrom=604800&searchsubmit=1\">�ҵĻ���</a> ";
	}
	if(is_array($CDB_CACHE_VARS[forumlinks])) {
		$forumlink_row = $forumlink_tight_row1 = $forumlink_tight_row2 = "";
		$flinkdisplay = 0;
		foreach($CDB_CACHE_VARS[forumlinks] as $flink) {
			if($flink[note] && $flink[logo]) {
				eval("\$forumlink_row .= \"".template("index_forumlink_row")."\";");
			} elseif($flink[note]) {
				$forumlink_content = "<a href=\"$flink[url]\" target=\"_blank\"><span class=\"bold\">$flink[name]</span></a><br>$flink[note]";
				eval("\$forumlink_row .= \"".template("index_forumlink_noimg_row")."\";");
			} elseif(!$flink[note] && $flink[logo]) {
				$forumlink_tight_row1 .= "<a href=\"$flink[url]\" target=\"_blank\"><img src=\"$flink[logo]\" border=\"0\" alt=\"$flink[name]\"></a> &nbsp; ";
			} elseif(!$flink[note] && !$flink[logo]) {
				$forumlink_tight_row2 .= "<a href=\"$flink[url]\" target=\"_blank\">[$flink[name]]</a> ";
			}
		}
		if($forumlink_tight_row1 || $forumlink_tight_row2) {
			$forumlink_tight_row1 = $forumlink_tight_row1 ? $forumlink_tight_row1."<br>" : "";
			$forumlink_content = $forumlink_tight_row1.$forumlink_tight_row2;
			eval("\$forumlink_row .= \"".template("index_forumlink_noimg_row")."\";");
		}
		if($forumlink_row) {
			eval("\$forumlink = \"".template("index_forumlink")."\";");
		}
	}

	$forumlist = "";
	$threads = $posts = 0;
	$query = $db->query("SELECT * FROM $table_forums WHERE status='1' ORDER BY displayorder");
	while($forum = $db->fetch_array($query)) {
		$forums[] = $forum;
		$forumname[$forum[fid]] = strip_tags($forum[name]);
		if($forum[type] != "group") {
			$threads += $forum[threads];
			$posts += $forum[posts];
		}
	}

	foreach($forums as $groupfid => $group) {
		if($group[type] == "group") {
			eval("\$forumlist .= \"".template("index_category")."\";");
			foreach($forums as $forumfid => $forum) {
				if($forum[fup] == $group[fid] && $forum[type] == "forum") {
					$forumlist .= forum($forum, "index_forum");
				}
			}
		} elseif(!$group[fup] && $group[type] == "forum") {
			$forumlist .= forum($group, "index_forum");
		}
	}

	if($whosonlinestatus) {
		$membercount = $guestcount = 0;

		$onlineinfo = explode("\t", $onlinerecord);
		if(!isset($HTTP_COOKIE_VARS[onlinedetail]) && !isset($CDB_SESSION_VARS[onlinedetail])) {
			if($onlineinfo[0] > 500) {
				setcookie("onlinedetail", 0, $timestamp + (86400 * 365), $cookiepath, $cookiedomain);
				$CDB_SESSION_VARS[onlinedetail] = 0;
			} else {
				setcookie("onlinedetail", 1, $timestamp + (86400 * 365), $cookiepath, $cookiedomain);
				$CDB_SESSION_VARS[onlinedetail] = 1;
			}
		}
		if(($HTTP_COOKIE_VARS[onlinedetail] || $CDB_SESSION_VARS[onlinedetail] || $showoldetails == "yes") && $showoldetails != "no") {
			$oldetaillink = "<a href=\"index.php?showoldetails=no\" style=\"color: $cattext\">[�ر���ϸ�б�]</a>";
			$memtally = $table = "";
			$online = array("username" => $cdbuserss, "time" => $timestamp, "fid" => 0, "action" => "�����{$bbname}����ҳ", "status" => $status);
			$query = $db->query("SELECT username, status, time, fid, action, username<>'' AS guests FROM $table_sessions WHERE !(username='$cdbuser' AND ip='$onlineip') ORDER BY guests DESC");
			do {
				$online[username] ? $membercount++ : $guestcount++;
				$online[time] = gmdate("$timeformat", $online[time] + ($timeoffset * 3600));
				$onlinedetail = "ʱ�䣺$online[time]";
				$onlinedetail .= $online[fid] ? "\n��̳��".$forumname[$online[fid]] : NULL;
				$onlinedetail .= "\n������$online[action]";

				if($online[status] == "��̳����Ա") {
					$memtally .= "$table<img src=\"$imgdir/online_admin.gif\" align=\"absmiddle\" alt=\"$onlinedetail\"> <a href=\"member.php?action=viewpro&username=".rawurlencode($online[username])."\" title=\"$onlinedetail\"><b><i>$online[username]</i></b></a>";
				} elseif($online[status] == "��������" || $online[status] == "����") {
					$memtally .= "$table<img src=\"$imgdir/online_moderator.gif\" align=\"absmiddle\" alt=\"$onlinedetail\"> <a href=\"member.php?action=viewpro&username=".rawurlencode($online[username])."\" title=\"$onlinedetail\"><b>$online[username]</b></a>";
				} elseif($online[status] == "��ʽ��Ա") {
					$memtally .= "$table<img src=\"$imgdir/online_member.gif\" align=\"absmiddle\" alt=\"$onlinedetail\"> <a href=\"member.php?action=viewpro&username=".rawurlencode($online[username])."\" title=\"$onlinedetail\">$online[username]</a>";
				} else {
					$memtally .= "$table<img src=\"$imgdir/online_guest.gif\" align=\"absmiddle\" alt=\"$onlinedetail\"> <span title=\"$onlinedetail\">�ο�</span>";
				}

				$table = ($membercount + $guestcount) % 7 == 0 ? "</td></tr><tr><td nowrap>" : "</td><td nowrap>";
			} while($online = $db->fetch_array($query));
			$onlinenum = $membercount + $guestcount;
			$memberlist = "<tr><td colspan=\"7\"><hr noshade size=\"0\" width=\"100%\" color=\"$bordercolor\" align=\"center\"></td></tr>\n<tr><td nowrap>$memtally</td></tr>";
			$memonmsg = "<font style=\"color: $cattext\">&nbsp;<span class=\"bold\">$membercount</span> λ��Ա��<span class=\"bold\">$guestcount</span> λ�οͣ��� <span class=\"bold\">$onlinenum</span> �� | ��߼�¼�� <span class=\"bold\">".gmdate("$dateformat $timeformat", $onlineinfo[1] + ($timeoffset * 3600))."</span> �� <span class=\"bold\">$onlineinfo[0]</span> ������</font>";
		} else {
			$oldetaillink = "<a href=\"index.php?showoldetails=yes\" style=\"color: $cattext\">[����ϸ�б�]</a>";
			$query = $db->query("SELECT COUNT(*) FROM $table_sessions");
			$onlinenum = $db->result($query, 0);
			$memonmsg = "<font style=\"color: $cattext\">&nbsp;�� <span class=\"bold\">$onlinenum</span> ������ | ��߼�¼�� <span class=\"bold\">".gmdate("$dateformat $timeformat", $onlineinfo[1] + ($timeoffset * 3600))."</span> �� <span class=\"bold\">$onlineinfo[0]</span> ������</font>";
		}

		if($onlinenum > $onlineinfo[0]) {
			$db->query("UPDATE $table_settings SET onlinerecord='$onlinenum\t$timestamp'");
			updatecache("settings", $table_settings, "", "vars");
			$onlineinfo[0] = $onlinenum;
			$onlineinfo[1] = $timestamp;
		}

		eval("\$whosonline = \"".template("index_whosonline")."\";");
	}

	if(is_array($CDB_CACHE_VARS[news])) {
		$num = 0;
		$index_news_row = "";
		foreach($CDB_CACHE_VARS[news] as $news) {
			$news[subject] = htmlspecialchars($news[subject]);
			$news[link] = htmlspecialchars($news[link]);
			eval("\$news[subject] = addslashes(\"$news[subject]\");");
			eval("\$news[link] = addslashes(\"$news[link]\");");
			if($news[link]) {
				$index_news_row .= "fcontent[$num] = \"<a id='newslink' href='$news[link]'>$news[subject]</a>\";\n";
			} else {
				$index_news_row .= "fcontent[$num] = \"$news[subject]\";\n";
			}
			$num++;
		}
		if($index_news_row) {
			eval("\$index_news = \"".template("index_news")."\";");
		}
	}
	
	if($cdbuser && $newu2u) {
		include "./u2uprompt.php";
	}

} else {

	preloader("index_category,index_forum_lastpost,index_forum,index");

	$forumlist = "";
	$threads = $posts = 0;
	$queryg = $db->query("SELECT type, fid, name, lastpost FROM $table_forums WHERE type='group' AND fid='$gid' AND status='1' ORDER BY displayorder");
	$group = $db->fetch_array($queryg);
	eval("\$forumlist .= \"".template("index_category")."\";");
	$query = $db->query("SELECT * FROM $table_forums WHERE type='forum' AND status='1' AND fup='$group[fid]' ORDER BY displayorder");
	while($forum = $db->fetch_array($query)) {
		$forumlist .= forum($forum, "index_forum");
		$threads += $forum[threads];
		$posts += $forum[posts];
	}
	$useraction = "�����$group[name]��";
}

eval("\$index = \"".template("index")."\";");
echo $index;

gettotaltime();
eval("\$footer = \"".template("footer")."\";");
echo $footer;

cdb_output();

?>