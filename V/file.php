<?php
$clean = true;
require_once dirname(__file__)."/security.php";

//Nur einfache Modulnamen zulassen (kein "../" etc.):
$module = isset($_REQUEST['module']) && is_string($_REQUEST['module']) ? $_REQUEST['module'] : '';
$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : '';
if (!preg_match('/^\w+$/', $module) || !preg_match('/^\w+$/', $type)) {
    header("HTTP/1.1 400 Bad Request");
    die("Ungueltige Parameter.");
}

if (file_exists(dirname(__file__)."/controller/".$module."/".$module.".controller.php")) {
    include_once dirname(__file__)."/controller/".$module."/".$module.".controller.php";
} elseif (file_exists(dirname(__file__)."/plugins/".$module."/".$module.".controller.php")) {
    include_once dirname(__file__)."/plugins/".$module."/".$module.".controller.php";
} else {
    header("HTTP/1.1 404 Not Found");
    die("Modul nicht gefunden.");
}

//Sicherheitsabfrage, um zu schauen, dass es sich bei Type auch wirklich um eine derartige Klasse handelt:
if (!class_exists($type, false) || !is_subclass_of($type, 'DBFile')) {
    header("HTTP/1.1 404 Not Found");
    die("Klasse ". htmlspecialchars($type) . " konnte nicht gefunden werden.");
}
$file = new $type($_REQUEST['file_id'] ?? null);
$file->deliver();
