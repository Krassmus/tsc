<?php

header("Content-Type: text/html; charset=UTF-8");
require_once dirname(__file__)."/security.php";

$moduleController = array();
foreach ($modules as $mod) {
    if ($mod instanceof ModuleController) {
        $moduleController[] = $mod;
    }
}

//View angehen:
print Template::summon(dirname(__file__)."/views/layout.php")
        ->with('modules', $moduleController)
        ->with('width', $stil['width'])
        ->with('stil', $stil)
        ->render();
        

