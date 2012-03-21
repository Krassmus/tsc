<?php

class live extends ModuleController {
    
    public function __construct() {
        FileInclude::JS("live_js", "live.js", $this);
        FileInclude::CSS("live_css", "live.css", $this);
    }
    
    /**
     * sets the navigation for the user in the "bridge" or second navigation
     * @return: array('actionname1' => $clearName, 'actionname2' => $clearName, ...)
     */
    public function getNavigation() {
        $nav = array(
            'chat' => array('title' => "Konferenz"),
            'messenger' => array('title' => "Kurznachrichten")
        );
        return $nav;
    }
    
    public function getTitle() {
        return "Live";
    }

    public function defaultAction() {
        return "messenger";
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //                                actions                                 // 
    ////////////////////////////////////////////////////////////////////////////
    
    public function action_chat() {
        global $force;
        print Template::summon(dirname(__file__)."/views/chat.view.php")
                ->with('force', $force)
                ->render();
    }
    
    public function action_messenger() {
        global $force, $login;
        $db = DBManager::get();
        $online_as = $db->query(
                "SELECT online_as FROM spieler WHERE login = ".$db->quote($login)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if (count($force) === 1) {
            //Für den Start und wenn alleine.
            $db->exec(
                "UPDATE spieler " .
                "SET online_as = ".$db->quote($force[0])." " .
                "WHERE login = ".$db->quote($login)." " .
            "");
        }
        print Template::summon(dirname(__file__)."/views/messenger.view.php")
                ->with('force', $force)
                ->with('online_as', $online_as)
                ->render();
    }
    
    public function sendData() {
        global $login, $force, $gruppe;
	$db = DBManager::get();
        $output = array();
        
        $online_as = $db->query(
            "SELECT online_as FROM spieler WHERE login = ".$db->quote($login)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if ($online_as && $force->has($online_as)) {
            $db->exec("UPDATE forces SET lastsignoflife = UNIX_TIMESTAMP() WHERE id = ".$db->quote($online_as)." ");
        }
		
        //Kontakte online:
        $output['contacts'] = array();
        if ($online_as) {
            $contacts = $db->query("SELECT DISTINCT of.* " .
                "FROM forces AS of " .
                    "JOIN contact AS c " .
                    "INNER JOIN forces AS f ON ((c.force1 = f.id AND c.force2 = of.id) OR (c.force2 = f.id AND c.force1 = of.id)) " .
                "WHERE f.id = ".$db->quote($online_as)." " .
                    "AND UNIX_TIMESTAMP() - of.lastsignoflife <= 61")->fetchAll();
            foreach ($contacts as $key => $contact) {
                $view = Template::summon(dirname(__file__)."/views/single_contact.view.php")
                    ->with('contact', $contact)
                    ->render();
                $output['contacts'][] = array('id' => $contact['id'], 'view' => $view);
            }
        }
        
        //Neue Nachrichten:
        $output['shorts'] = $db->query(
            "SELECT live_messages.*, forces.name " .
            "FROM live_messages " .
                "INNER JOIN forces ON (forces.id = live_messages.from_force) " .
            "WHERE to_force = ".$db->quote($online_as)." " .
            "ORDER BY live_messages.date ASC " .
        "")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($output['shorts'] as $key => $message) {
            $output['shorts'][$key]['content'] = Text::general_format($message['content']);
        }
        if (count($output['shorts'])) {
            $output['alert'] = true;
        }
        
        return $output;
    }

    public function isSomethingNew() {
        global $login;
        $db = DBManager::get();
        $online_as = $db->query(
                "SELECT online_as FROM spieler WHERE login = ".$db->quote($login)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        return count($db->query(
            "SELECT live_messages.* " .
            "FROM live_messages " .
            "WHERE live_messages.to_force = ".$db->quote($online_as)." " .
            "ORDER BY live_messages.date ASC " .
        "")->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    public function action_change_as_online() {
        global $force, $login;
        if ($force->has($_REQUEST['new_force']) OR !$_REQUEST['new_force']) {
            $db = DBManager::get();
            $db->exec(
                "UPDATE spieler " .
                "SET online_as = ".$db->quote($_REQUEST['new_force'])." " .
                "WHERE login = ".$db->quote($login)." " .
            "");
        }
    }

    public function action_send_message() {
        global $force, $login;
        if ($_REQUEST['message'] && $force->has($_REQUEST['fromforce'])) {
            $db = DBManager::get();
            $is_contact = $db->query(
                "SELECT force1 FROM contact WHERE " .
                    "(force1 = ".$db->quote($_REQUEST['to'])." AND force2 = ".$db->quote($_REQUEST['fromforce']).") " .
                    "OR (force2 = ".$db->quote($_REQUEST['to'])." AND force1 = ".$db->quote($_REQUEST['fromforce']).") " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
            if ($is_contact !== false) {
                $db->exec(
                    "INSERT INTO live_messages " .
                    "SET content = ".$db->quote($_REQUEST['message']).", " .
                        "to_force = ".$db->quote($_REQUEST['to']).", " .
                        "from_force = ".$db->quote($_REQUEST['fromforce']).", " .
                        "date = UNIX_TIMESTAMP() " .
                "");
                print Text::general_format($_REQUEST['message']);
            } else {
                throw new Exception("Versenden gescheitert. Kontakt zu Gesprächspartner ist abgebrochen! Sie sollten sich Sorgen machen.");
            }
        }
    }

    public function action_have_read_message() {
        global $force;
        $db = DBManager::get();
        $force_array = "";
        foreach ($force as $key => $f) {
            $key < 1 OR $force_array .= ", ";
            $force_array .= $db->quote($f);
        }
        $db->exec("DELETE FROM live_messages WHERE id = ".$db->quote($_REQUEST['message_id'])." AND to_force IN (".$force_array.") ");
    }
    
}
