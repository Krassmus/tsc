<?php

require_once dirname(__file__)."/Items.class.php";
require_once dirname(__file__)."/DBManager.class.php";

class Groups extends Items {
    static protected $cachedNames = array();
    
    function __construct() {
        parent::__construct();
    }
  
    static function playersGroups($spieler) {
  	$db = DBManager::get();
        $pG = new Forces();
        $result = $db->query("SELECT DISTINCT g.gruppe_id " .
          "FROM relate_f_gr AS g " .
              "INNER JOIN relate_sp_f AS f ON (f.force_id = g.force_id) " .
          "WHERE f.spieler = ".$db->quote($spieler))->fetchAll();
        foreach($result as $key => $row) {
            $pG[$key] = $row['gruppe_id'];
        }
        return $pG;
    }
  
    static function mastersGroups($spieler) {
        $db = DBManager::get();
        $pG = new Forces();
        $result = $db->query("SELECT DISTINCT m.gruppe_id " .
            "FROM relate_master_gr AS m " .
            "WHERE m.spieler = ".$db->quote($spieler))->fetchAll();
        foreach($result as $key => $row) {
            $pG[$key] = $row['gruppe_id'];
        }
        return $pG;
    }
  
    static function id2name($id) {
        return Cache::getCached("SELECT name FROM gruppe WHERE id = :id ", array('id' => $id));
    }
}
