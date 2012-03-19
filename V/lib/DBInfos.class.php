<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class DBInfos {

    /*
     * a lot of the information are statically stored in this class
     * to minify the access to database
     */
    static protected $table_info = array();
    static protected $primary_keys = array();
    static protected $table_values = array();
    static protected $item_changed = array();

    static public function fetchTableInfo($table, $force_update = false) {
        if (!isset(self::$table_info[$table]) OR $force_update) {
            $columns = Cache::getCachedFetchAll(
                "SHOW COLUMNS " .
                "FROM `".addslashes($table)."` " .
            "");
            foreach ($columns as $key => $column) {
                $columns[$column['Field']] = $column;
                unset($columns[$key]);
                if ($column['Key'] === "PRI") {
                    self::$primary_keys[$table] = $column['Field'];
                }
            }
            if (!self::$primary_keys[$table]) {
                throw new Exception("Tabelle $table muss einen einzeiligen Primary Key haben, ansonsten lässt sich diese Klasse nicht anwenden.");
            }
            self::$table_info[$table] = $columns;
        }
        return self::$table_info[$table];
    }

    static protected function getValues($table, $id, $force_restore = false) {
        if (!isset(self::$table_values[$table][$id]) OR $force_update) {
            $db = DBManager::get();
            $statement = $db->prepare(
                "SELECT * " .
                "FROM ´".addslashes($table)."´ " .
                "WHERE ´".addslashes(self::$primary_keys[$table])."´ = :id " .
            "");
        }
    }

    static public function getStaticValue($table, $id, $field) {
        return self::$table_values[$table][$id][$field];
    }

    static public function setStaticValue($table, $id, $field, $value) {
        if (self::$table_values[$table][$id][$field] != $value) {
            self::$table_values[$table][$id][$field] = $value;
            self::$item_changed[$table][$id] = true;
        }
    }
    

}