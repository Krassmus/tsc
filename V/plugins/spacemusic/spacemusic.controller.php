<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class spacemusic extends Module {

    public function __construct() {
        global $login;
        bureau::registerFirepoint("plugin_settings", "spacemusic::enable_music_setting");
        FileInclude::JS("spacemusic_js", "spacemusic.js", $this);
        $db = DBManager::get();
        $autoplay = (int) $db->query(
            "SELECT value " .
            "FROM datafields " .
            "WHERE id = ".$db->quote($login)." " .
                "AND db_table = 'spieler' " .
                "AND field = 'spacemusic_autoplay' " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        FileInclude::Variables("TSC.spacemusic.autoplay = ".($autoplay ? "true" : "false").";");
    }

    static public function enable_music_setting() {
        global $login;
        $db = DBManager::get();
        $autoplay = (int) $db->query(
            "SELECT value " .
            "FROM datafields " .
            "WHERE id = ".$db->quote($login)." " .
                "AND db_table = 'spieler' " .
                "AND field = 'spacemusic_autoplay' " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        return array(array(
            "Hintergrundmusik",
            "<input type=\"checkbox\" onChange=\"TSC.spacemusic.play_pause(this.checked)\"".($autoplay ? " checked" : "").">"
        ));
    }

    public function action_songlist() {
        $handle = opendir(dirname(__file__).'/music');
        $list = array();
        while (false !== ($file = readdir($handle))) {
            if (stripos($file, ".ogg") !== false) {
                $list[] = "plugins/spacemusic/music/".$file;
            }
        }
        shuffle($list);
        print json_encode($list);
    }

    public function action_set_autoplay() {
        global $login;
        $autoplay = $_REQUEST['autoplay'] ? 1 : 0;
        $db = DBManager::get();
        $db->exec(
            "INSERT datafields " .
            "SET " .
                "db_table = 'spieler', " .
                "field = 'spacemusic_autoplay', " .
                "id = ".$db->quote($login).", " .
                "value = ".$db->quote($autoplay)." " .
            "ON DUPLICATE KEY UPDATE " .
                "value = ".$db->quote($autoplay)." " .
        "");

    }

}