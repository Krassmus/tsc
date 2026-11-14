<?php

require_once dirname(__file__)."/security.php";

//besondere Fehlerbehandlung für AJAX:
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    ob_end_clean();
    $display_error = (bool)($errno & ini_get('error_reporting') );
    if ($display_error) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}
//set_error_handler("exception_error_handler");

$controller = $_REQUEST['controller'] ?? '';
$action = $_REQUEST['action'] ?? '';

//nur geladene Modul-Controller duerfen instanziiert werden (keine beliebigen Klassen!)
if (!is_string($controller) || !class_exists($controller, false) || !is_subclass_of($controller, 'Module')) {
    header("HTTP/1.1 404 Not Found");
    die("Modul nicht gefunden.");
}
$mod = new $controller();

//View angehen:
try {
    print $mod->activateAction($action);
} catch (Exception $ex) {
    header("HTTP/1.1 500 Internal Server Error");
    header("Status: 500 Internal Server Error");
    print Template::summon(dirname(__file__)."/views/exception_text.php")
            ->with("exception", $ex)
            ->render();
}
