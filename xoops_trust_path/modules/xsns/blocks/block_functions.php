<?php

function b_xsns_recent_topic_show($options)
{
	global $xoopsUser, $xoopsUserIsAdmin;
	
	require_once dirname(dirname(__FILE__)).'/include/common_functions.php';
	
	$db =& Database::getInstance();
	$myts =& MyTextSanitizer::getInstance();
	
	$mydirname = empty($options[0]) ? 'xsns' : $options[0];
	$item_limit = empty($options[1]) ? 5 : intval($options[1]);
	
	if( preg_match( '/[^0-9a-zA-Z_-]/' , $mydirname ) ) die( 'Invalid dirname' ) ;
	
	$constpref = '_MB_'.strtoupper($mydirname);
	
	$block = array();
	$perm_arr = array();
	
	$own_uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : -1;
	
	// topic search
	$sql = "SELECT ".
			"c.c_commu_id AS cid,".
			"c.name AS cname,".
			"c.uid_admin AS cadmin,".
			"c.uid_sub_admin AS csubadmin,".
			"c.public_flag AS cflag,".
			"t.c_commu_topic_id AS tid,".
			"t.name AS tname,".
			"tc.body AS tcbody,".
			"tc.uid AS tcuid,".
			"MAX(tc.number) AS comment_count,".
			"MAX(tc.r_datetime) AS max_r_datetime".
			" FROM (". $db->prefix($mydirname.'_c_commu'). " c".
			" INNER JOIN ". $db->prefix($mydirname.'_c_commu_topic_comment'). " tc".
			" USING(c_commu_id))".
			" INNER JOIN ". $db->prefix($mydirname.'_c_commu_topic'). " t".
			" USING(c_commu_topic_id)".
			" GROUP BY tid".
			" ORDER BY max_r_datetime DESC";
	$rs = $db->query($sql);
	if(!$rs || $db->getRowsNum($rs) < 1){
		return array();
	}
	
	$today = date('Y-m-d');
	$item_count = 0;
	require_once dirname(dirname(__FILE__)).'/userlib/utils.php';
	
	while($row = $db->fetchArray($rs)) {
		
		if($item_limit <= $item_count){
			break;
		}
		
		// check community permission
		if($row['cflag']==3 && !$xoopsUserIsAdmin && $row['cadmin']!=$own_uid && $row['csubadmin']!=$own_uid){
			if($own_uid < 0){
				continue;
			}
			$cid = intval($row['cid']);
			if(!isset($perm_arr[$cid])){
				$perm_arr[$cid] = xsns_is_community_member($mydirname, $cid, $own_uid);
			}
			if(!$perm_arr[$cid]){
				continue;
			}
		}
		
		$date_arr = explode(' ', XsnsUtils::getUserDatetime($row['max_r_datetime']), 2);
		if(!is_array($date_arr)){
			continue;
		}
		if($today==$date_arr[0]){
			$r_time_arr = explode(':', $date_arr[1], 3);
			if(!is_array($r_time_arr)){
				continue;
			}
			$r_time = $r_time_arr[0].':'.$r_time_arr[1];
		}
		else{
			$r_time_arr = explode('-', $date_arr[0], 3);
			if(!is_array($r_time_arr)){
				continue;
			}
			$r_time = $r_time_arr[1]. constant($constpref.'_MONTH'). $r_time_arr[2]. constant($constpref.'_DAY');
		}
		
		$block['topic_list'][] = array(
			'link' => XOOPS_URL.'/modules/'.$mydirname.'/?p=topic&tid='.intval($row['tid']),
			'title' => $myts->htmlSpecialChars($row['tname']),
			'body' => $myts->htmlSpecialChars($row['tcbody']),
			'comment_count' => intval($row['comment_count']),
			'datetime' => $r_time,
			'time' => XsnsUtils::getUserTimestamp($row['max_r_datetime']),
			'uid' => intval($row['tcuid']),
			'community' => array(
				'link' => XOOPS_URL.'/modules/'.$mydirname.'/?cid='.intval($row['cid']),
				'title' => $myts->htmlSpecialChars($row['cname']),
			),
		);
		
		$item_count++;
	}
	
	if(empty($options['disable_renderer'])){
		require_once XOOPS_ROOT_PATH.'/class/template.php' ;
		$tpl =& new XoopsTpl();
		$tpl->assign('block', $block);
		$ret['content'] = $tpl->fetch('db:'.$mydirname.'_block_recent_topic.html');
		return $ret;
	}
	else{
		return $block;
	}
}
//------------------------------------------------------------------------------

