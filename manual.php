<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       manual.php                                                  ##
##  Developed by:  Dzoki                                                       ##
##  License:       TravianX Project                                            ##
##  Copyright:     TravianX (c) 2010-2011. All rights reserved.                ##
##                                                                             ##
#################################################################################

include_once("GameEngine/config.php");
include_once("GameEngine/Lang/" . LANG . ".php");
include_once("GameEngine/Data/unitdata.php");
include_once("GameEngine/Data/buidata.php");
?>

<html>
	<head>
	<title><?php echo SERVER_NAME; ?> - Manual</title>
		<link rel="shortcut icon" href="favicon.ico"/>
	<meta name="content-language" content="en" />
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script src="mt-core.js?0faab" type="text/javascript"></script>
	<script src="mt-more.js?0faab" type="text/javascript"></script>
	<script src="unx.js?f4b7h" type="text/javascript"></script>
	<script src="new.js?0faab" type="text/javascript"></script>
	<link href="<?php echo GP_LOCATE; ?>lang/en/compact.css?f4b7i" rel="stylesheet" type="text/css" />
	<link href="<?php echo GP_LOCATE; ?>lang/en/lang.css?f4b7d" rel="stylesheet" type="text/css" />
	<link href="<?php echo GP_LOCATE; ?>travian.css?f4b7d" rel="stylesheet" type="text/css" />
		<link href="<?php echo GP_LOCATE; ?>lang/en/lang.css" rel="stylesheet" type="text/css" />
	   </head>
	<body class="manual">
<?php

if (isset($_GET['s']) && !ctype_digit($_GET['s'])) {
	$_GET['s'] = "0";
}
if (isset($_GET['typ']) && !ctype_digit($_GET['typ'])) {
	$_GET['typ'] = null;
}
if(!isset($_GET['typ']) && !isset($_GET['s'])) {
	include("Templates/Manual/00.tpl");
}
else if (!isset($_GET['typ']) && $_GET['s'] == 1) {
	include("Templates/Manual/00.tpl");
}
else if (!isset($_GET['typ']) && $_GET['s'] == 2) {
	include("Templates/Manual/direct.tpl");
}
else if (isset($_GET['typ']) && $_GET['typ'] == 5 && $_GET['s'] == 3) {
	include("Templates/Manual/medal.tpl");
}
else {
	if(isset($_GET['gid'])) {
		$gid = preg_replace("/[^a-zA-Z0-9_-]/", "", $_GET['gid']);
		if ($_GET['typ'] == 4 && ctype_digit($gid)) {
			$gid = str_pad($gid, 2, '0', STR_PAD_LEFT);
		}
		include("Templates/Manual/".$_GET['typ'].$gid.".tpl");
	}
	else {
		if($_GET['typ'] == 4 && $_GET['s'] == 0) {
			$_GET['s'] = 1;
		}
		$s = isset($_GET['s']) ? preg_replace("/[^a-zA-Z0-9_-]/", "", $_GET['s']) : '';
		if ($_GET['typ'] == 1 && ctype_digit($s)) {
			$s = str_pad($s, 2, '0', STR_PAD_LEFT);
		}else if ($_GET['typ'] == 4 && ctype_digit($s)) {
			$s = str_pad($s, 2, '0', STR_PAD_LEFT);
		}
		include("Templates/Manual/".$_GET['typ'].$s.".tpl");
	}
}
?>
</body>

</html>