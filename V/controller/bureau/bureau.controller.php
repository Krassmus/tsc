<?php


class bureau extends ModuleGroupController {
    
    public function __construct() {
        FileInclude::JS("bureau_js", "bureau.js", $this);
    }
    
    /**
     * sets the navigation for the user in the "bridge" or second navigation
     * @return: array('actionname1' => $clearName, 'actionname2' => $clearName, ...)
     */
    public function getNavigation() {
        global $masterof;
        $nav = array(
            'notes' => array('title' => "Arbeitsplatz"),
            'settings' => array('title' => "Einstellungen")
        );
        if (count($masterof)) {
            $nav['admin'] = array('title' => "Verwalten");
        }
        return $nav;
    }
    
    public function getTitle() {
        return "Büro";
    }

    protected function framedActions() {
        return array("admin");
    }
    
    //gibt alle Gruppen, von denen man Meister ist wieder
    protected function getGroup() {
        global $masterof;
        $ret = array();
        foreach ($masterof as $gruppe_id) {
            $ret[] = array('id' => $gruppe_id, 'name' => Groups::id2name($gruppe_id));
        }
        return $ret;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //                                actions                                 // 
    ////////////////////////////////////////////////////////////////////////////
    
    /**
     * Der Notizzettel
     */
    public function action_notes() {
        global $db, $login;
        $notiz = $db->query("SELECT notiz " .
                    "FROM spieler " .
                    "WHERE login = ".$db->quote($login)
                )->fetch(PDO::FETCH_COLUMN, 0);
        print Template::summon(dirname(__file__)."/views/notes.view.php")
                ->with('notiz', $notiz)
                ->render();
    }
    
    /**
     * Die Einstellungsseite
     */
    public function action_settings() {
        global $db, $login, $stil, $force;
        foreach ($force as $f) {
            $bilder[$f] = $db->query("SELECT DISTINCT b.id, b.filename " .
                "FROM bilder AS b " .
                    "JOIN relate_f_gr AS g ON (b.matrix = g.gruppe_id) " .
                    "JOIN relate_sp_f AS f ON (g.force_id = f.force_id) " .
                "WHERE f.spieler = ".$db->quote($login)." " .
                    "AND (SELECT count(*) " .
                        "FROM forces " .
                        "WHERE forces.picture = b.id " .
                            "AND forces.id != ".$db->quote($f).") = 0 " .
                "ORDER BY b.id DESC")->fetchAll();
        }
        $allebilder = $db->query(
            "SELECT DISTINCT b.id, b.filename " .
            "FROM bilder AS b, relate_sp_f AS f, relate_f_gr AS g " .
            "WHERE b.matrix = g.gruppe_id " .
                "AND g.force_id = f.force_id " .
                "AND f.spieler = ".$db->quote($login)." " .
            "ORDER BY b.id DESC"
        )->fetchAll() OR $allebilder = array();

        //expects i.e.
        //  array(array("Sound-Einstellung", "<input type=\"checkbox\" onChange=\"alert('yes!')\">"))
        //to be returned by the registered function
        $plugin_settings = $this->fire("plugin_settings", "array");
        
        print Template::summon(dirname(__file__)."/views/settings.view.php")
                ->with("bilder", $bilder)
                ->with("allebilder", $allebilder)
                ->with("stil", $stil)
                ->with("plugin_settings", $plugin_settings)
                ->render();
    }
    
    
    /**
     * �ndert die Einstellungen und den Notizzettel
     */
    public function action_set() {
        global $db, $login, $salt1, $salt2;
        if (in_array($_REQUEST['typus'], array("headerfontsize", "fontsize", "font", "headerfont", "notiz", "headercolor", "backgroundimage", "width"))) {
            $db->query("UPDATE spieler " .
                       "SET ".$_REQUEST['typus']." = ".$db->quote($_REQUEST['val'])." " .
                       "WHERE login = ".$db->quote($login));
        }
        if ($_REQUEST['typus'] === "password") {
            $pass = sha1($salt1.$_REQUEST['val'].$salt2);
            $db->query("UPDATE spieler " .
                       "SET password = ".$db->quote($pass)." " .
                       "WHERE login = ".$db->quote($login));
            $_SESSION['pass'] = $pass;
        }
        if ($_REQUEST['typus'] === "titlealert") {
            $db->query("UPDATE spieler " .
                       "SET ".$_REQUEST['typus']." = ".$db->quote($_REQUEST['val'] === "false" ? "0" : "1")." " .
                       "WHERE login = ".$db->quote($login));
        }
    }

    public function action_set_force_picture() {
        global $force;
        if ($_REQUEST['force_id'] && $force->has($_REQUEST['force_id']) && $_REQUEST['picture_id']) {
            $db = DBManager::get();
            $db->exec(
                "UPDATE forces SET picture = ".$db->quote($_REQUEST['picture_id'])." WHERE id = ".$db->quote($_REQUEST['force_id'])." " .
            "");
        }
    }
    
    public function action_admin() {
        global $masterof;
        $db = DBManager::get();
        $maechte = $jahr = $infos = $statistic = $activated_modules = array();
        foreach ($masterof as $gruppe) {
            $jahr[$gruppe] = $db->query("SELECT y.jahreszahl " .
                "FROM gruppe AS g, year AS y " .
                "WHERE g.id = y.gruppe AND g.id=".$db->quote($gruppe)." " .
                "ORDER BY y.zeit DESC LIMIT 1")->fetch(PDO::FETCH_COLUMN, 0);
            $maechte[$gruppe] = $db->query(
                "SELECT f.id, f.name " .
                "FROM forces AS f, relate_f_gr AS rgr " .
                "WHERE rgr.force_id = f.id " .
                    "AND rgr.gruppe_id = ".$db->quote($gruppe)." " .
            "")->fetchAll();
            foreach ($maechte[$gruppe] as $key => $macht) {
                $maechte[$gruppe][$key]['players'] = $db->query("SELECT spieler FROM relate_sp_f WHERE force_id = ".$db->quote($macht['id']))->fetchAll(PDO::FETCH_COLUMN, 0);
            }
            $activated_modules[$gruppe] = ModuleLoader::getModuleData($gruppe);
            foreach ($activated_modules[$gruppe] as $key => $modul) {
                unset($activated_modules[$gruppe][$key]);
                $activated_modules[$gruppe][$modul['modulename']] = $modul;
            }

            //Die folgenden Einträge werden aus anderen Modulen geholt:
            $statistic[$gruppe] = $this->fire("group_statistic", "string", array('matrix' => $this->group));
            
        }
        print Template::summon(dirname(__file__)."/views/admin.view.php")
                ->with("gruppe", $this->group)
                ->with("jahr", $jahr)
                ->with("maechte", $maechte)
                ->with('controller', ModuleLoader::seeController())
                ->with('plugins', ModuleLoader::seePlugins())
                ->with("infos", $infos)
                ->with("statistic", $statistic)
                ->with("activated_modules", $activated_modules)
                ->render();
    }
    
    public function action_admin_show_macht() {
        global $masterof;
        $db = DBManager::get();
        if (!$masterof->has($this->group)) {
            throw Exception("Unbefugter Zutritt!");
        }
        
        //Nachbarn sind die Mitglieder in gemeinsamen Gruppen
        $nachbarn = $db->query(
            "SELECT DISTINCT r2.force_id " .
            "FROM relate_f_gr AS r1 " .
                "INNER JOIN relate_f_gr AS r2 ON (r1.gruppe_id = r2.gruppe_id) " .
            "WHERE r1.force_id = ".$db->quote($_REQUEST['macht'])." " .
                "AND r2.force_id != ".$db->quote($_REQUEST['macht'])." " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        $kontakte = $db->query(
            "SELECT c1.force1 FROM contact AS c1 WHERE force2 = ".$db->quote($_REQUEST['macht'])." " .
            "UNION DISTINCT SELECT c2.force2 FROM contact AS c2 WHERE force1 = ".$db->quote($_REQUEST['macht'])." ")->fetchAll(PDO::FETCH_COLUMN, 0);
        $spieler = $db->query("SELECT spieler FROM relate_sp_f WHERE force_id = ".$db->quote($_REQUEST['macht'])."")->fetchAll(PDO::FETCH_COLUMN, 0);
        $allespieler = $db->query("SELECT DISTINCT sp.spieler  " .
            "FROM relate_sp_f AS sp " .
                "INNER JOIN relate_f_gr AS r1 ON (r1.force_id = sp.force_id) " .
                "INNER JOIN relate_f_gr AS r2 ON (r2.gruppe_id = r1.gruppe_id) " .
            "WHERE r2.force_id = ".$db->quote($_REQUEST['macht'])." " .
            "ORDER BY sp.spieler ASC")->fetchAll(PDO::FETCH_COLUMN, 0);

        print Template::summon(dirname(__file__)."/views/macht.view.php")
                ->with("nachbarn", $nachbarn)
                ->with("kontakte", $kontakte)
                ->with("spieler", $spieler)
                ->with("allespieler", $allespieler)
                ->with("gruppe", $this->group)
                ->with("macht", $_REQUEST['macht'])
                ->render();
    }
    
    public function action_neuer_spieler_und_macht() {
        global $masterof, $salt1, $salt2;
        if (!$masterof->has($this->group)) {
            throw new Exception("Unbefugter Zugriff!");
            return;
        }
        $db = DBManager::get();
        
        $result = $db->query("SELECT * FROM spieler WHERE login = ".$db->quote($_REQUEST['spielername']))->fetch();
        if ($result) {
            throw new Exception("Login-Name des Spielers schon vergeben.");
            return;
        }
        $result = $db->query("SELECT f.id " .
                "FROM forces AS f INNER JOIN relate_f_gr AS r ON (f.id = r.force_id) " .
                "WHERE f.name = ".$db->quote($_REQUEST['machtname'])." " .
                    "AND r.gruppe_id = ".$db->quote($this->group))->fetch();
        if ($result['id']) {
            throw new Exception("So eine Macht gibt es in der Sternengruppe schon.");
            return;
        }
        if (strlen($_REQUEST['spieler']) > 40) {
            throw new Exception("Spielerlogin hat mehr als 40 Zeichen, was zu lang ist.");
            return;
        }
        if (strlen($_REQUEST['macht']) > 40) {
            throw new Exception("Name der neuen Macht hat mehr als 40 Zeichen, was zu lang ist.");
            return;
        }
        //Jetzt kann es los gehen.
        $db->query("INSERT INTO spieler (login, password) " .
            "VALUES (".$db->quote($_REQUEST['spielername']).", ".$db->quote(sha1($salt1.$_REQUEST['spielername'].$salt2)).")");
        
        $db->query("INSERT INTO forces (name) VALUES (".$db->quote($_REQUEST['machtname']).")");
        $force_id = $db->query("SELECT id FROM forces WHERE name = ".$db->quote($_REQUEST['machtname'])." ORDER BY id DESC")->fetch(PDO::FETCH_COLUMN, 0);
        
        $db->query("INSERT INTO relate_sp_f (spieler, force_id) VALUES (".$db->quote($_REQUEST['spielername']).", ".$db->quote($force_id).")");
        $nachbarn = $db->query("SELECT force_id FROM relate_f_gr WHERE gruppe_id = ".$db->quote($this->group))->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach($nachbarn as $nachbar) {
            $db->query("INSERT INTO contact (force1, force2) VALUES (".$db->quote($nachbar).", ".$db->quote($force_id).")");
        }
        $db->query("INSERT INTO relate_f_gr (force_id, gruppe_id) VALUES (".$db->quote($force_id).", ".$db->quote($this->group).")");
        print "Spieler und Macht sind erstellt worden. Einloggen kann der Spieler sich mit seinem Spielernamen und dem Passwort gleich dem Spielernamen.";
    }
    
    public function action_neue_macht() {
        global $masterof;
        if (!$masterof->has($this->group)) {
            throw new Exception("Unbefugter Zugriff!");
            return;
        }
        $db = DBManager::get();
        
        $result = $db->query("SELECT f.id " .
                "FROM forces AS f INNER JOIN relate_f_gr AS r ON (f.id = r.force_id) " .
                "WHERE f.name = ".$db->quote($_REQUEST['machtname'])." " .
                    "AND r.gruppe_id = ".$db->quote($this->group))->fetch();
        if ($result['id']) {
            throw new Exception("So eine Macht gibt es in der Sternengruppe schon.");
            return;
        }
        if (strlen($_REQUEST['spieler']) > 40) {
            throw new Exception("Spielerlogin hat mehr als 40 Zeichen, was zu lang ist.");
            return;
        }
        if (strlen($_REQUEST['macht']) > 40) {
            throw new Exception("Name der neuen Macht hat mehr als 40 Zeichen, was zu lang ist.");
            return;
        }
        //Jetzt kann es los gehen.
        $db->query("INSERT INTO forces (name) VALUES (".$db->quote($_REQUEST['machtname']).")");
        $force_id = $db->query("SELECT id FROM forces WHERE name = ".$db->quote($_REQUEST['machtname'])." ORDER BY id DESC")->fetch(PDO::FETCH_COLUMN, 0);
        
        $nachbarn = $db->query("SELECT force_id FROM relate_f_gr WHERE gruppe_id = ".$db->quote($this->group))->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach($nachbarn as $nachbar) {
            $db->query("INSERT INTO contact (force1, force2) VALUES (".$db->quote($nachbar).", ".$db->quote($force_id).")");
        }
        $db->query("INSERT INTO relate_f_gr (force_id, gruppe_id) VALUES (".$db->quote($force_id).", ".$db->quote($this->group).")");
        print "Macht wurde angelegt.";
    }
    
    public function action_setcontact() {
        global $masterof;
        if (!$masterof->has($this->group)) {
            throw new Exception("Unbefugter Zugriff!");
            return;
        }
        $db = DBManager::get();
        
        if ($_REQUEST['contact']) {
            $result = $db->query("INSERT IGNORE INTO contact (force1, force2) VALUES (".$_REQUEST['macht1'].", ".$_REQUEST['macht2'].")");
        } else {
            $result = $db->query("DELETE FROM contact WHERE (force1 = ".$_REQUEST['macht1']." AND force2 = ".$_REQUEST['macht2'].") OR (force1 = ".$_REQUEST['macht2']." AND force2 = ".$_REQUEST['macht1'].")");
        }
    }
    
    public function action_give_force_to() {
        global $masterof;
        if (!$masterof->has($this->group)) {
            throw new Exception("Unbefugter Zugriff!");
            return;
        }
        $db = DBManager::get();
        $db->query("INSERT IGNORE INTO relate_sp_f SET spieler = ".$db->quote($_REQUEST['spieler']).", force_id = ".$db->quote($_REQUEST['macht'])." ");
        
        $this->action_admin_show_macht();
    }
    
    public function action_happynewyear() {
        global $masterof;
        if (!$masterof->has($this->group)) {
            throw new Exception("Unbefugter Zugriff!");
            return;
        }
        $db = DBManager::get();
        
        $jahreszahl = (int) $db->query("SELECT jahreszahl " .
            "FROM year " .
            "WHERE gruppe = ".$db->quote($this->group)." " .
            "ORDER BY zeit DESC " .
            "LIMIT 1")->fetch(PDO::FETCH_COLUMN, 0);
        $jahreszahl++;
        $db->query("UPDATE gruppe SET year = ".$db->quote($jahreszahl)." WHERE id = " . $db->quote($this->group));
        $db->query("INSERT INTO year (gruppe, zeit, jahreszahl) VALUES (".$db->quote($this->group).", UNIX_TIMESTAMP(), ".$db->quote($jahreszahl).")");
        
        $this->action_admin();
    }

    public function action_set_module_enabled() {
        global $masterof;
        if ($this->group && $masterof->has($this->group) && $_REQUEST['modules'] && is_array($_REQUEST['modules'])) {
            $db = DBManager::get();
            if (!in_array("bureau", $_REQUEST['modules'])) {
                $_REQUEST['modules'][] = "bureau";
            }
            if (!in_array("abmelden", $_REQUEST['modules'])) {
                $_REQUEST['modules'][] = "abmelden";
            }
            foreach ($_REQUEST['modules'] as $position => $module) {
                if (file_exists(dirname(__file__)."/../".$module)) {
                    $type = 'controller';
                } elseif (file_exists(dirname(__file__)."/../../plugins/".$module)) {
                    $type = "plugins";
                }
                if ($type) {
                    $db->exec(
                        "INSERT INTO module_groups " .
                        "SET modulename = ".$db->quote($module).", " .
                            "type = '$type', " .
                            "gruppe_id = ".$db->quote($_REQUEST['group']).", " .
                            "position = ".$db->quote((int) $position+1)." " .
                        "ON DUPLICATE KEY UPDATE position = ".$db->quote((int) $position+1)." " .
                    "");
                }
                $_REQUEST['modules'][$position] = addslashes($module);
            }
            $db->exec(
                "DELETE FROM module_groups " .
                "WHERE gruppe_id = ".$db->quote($_REQUEST['group'])." " .
                    "AND modulename NOT IN ('".implode("','", $_REQUEST['modules'])."') " .
            "");
        }
    }
}
