<?php

require_once dirname(__file__)."/classes/NewsPDF.class.php";

class news extends ModuleController {
    
    public function __construct() {
        FileInclude::JS("news_js", "news.js", $this);
        //FileInclude::CSS("news_css", "news.css", $this);
        if (class_exists("bureau")) {
            bureau::registerFirepoint("group_statistic", "news::getStatistics");
        }
    }

    /**
     * sets the navigation for the user in the "bridge" or second navigation
     * @return: array('actionname1' => $clearName, 'actionname2' => $clearName, ...)
     */
    public function getNavigation() {
        $nav = array(
            'write' => array('title' => "Schreiben"),
            'mails' => array('title' => "Postfach")
        );
        return $nav;
    }
    
    public function defaultAction() {
        return "mails";
    }
    
    public function getTitle() {
        return "News";
    }

    public function sendData() {
        global $login, $force, $gruppe;
        $db = DBManager::get();
        $output = array();
        
        $output['news'] = $db->query(
            "SELECT SUM(1) " .
            "FROM messages AS m " .
                "INNER JOIN relate_messages AS toforce ON (toforce.ms_id = m.id) " .
                "INNER JOIN relate_sp_f AS spieler ON (spieler.force_id = toforce.toforce) " .
            "WHERE toforce.yet_read = '0' " .
                "AND spieler.spieler = ".$db->quote($login)." " .
            "GROUP BY m.id " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if ($output['news'] > 0) {
            $output['alert'] = true;
        }

        return $output;
    }

    public function isSomethingNew() {
        global $login, $force, $gruppe;
        $db = DBManager::get();
        
        $anzahl = $db->query(
            "SELECT SUM(1) " .
            "FROM messages AS m " .
                "INNER JOIN relate_messages AS toforce ON (toforce.ms_id = m.id) " .
                "INNER JOIN relate_sp_f AS spieler ON (spieler.force_id = toforce.toforce) " .
            "WHERE toforce.yet_read = '0' " .
                "AND spieler.spieler = ".$db->quote($login)." " .
            "GROUP BY m.id " .
        "")->fetch(PDO::FETCH_COLUMN, 0);

        return $anzahl ? true : false;
    }
    
    public static function getStatistics($params) {
        $db = DBManager::get();
        $ret = "";
        $ret .= $db->query(
            "SELECT COUNT(DISTINCT m.id) " .
            "FROM `messages` AS m " .
                "INNER JOIN `relate_f_gr` AS f1 ON (f1.force_id = m.fromforce) " .
            "WHERE f1.gruppe_id=".$db->quote($params['matrix'])." " .
        "")->fetch(PDO::FETCH_COLUMN, 0)." Nachrichten<br>";
        return $ret;
    }

    ////////////////////////////////////////////////////////////////////////////
    //                                actions                                 // 
    ////////////////////////////////////////////////////////////////////////////
    
    public function action_write() {
        global $login, $force;
        $db = DBManager::get();
        $adressees = array();
        foreach ($force as $f) {
            $adressees[$f] = $db->query(
                "SELECT force2 " .
                "FROM contact " .
                "WHERE force1 = ".$db->quote($f)." " .
                    "AND force2 != ".$db->quote($f)." " .
                "UNION DISTINCT SELECT force1 " .
                "FROM contact " .
                "WHERE force2 = ".$db->quote($f)." " .
                    "AND force1 != ".$db->quote($f)." " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
        }
        $old_news = $db->query(
            "SELECT id, fromforce, date, content, frompicture, pdf " .
            "FROM messages " .
                "INNER JOIN relate_sp_f ON (messages.fromforce = relate_sp_f.force_id) " .
            "WHERE relate_sp_f.spieler = ".$db->quote($login)." " .
                "AND messages.frompicture IS NULL " .
        "")->fetch(PDO::FETCH_ASSOC);
        print Template::summon(dirname(__file__)."/views/composer.php")
                    ->with('forces', $force)
                    ->with('adressees', $adressees)
                    ->with('old_news', $old_news)
                    ->render();
    }

    public function action_save() {
        global $force;
        $db = DBManager::get();
        if ($_REQUEST['content'] && $_REQUEST['fromforce'] && $force->has($_REQUEST['fromforce'])) {
            $set =
                "SET content = ".$db->quote($_REQUEST['content']).", " .
                    "fromforce = ".$db->quote($_REQUEST['fromforce']).", " .
                    "pdf = '0' " .
                "";
            if ($_REQUEST['message_id']) {
                $db->exec(
                    "UPDATE messages " .
                    $set .
                    "WHERE id = ".$db->quote($_REQUEST['message_id'])." " .
                "");
                print $_REQUEST['message_id'];
            } else {
                $db->exec(
                    "INSERT INTO messages " .
                    $set .
                "");
                print $db->lastInsertId();
            }
        }
    }

    public function action_upload_pdf() {
        $new_file = new NewsPDF($_REQUEST['message_id'] ? $_REQUEST['message_id'] : null);
        $new_file->upload();
    }
    
    public function action_send() {
        global $force;
        $db = DBManager::get();
        if ($_REQUEST['fromforce'] && $force->has($_REQUEST['fromforce']) && is_array($_REQUEST['adressees'])) {
            $picture = Forces::forcespicture($_REQUEST['fromforce']);
            $set =
                "SET fromforce = ".$db->quote($_REQUEST['fromforce']).", " .
                    "date = ".$db->quote(time()).", " .
                    "frompicture = ".$db->quote($picture['id']).", " .
                    ($_REQUEST['pdf'] && $_REQUEST['message_id']
                        ?   "pdf = '1' "
                        :   "pdf = '0', " .
                            "content = ".$db->quote($_REQUEST['content'])." "
                            ) .
                "";
            if ($_REQUEST['message_id']) {
                $db->exec(
                    "UPDATE messages " .
                    $set .
                    "WHERE id = ".$db->quote($_REQUEST['message_id'])." " .
                "");
                $id = $_REQUEST['message_id'];
            } else {
                $db->exec(
                    "INSERT INTO messages " .
                    $set .
                "");
                $id = $db->lastInsertId();
            }
            //nun noch die Empf�nger hinzuf�gen:
            $contacts = $db->query(
                "SELECT force1 FROM contact WHERE force2 = ".$db->quote($_REQUEST['fromforce'])." " .
                "UNION SELECT force2 FROM contact WHERE force1 = ".$db->quote($_REQUEST['fromforce'])." " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
            foreach ($_REQUEST['adressees'] as $receiver) {
                if (in_array($receiver, $contacts)) {
                    $picture = Forces::forcespicture($receiver);
                    $db->exec(
                        "INSERT IGNORE INTO relate_messages " .
                        "SET ms_id = ".$db->quote($id).", " .
                            "toforce = ".$db->quote($receiver).", " .
                            "yet_read = '0', " .
                            "topicture = ".$db->quote($picture['id'])." " .
                    "");
                }
            }
        }
    }
    
    public function action_mails() {
        global $login, $force, $gruppe;
        $db = DBManager::get();
        $maximum = 0;
        $jahresauswahl = $_REQUEST['jahresauswahl'] ? $_REQUEST['jahresauswahl'] : 0;
        $group_year = $db->query(
                "SELECT year.gruppe, MAX(year.zeit) as zeit, MAX(year.jahreszahl) as jahreszahl, MIN(year.jahreszahl) AS startjahr " .
                "FROM year " .
                "INNER JOIN relate_f_gr ON (relate_f_gr.gruppe_id = year.gruppe) " .
                    "INNER JOIN relate_sp_f ON (relate_sp_f.force_id = relate_f_gr.force_id) " .
                "WHERE relate_sp_f.spieler = ".$db->quote($login)." " .
                "GROUP BY year.gruppe " .
                "ORDER BY jahreszahl DESC" .
        "")->fetchAll();
        $maximum = $group_year[0]['jahreszahl'];
        $minimum = $maximum;
        foreach ($group_year as $row) {
            $minimum = ($minimum > $row['startjahr']) ? $row['startjahr'] : $minimum;
        }
        
        print Template::summon(dirname(__file__)."/views/mails.php")
                    ->with("years", $years)
                    ->with("forces", $force)
                    ->with("gruppe", $gruppe)
                    ->with("maximum", $maximum)
                    ->with("minimum", $minimum)
                    ->with("group_year", $group_year)
                    ->with("jahresauswahl", $jahresauswahl)
                    ->render();
    }
    
    public function action_get_mails() {
        global $login, $force;
        $db = DBManager::get();
        $jahresauswahl = $_REQUEST['year'];
        $data = array('inbox' => array(), 'outbox' => array());
        $inbox = $db->query(
            "SELECT DISTINCT m.id, m.fromforce, m.date, m.content, r.toforce, r.yet_read, m.frompicture, r.topicture, m.pdf " .
            "FROM messages AS m " .
                "INNER JOIN relate_messages AS r ON (r.ms_id = m.id) " .
                "INNER JOIN relate_sp_f AS f ON (r.toforce = f.force_id) " .
                "INNER JOIN relate_f_gr AS g ON (f.force_id = g.force_id) " .
                "INNER JOIN relate_master_gr AS ma ON (ma.gruppe_id = g.gruppe_id) " .
            "WHERE " .
                "date > IF(".
                        $db->quote($_REQUEST['latest_date'])." > (SELECT zeit FROM year WHERE gruppe = g.gruppe_id ORDER BY zeit DESC LIMIT ".addslashes($jahresauswahl).", 1), ".
                        $db->quote($_REQUEST['latest_date']).", " .
                        "(SELECT zeit FROM year WHERE gruppe = g.gruppe_id ORDER BY zeit DESC LIMIT ".addslashes($jahresauswahl).", 1)" .
                        ")".
                    (($jahresauswahl > 0) 
                        ? "AND date < (SELECT zeit FROM year WHERE gruppe = g.gruppe_id ORDER BY zeit DESC LIMIT ".addslashes($jahresauswahl-1).", 1) " 
                        : "" ) .
                "AND (f.spieler = ".$db->quote($login)." OR ma.spieler = ".$db->quote($login).") " .
                "ORDER BY m.date ASC " . 
        "")->fetchAll();
        foreach ($inbox as $news_row) {
            $data['inbox'][] = array(
                'id' => $news_row['id'],
                'date' => $news_row['date'],
                'html' => Template::summon(dirname(__file__)."/views/inbox_overview.php")
                                ->with("id", $news_row['id'])
                                ->with("row", $news_row)
                                ->with("force", $force)
                                ->render()
            );
        }
        $outbox = $db->query(
            "SELECT m.id, m.fromforce, m.date, m.content, m.frompicture, m.pdf, r.*, GROUP_CONCAT(r.toforce SEPARATOR '_') AS toforces, GROUP_CONCAT(r.topicture SEPARATOR '_') AS topictures " .
            "FROM messages AS m " .
                "INNER JOIN relate_sp_f AS f ON (m.fromforce = f.force_id) " .
                "INNER JOIN relate_f_gr AS g ON (f.force_id = g.force_id) " .
                "INNER JOIN relate_messages AS r ON (r.ms_id = m.id) " .
            "WHERE " .
                "date > IF(".
                        $db->quote($_REQUEST['latest_date'])." > (SELECT zeit FROM year WHERE gruppe = g.gruppe_id ORDER BY zeit DESC LIMIT ".addslashes($jahresauswahl).", 1), ".
                        $db->quote($_REQUEST['latest_date']).", " .
                        "(SELECT zeit FROM year WHERE gruppe = g.gruppe_id ORDER BY zeit DESC LIMIT ".addslashes($jahresauswahl).", 1)" .
                        ")".
                    (($jahresauswahl > 0) 
                        ? "AND date < (SELECT zeit FROM year WHERE gruppe = g.gruppe_id ORDER BY zeit DESC LIMIT ".addslashes($jahresauswahl-1).", 1) " 
                        : "" ) .
                "AND (f.spieler = ".$db->quote($login).") " . //keine Spielleitersache
                "GROUP BY m.id " .
                "ORDER BY m.date ASC " . 
        "")->fetchAll();
        foreach ($outbox as $news_row) {
            $data['outbox'][] = array(
                'id' => $news_row['id'],
                'date' => $news_row['date'],
                'html' => Template::summon(dirname(__file__)."/views/outbox_overview.php")
                                ->with("id", $news_row['id'])
                                ->with("row", $news_row)
                                ->with("force", $force)
                                ->render()
            );
        }
        print json_encode($data);
    }
    public function action_watch_mail() {
        global $login, $force;
        $db = DBManager::get();
        $mail = $db->query(
            "SELECT m.id, m.fromforce, m.date, m.content, r.toforce, r.yet_read, m.frompicture, r.topicture, m.pdf " .
            "FROM messages AS m " .
                "INNER JOIN relate_messages AS r ON (r.ms_id = m.id) " .
                "INNER JOIN relate_sp_f AS f ON (r.toforce = f.force_id) " .
                "INNER JOIN relate_sp_f AS sender ON (sender.force_id = m.fromforce) " .
                "INNER JOIN relate_f_gr AS g ON (f.force_id = g.force_id) " .
                "INNER JOIN relate_master_gr AS ma ON (ma.gruppe_id = g.gruppe_id) " .
            "WHERE ( " .
                "f.spieler = ".$db->quote($login)." " .
                "OR ma.spieler = ".$db->quote($login)." " .
                "OR sender.spieler = ".$db->quote($login)." " .
            ") " .
                "AND m.id = ".$db->quote($_REQUEST['message_id'])." " .
        "")->fetch();
        $group = $db->query(
            "SELECT gruppe_id FROM relate_f_gr WHERE force_id = ".$db->quote($mail['fromforce'])." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        print Template::summon(dirname(__file__)."/views/one_mail.php")
                    ->with("mail", $mail)
                    ->with("group", $gruppe)
                    ->render();
        if ($force->has($mail['toforce'])) {
            $db->exec(
                "UPDATE relate_messages " .
                "SET yet_read = '1' " .
                "WHERE ms_id = ".$db->quote($_REQUEST['message_id'])." " .
                    "AND toforce IN (" .
                        "SELECT relate_sp_f.force_id FROM relate_sp_f WHERE relate_sp_f.spieler = ".$db->quote($login)." " .
                    ") " .
            "");
        }
    }
}