function b_xsns_recent_topic_edit($options)
{
	$mydirname = empty($options[0]) ? 'xsns' : $options[0];
	$item_limit = empty($options[1]) ? 5 : intval($options[1]);
	
	if( preg_match( '/[^0-9a-zA-Z_-]/' , $mydirname ) ) die( 'Invalid mydirname' ) ;
	
	require_once XOOPS_ROOT_PATH.'/class/template.php' ;
	$tpl =& new XoopsTpl() ;
	$tpl->assign(array(
		'mydirname' => $mydirname,
		'item_limit' => $item_limit,
	));
	return $tpl->fetch( 'db:'.$mydirname.'_block_recent_topic_edit.html' ) ;
}
//------------------------------------------------------------------------------

function b_xsns_information_show($options)
{
	global $xoopsUser, $xoopsUserIsAdmin;
	if(!is_object($xoopsUser)){
		return array();
	}
	
	require_once dirname(dirname(__FILE__)).'/include/common_functions.php';
	
	$db =& Database::getInstance();
	$myts =& MyTextSanitizer::getInstance();
	
	$mydirname = empty($options[0]) ? 'xsns' : $options[0];
	
	if( preg_match( '/[^0-9a-zA-Z_-]/' , $mydirname ) ) die( 'Invalid dirname' ) ;
	
	$constpref = '_MB_'.strtoupper($mydirname);
	
	$block = array();
	
	$sql = "SELECT mode,COUNT(*),MAX(r_datetime) AS max_r_datetime".
			" FROM ".$db->prefix($mydirname.'_c_commu_confirm').
			" WHERE uid_to='".$xoopsUser->getVar('uid')."'".
			" GROUP BY mode".
			" ORDER BY max_r_datetime DESC";
	$rs = $db->query($sql);
	if(!$rs || $db->getRowsNum($rs) < 1){
		return array();
	}
	
	require_once dirname(dirname(__FILE__)).'/userlib/utils.php';
	
	while($row = $db->fetchArray($rs)){
		$mode = intval($row['mode']);
		if(defined($constpref.'_INDEX_INFO_MSG_'.$mode)){
			$block['info_list'][] = array(
				'link' => XOOPS_URL.'/modules/'.$mydirname.'/?p=mypage&act=confirm#mode'.$mode,
				'title' => sprintf(constant($constpref.'_INDEX_INFO_MSG_'.$mode), $row['COUNT(*)']),
				'time' => XsnsUtils::getUserTimestamp($row['max_r_datetime']),
			);
		}
	}
	
	$tpl =& new XoopsTpl();
	$tpl->assign('block', $block);
	
	$ret = array();
	$ret['content'] = $tpl->fetch('db:'.$mydirname.'_block_information.html');
	return $ret;
}
//------------------------------------------------------------------------------

function b_xsns_information_edit($options)
{
	$mydirname = empty($options[0]) ? 'xsns' : $options[0];
	
	if( preg_match( '/[^0-9a-zA-Z_-]/' , $mydirname ) ) die( 'Invalid mydirname' ) ;
	
	require_once XOOPS_ROOT_PATH.'/class/template.php' ;
	$tpl =& new XoopsTpl() ;
	$tpl->assign(array(
		'mydirname' => $mydirname,
	));
	return $tpl->fetch( 'db:'.$mydirname.'_block_information_edit.html' ) ;
}
//------------------------------------------------------------------------------

?>