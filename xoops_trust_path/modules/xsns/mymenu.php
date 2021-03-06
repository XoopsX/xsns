<?php

// Skip for ORETEKI XOOPS
if( defined( 'XOOPS_ORETEKI' ) ) return ;

global $xoopsModule ;
if( ! is_object( $xoopsModule ) ) die( '$xoopsModule is not set' )  ;

// language files (modinfo.php)
$language = empty( $xoopsConfig['language'] ) ? 'english' : $xoopsConfig['language'] ;
if( file_exists( "$mydirpath/language/$language/modinfo.php" ) ) {
	// user customized language file
	include_once "$mydirpath/language/$language/modinfo.php" ;
} else if( file_exists( "$mytrustdirpath/language/$language/modinfo.php" ) ) {
	// default language file
	include_once "$mytrustdirpath/language/$language/modinfo.php" ;
} else {
	// fallback english
	include_once "$mytrustdirpath/language/english/modinfo.php" ;
}
include $mytrustdirpath.'/admin_menu.php' ;

if( file_exists( XOOPS_TRUST_PATH.'/libs/altsys/mytplsadmin.php' ) ) {
	// mytplsadmin (TODO check if this module has tplfile)
	$title = defined( '_AM_MENU_MYTPLSADMIN' ) ? _AM_MENU_MYTPLSADMIN : 'tplsadmin' ;
	array_push( $adminmenu , array( 'title' => $title , 'link' => 'admin/index.php?mode=admin&lib=altsys&page=mytplsadmin' ) ) ;
}

if( file_exists( XOOPS_TRUST_PATH.'/libs/altsys/myblocksadmin.php' ) ) {
	// myblocksadmin
	$title = defined( '_AM_MENU_MYBLOCKSADMIN' ) ? _AM_MENU_MYBLOCKSADMIN : 'blocksadmin' ;
	array_push( $adminmenu , array( 'title' => $title , 'link' => 'admin/index.php?mode=admin&lib=altsys&page=myblocksadmin' ) ) ;
}

if( file_exists( XOOPS_TRUST_PATH.'/libs/altsys/mylangadmin.php' ) ) {
	// mylangadmin
	$title = defined( '_AM_MENU_MYLANGADMIN' ) ? _AM_MENU_MYLANGADMIN : 'langadmin' ;
	array_push( $adminmenu , array( 'title' => $title , 'link' => 'admin/index.php?mode=admin&lib=altsys&page=mylangadmin&dirname='.$mydirname ) ) ;
}

// preferences
$config_handler =& xoops_gethandler('config');
if( count( $config_handler->getConfigs( new Criteria( 'conf_modid' , $xoopsModule->mid() ) ) ) > 0 ) {
	if( file_exists( XOOPS_TRUST_PATH.'/libs/altsys/mypreferences.php' ) ) {
		// mypreferences
		$title = defined( '_AM_MENU_MYPREFERENCES' ) ? _AM_MENU_MYPREFERENCES : _PREFERENCES ;
		array_push( $adminmenu , array( 'title' => $title , 'link' => 'admin/index.php?mode=admin&lib=altsys&page=mypreferences' ) ) ;
	} else {
		// system->preferences
		$url = defined('XOOPS_CUBE_LEGACY') ? 
				XOOPS_URL.'/modules/legacy/admin/index.php?action=PreferenceEdit&confmod_id='.$xoopsModule->mid() :
				XOOPS_URL.'/modules/system/admin.php?fct=preferences&op=showmod&mod='.$xoopsModule->mid();
		array_push( $adminmenu , array( 'title' => _PREFERENCES , 'link' => $url ) ) ;
	}
}

$mymenu_uri = empty( $mymenu_fake_uri ) ? $_SERVER['REQUEST_URI'] : $mymenu_fake_uri ;
$mymenu_link = substr( strstr( $mymenu_uri , '/admin/' ) , 1 ) ;



// highlight (you can customize the colors)
foreach( array_keys( $adminmenu ) as $i ) {
	if( $mymenu_link == $adminmenu[$i]['link'] ) {
		$adminmenu[$i]['color'] = '#FFCCCC' ;
		$adminmenu_hilighted = true ;
		$GLOBALS['altsysAdminPageTitle'] = $adminmenu[$i]['title'] ;
	} else {
		$adminmenu[$i]['color'] = '#DDDDDD' ;
	}
}
if( empty( $adminmenu_hilighted ) ) {
	foreach( array_keys( $adminmenu ) as $i ) {
		if( stristr( $mymenu_uri , $adminmenu[$i]['link'] ) ) {
			$adminmenu[$i]['color'] = '#FFCCCC' ;
			$GLOBALS['altsysAdminPageTitle'] = $adminmenu[$i]['title'] ;
			break ;
		}
	}
}

// link conversion from relative to absolute
foreach( array_keys( $adminmenu ) as $i ) {
	if( stristr( $adminmenu[$i]['link'] , XOOPS_URL ) === false ) {
		$adminmenu[$i]['link'] = XOOPS_URL."/modules/$mydirname/" . $adminmenu[$i]['link'] ;
	}
}

// display (you can customize htmls)
echo "<div style='text-align:left;width:98%;'>" ;
foreach( $adminmenu as $menuitem ) {
	echo "<div style='float:left;height:1.5em;'><nobr><a href='".htmlspecialchars($menuitem['link'],ENT_QUOTES)."' style='background-color:{$menuitem['color']};font:normal normal bold 9pt/12pt;'>".htmlspecialchars($menuitem['title'],ENT_QUOTES)."</a> | </nobr></div>\n" ;
}
echo "</div>\n<hr style='clear:left;display:block;' />\n" ;

?>