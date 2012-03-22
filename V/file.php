<?php
$clean = true;
require_once dirname(__file__)."/security.php";

$_REQUEST['module']; // == "matrix"
if (file_exists(dirname(__file__)."/controller/".addslashes($_REQUEST['module'])."/".addslashes($_REQUEST['module']).".controller.php")) {
    include_once dirname(__file__)."/controller/".addslashes($_REQUEST['module'])."/".addslashes($_REQUEST['module']).".controller.php";
} else {
    include_once dirname(__file__)."/plugins/".addslashes($_REQUEST['module'])."/".addslashes($_REQUEST['module']).".controller.php";
}

//Sicherheitsabfrage, um zu schauen, dass es sich bei Type auch wirklich um eine derartige Klasse handelt:
$class_vars = get_class_vars($_REQUEST['type']);
if ($class_vars === false) {
    print "Klasse ". $_REQUEST['type'] . " konnte nicht gefunden werden.";
} else {
    $class_spec = array_keys($class_vars);
    if (in_array("table", $class_spec) && in_array("content_field", $class_spec)) {
        $file = new $_REQUEST['type']($_REQUEST['file_id']);
        $file->deliver();
    } else {
        print "Klasse hat nicht den korrekten Typ.";
    }
}


