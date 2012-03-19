<?php

require_once dirname(__file__)."/Items.class.php";
require_once dirname(__file__)."/DBManager.class.php";

class Forces extends Items {
  
    static protected $cachedNames = array();
  
    function __construct() {
        parent::__construct();
    }

    static function playersForces($spieler) {
        $db = DBManager::get();
        $pF = new Forces();
        $result = $db->query("SELECT DISTINCT force_id " .
            "FROM relate_sp_f " .
            "WHERE spieler = ".$db->quote($spieler))->fetchAll();
        foreach($result as $key => $row) {
            $pF[$key] = $row['force_id'];
        }
        return $pF;
    }
  
    static function id2name($id) {
        return Cache::getCached(
            "SELECT name FROM forces WHERE id = :id ",
            array('id' => $id)
        );
    }
  
    static function forcespicture($force_id) {
        return Cache::getCachedFetch(
            "SELECT b.id, b.filename " .
            "FROM forces AS f INNER JOIN bilder AS b ON (b.id = f.picture) " .
            "WHERE f.id = :id " ,
            array('id' => $force_id)
        );
    }
  
    static function forcesGroup($force_id) {
        return Cache::getCached(
            "SELECT gruppe_id FROM relate_f_gr WHERE force_id = :id ",
            array('id' => $force_id)
        );
    }
}
