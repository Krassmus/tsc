<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ModuleLoader {

    static protected $loaded = array();

    static public function getModules() {
        $module_data = self::getModuleData();

        //Laden des Programmcodes:
        foreach ($module_data as $mod) {
            $modpath = dirname(__file__)."/../".
                $mod['type']."/".
                $mod['modulename']."/".
                $mod['modulename'].".controller.php";
            if (!self::$loaded[$mod['type']][$mod['modulename']] && file_exists($modpath)) {
                include $modpath;
                self::$loaded[$mod['type']][$mod['modulename']] = true;
            }
        }
        $got_bureau = self::$loaded['controller']['bureau'];
        $got_abmelden = self::$loaded['controller']['abmelden'];
        if (!$got_bureau) {
            include dirname(__file__)."/../controller/bureau/bureau.controller.php";
        }
        if (!$got_abmelden) {
            include dirname(__file__)."/../controller/abmelden/abmelden.controller.php";
        }

        //Einbinden der Controller:
        $controller = array();
        foreach ($module_data as $mod) {
            if (self::$loaded[$mod['type']][$mod['modulename']]) {
                $controller[] = new $mod['modulename']();
            }
        }
        if (!$got_bureau) {
            $controller[] = new bureau();
        }
        if (!$got_abmelden) {
            $controller[] = new abmelden();
        }
        return $controller;
    }

    static public function getModuleData($gruppe_id = null) {
        global $gruppe;
        $gruppen = $gruppe_id ? array($gruppe_id) : $gruppe;
        $db = DBManager::get();
        $query = "SELECT modulename, type, position FROM module_groups WHERE gruppe_id IN (";
        foreach ($gruppen as $key => $g) {
            $key < 1 OR $query .= ",";
            $query .= $db->quote($g);
        }
        $query .= ") GROUP BY modulename, type ";
        $query .= "ORDER BY position ASC ";
        return $db->query($query)->fetchAll();
    }

    static public function seeController() {
        $controller_handle = opendir(dirname(__file__)."/../controller");
        $controller = array();
        while (false !== ($file = readdir($controller_handle))) {
            if (strpos($file, ".") === false
                    && file_exists(dirname(__file__)."/../controller/".$file."/".$file.".controller.php")) {
                $controller[] = $file;
            }
        }
        return $controller;
    }

    static public function seePlugins() {
        $controller_handle = opendir(dirname(__file__)."/../plugins");
        $controller = array();
        while (false !== ($file = readdir($controller_handle))) {
            if (strpos($file, ".") === false
                    && file_exists(dirname(__file__)."/../plugins/".$file."/".$file.".controller.php")) {
                $controller[] = $file;
            }
        }
        return $controller;
    }

}
