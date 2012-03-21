<?php

class haushalt extends ModuleController {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * sets the navigation for the user in the "bridge" or second navigation
     * @return: array('actionname1' => $clearName, 'actionname2' => $clearName, ...)
     */
    public function getNavigation() {
        $nav = array(
            'haushalt' => array('title' => "Haushalt", 'open' => true),
            'bestand' => array('title' => "Bestand", 'open' => false)
        );
        if (count($GLOBALS['masterof'])) {
            $nav['control'] = array('title' => "Verwaltung", 'open' => false);
        }
        return $nav;
    }
    
    public function getTitle() {
        return "Haushalt";
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                actions                                 // 
    ////////////////////////////////////////////////////////////////////////////
    
    public function action_haushalt() {
        print "overview";
    }
    
    public function action_bestand() {
        print Template::summon(dirname(__file__)."/views/details.view.php")
                ->render();
        
    }
    
    public function action_control() {
        if (!count($GLOBALS['masterof'])) {
            echo "Kein Zugriff";
        }
        global $login;
        $db = DBManager::get();
        $group_year = $db->query(
            "SELECT year.gruppe, MAX(year.zeit) as zeit, MAX(year.jahreszahl) as jahreszahl, MIN(year.jahreszahl) AS startjahr " .
            "FROM year " .
            "INNER JOIN relate_f_gr ON (relate_f_gr.gruppe_id = year.gruppe) " .
                "INNER JOIN relate_sp_f ON (relate_sp_f.force_id = relate_f_gr.force_id) " .
            "WHERE relate_sp_f.spieler = ".$db->quote($login)." " .
            "GROUP BY year.gruppe " .
            "ORDER BY jahreszahl DESC" .
        "")->fetch(PDO::FETCH_ASSOC);
        $jahr = $group_year['jahreszahl'];
        $colonies = array();
        foreach ($GLOBALS['masterof'] as $group) {
            $colonies[$group] = $db->query(
                "SELECT * " .
                "FROM hh_colonies " .
                "WHERE gruppe_id = ".$db->quote($group)." " .
                        "AND year = ".$db->quote($jahr)." " .
                "ORDER BY name ASC, force_id " .
            "")->fetchAll(PDO::FETCH_ASSOC);
        }
        print Template::summon(dirname(__file__)."/views/control.php")
                ->with("jahr", $jahr)
                ->with("colonies", $colonies)
                ->render();
    }
	
    public function action_edit_ip() {
        if (!count($GLOBALS['masterof']) OR !$masterof->has($_REQUEST['gruppe_id'])) {
                echo "Kein Zugriff";
        }
    }
}
