<?php
/*
 * DBManager.class.php - model for getting access to the datebase
 *
 * Copyright (C) 2010 - Rasmus Fuhse <Krassmus@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

if (file_exists(dirname(__file__)."/../../config.php")) {
  include_once dirname(__file__)."/../../config.php";
}

class DBManager {
    static $base = NULL;
   
    /**
    * returns a PDO-object to the default database
    * @return PDO: database-handler
    */
    static function get() {
        global $DB_TYPE, $DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME;
        if (self::$base === NULL) {
            self::$base = self::create($DB_TYPE, $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD);
        }
        return self::$base;
    }
  
    static function create($db_type, $db_host, $db_name, $db_user, $db_pass) {
        $db = false;
        switch ($db_type) {
          case "mysql":
            try {
              $db = new PDO("mysql:dbname=".$db_name.";host=".$db_host, $db_user, $db_pass);
            } catch (Exception $exception) {
              print _("Fehler: ").$exception->getMessage();
              return false;
            }
            break;
          case "sqlite":
            try {
              $db_url = dirname(__file__)."/../db/database.sqlite";
              $db = new PDO("sqlite:".$db_url);
            } catch (Exception $exception) {
              print _("Fehler: ").$exception->getMessage();
              return false;
            }
            break;
        }
        if ($db) {
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $db;
    }
  
    static function checkdb($db = null) {
        if (!$db) {
            $db = self::get();
        }
    }
  
    public function __construct() {
    }
  
  
}