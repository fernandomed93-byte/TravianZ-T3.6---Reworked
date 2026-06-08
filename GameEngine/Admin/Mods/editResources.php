<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       editResources.php                                           ##
##  Developed by:  aggenkeech                                                  ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2025. All rights reserved.                ##
##                                                                             ##
#################################################################################
use App\Entity\User;

// go max 5 levels up - we don't have folders that go deeper than that
$autoprefix = '';
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix.'autoloader.php')) {
        // we have our path, let's leave
        break;
    }
}
include_once("../../session.php");
include_once($autoprefix."GameEngine/config.php");
include_once($autoprefix."GameEngine/Database.php");

if(!isset($_SESSION)) session_start();

if (!isset($_SESSION['admin_username'])) {
    die("Access Denied: You are not logged in!");
}

if(!isset($_SESSION['access'])) die("Access Denied: You are not Admin!");

if(isset($_SESSION['access']) && $_SESSION['access'] < 9) die("Access Denied: You are not Admin!");


$session = (int) $_POST['admid'];
$id = (int) $_POST['did'];

$sql = mysqli_query($GLOBALS["link"], "SELECT * FROM ".TB_PREFIX."users WHERE id = ".$session."");
$access = mysqli_fetch_array($sql);
$sessionaccess = $access['access'];

if($sessionaccess != 9) die("<h1><font color=\"red\">Access Denied: You are not Admin!</font></h1>");

mysqli_query($GLOBALS["link"], "UPDATE ".TB_PREFIX."vdata SET 
	wood  = '".(int) $_POST['wood']."', 
	clay  = '".(int) $_POST['clay']."', 
	iron  = '".(int) $_POST['iron']."', 
	crop  = '".(int) $_POST['crop']."', 
	maxstore  = '".(int) $_POST['maxstore']."', 
	maxcrop   = '".(int) $_POST['maxcrop']."' 
	WHERE wref = '".$id."'") or die(mysqli_error($database->dblink));

header("Location: ../../../Admin/admin.php?p=village&did=".$id."");
?>