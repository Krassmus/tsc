<?php

require_once dirname(__file__)."/lib/DBManager.class.php";
require_once dirname(__file__)."/lib/Cache.class.php";
require_once dirname(__file__)."/lib/Text.class.php";
require_once dirname(__file__)."/lib/Forces.class.php";
require_once dirname(__file__)."/lib/Groups.class.php";
require_once dirname(__file__)."/lib/Template.class.php";
require_once dirname(__file__)."/lib/FileInclude.class.php";
require_once dirname(__file__)."/lib/Localization.php";

FileInclude::init();

require_once dirname(__file__)."/lib/ModuleGroupController.class.php";
require_once dirname(__file__)."/lib/ModuleLoader.class.php";

Template::setRootPath(dirname(__file__));

// CANONICAL_RELATIVE_PATH_STUDIP should end with a '/'
$CANONICAL_RELATIVE_PATH = dirname($_SERVER['PHP_SELF']);
if (substr($CANONICAL_RELATIVE_PATH,-1) != "/"){
    //$CANONICAL_RELATIVE_PATH .= "/";
}
// ABSOLUTE_URI_STUDIP: insert the absolute URL to your Stud.IP installation; it should end with a '/'
$ABSOLUTE_URI = "";
// automagically compute ABSOLUTE_URI_STUDIP if $_SERVER['SERVER_NAME'] is set
if (isset($_SERVER['SERVER_NAME'])) {
    // work around possible bug in lighttpd
    if (strpos($_SERVER['SERVER_NAME'], ':') !== false) {
        list($_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT']) =
            explode(':', $_SERVER['SERVER_NAME']);
    }
    $ABSOLUTE_URI = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    $ABSOLUTE_URI .= '://'.$_SERVER['SERVER_NAME'];
    if ($_SERVER['HTTPS'] == 'on' && $_SERVER['SERVER_PORT'] != 443 ||
        $_SERVER['HTTPS'] != 'on' && $_SERVER['SERVER_PORT'] != 80) {
        $ABSOLUTE_URI .= ':'.$_SERVER['SERVER_PORT'];
    }
    $ABSOLUTE_URI .= $CANONICAL_RELATIVE_PATH;
}

$sec = true;
session_start();
$db = DBManager::get();


if ($_REQUEST['login']) {
    $login = $_SESSION['login'] = $_REQUEST['login'];
} else {
    $login = $_SESSION['login'];
}
if ($_REQUEST['pass']) {
    $pass = $_SESSION['pass'] = sha1($salt1.$_REQUEST['pass'].$salt2);
} else {
    $pass = $_SESSION['pass'];
}

#Zeichensatz von PHP und der Datenbank einstellen
$db->query("SET NAMES utf8");
setlocale(LC_TIME, 'de_DE.UTF8');

#Ueberpruefen der Zugangsdaten
$result = $db->query("SELECT password " .
    "FROM spieler " .
    "WHERE login = ".$db->quote($login)." " .
        "AND locked != '1' "
    )->fetch();
if (!$result)
  //Falscher Login.
  header("Location: ../index.php?fehler=1");
if ($result['password'] != $pass)
  //  die("Falsches Passwort.");
  header("Location: ../index.php?fehler=2");

#Initialisieren der Variablen $force, $gruppe und $masterof als Arrays
$force = Forces::playersForces($login);
$gruppe = Groups::playersGroups($login);
$masterof = Groups::mastersGroups($login);


function force2name($number) {
    return Forces::id2name($number);
}
function gruppe2name($number) {
    return Groups::id2name($number);
}
function picid2filename($number) {
    $db = DBManager::get();
    $result = $db->query("SELECT filename FROM bilder WHERE id = ".$db->quote($number))->fetch();
    return $result['filename'];
}


$stil = $db->query("SELECT headercolor, headerfont, font, headerfontsize, fontsize, backgroundimage, titlealert, width " .
    "FROM spieler " .
    "WHERE login = ".$db->quote($login))->fetch();

    
//initialisieren der Module
$modules = $clean ? array() : ModuleLoader::getModules();

$GLOBALS['user_language'] = "de";