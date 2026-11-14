<?php

header('Content-Type: application/json; charset=UTF-8');
require_once dirname(__file__)."/security.php";

$new_things = array();

foreach($modules as $mod) {
    $modname = get_class($mod);
    $new_things[$modname] = array();
    
    $data = $mod->sendData();
    if ($data !== null) {
        $new_things[$modname]['data'] = $data;
    }
    
    if (method_exists($mod, "getTitle")) {
        $new_things[$modname]['title'] = $mod->getTitle();
    }
}

//if this does not work, we may have utf8+bom encoded files
print json_encode($new_things);
