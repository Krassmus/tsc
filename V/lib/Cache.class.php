<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cache
 *
 * @author Krassmus
 */
class Cache {
    static protected $handler = null;
    static protected $data = array();
    
    static public function get($category, $parameter) {
        ksort($parameter);
        $handler = self::getCacheHandler();
        if ($handler === null) {
            return self::$data[$category][json_encode($parameter)];
        }
        $search = $handler->prepare(
            "SELECT value " .
            "FROM cache_data " .
            "WHERE category = :category " .
                "AND parameter = :json_parameters " .
        "");
        $search->bindValue(':category', $category);
        $search->bindValue(':json_parameters', json_encode($parameter));
        $result = $search->execute();
        $result = $result->fetchArray(SQLITE3_ASSOC);
        return $result['value'];
    }
    
    /**
     * 
     * @param string $category
     * @param array $parameter
     * @param string $value 
     */
    static public function set($category, $parameter, $value, $expires = 5) {
        ksort($parameter);
        $handler = self::getCacheHandler();
        if ($handler === null) {
            self::$data[$category][json_encode($parameter)] = $value;
            return;
        }
        $delete_old_entry = $handler->prepare(
            "DELETE FROM cache_data " .
            "WHERE category = :category " .
                "AND parameter = :json_parameters " .
        "");
        $delete_old_entry->bindValue(':category', $category);
        $delete_old_entry->bindValue(':json_parameters', json_encode($parameter));
        $delete_old_entry->execute();

        $set_new_entry = $handler->prepare(
            "INSERT INTO cache_data (category, parameter, value, expires) " .
            "VALUES ( " .
                ":category, " .
                ":json_parameters, " .
                ":value, " .
                ":new_expire_date " .
            ") " .
        "");
        $set_new_entry->bindValue(':category', $category);
        $set_new_entry->bindValue(':json_parameters', json_encode($parameter));
        $set_new_entry->bindValue(':value', $value);
        $set_new_entry->bindValue(':new_expire_date', time() + $expires);
        return $set_new_entry->execute();
    }

    static public function getCached($sql_prepared_statement, $parameter, $expires = 15) {
        $cache_result = self::get(md5($sql_prepared_statement), $parameter);
        if ($cache_result !== null) {
            return $cache_result;
        } else {
            $get_value = DBManager::get()->prepare($sql_prepared_statement);
            $get_value->execute($parameter);
            $value = (string) $get_value->fetch(PDO::FETCH_COLUMN, 0);
            self::set(md5($sql_prepared_statement), $parameter, $value, $expires);
            return $value;
        }
    }

    static public function getCachedFetch($sql_prepared_statement, $parameter, $expires = 15) {
        $cache_result = self::get(md5($sql_prepared_statement), $parameter);
        if ($cache_result !== null) {
            return json_decode($cache_result);
        } else {
            $get_value = DBManager::get()->prepare($sql_prepared_statement);
            $get_value->execute($parameter);
            $value = $get_value->fetch(PDO::FETCH_ASSOC);
            self::set(md5($sql_prepared_statement), $parameter, json_encode($value), $expires);
            return $value;
        }
    }

    static public function getCachedFetchAll($sql_prepared_statement, $parameter = array(), $expires = 15) {
        $cache_result = self::get(md5($sql_prepared_statement), $parameter);
        if ($cache_result !== null) {
            return json_decode($cache_result);
        } else {
            $get_value = DBManager::get()->prepare($sql_prepared_statement);
            $get_value->execute($parameter);
            $value = $get_value->fetchAll(PDO::FETCH_ASSOC);
            self::set(md5($sql_prepared_statement), $parameter, json_encode($value), $expires);
            return $value;
        }
    }
    
    /**
     * returns an pdo-sqlite-object to the cache-sqlite-file
     */
    static protected function getCacheHandler() {
        if (self::$handler === null && class_exists("SQLite3")) {
            self::$handler = new SQLite3(dirname(__file__)."/../tmp/cache.sqlite");
            if (!self::checkCacheDB()) {
                self::$handler = null;
            }
        }
        return self::$handler;
    }

    static protected function checkCacheDB() {
        $statement = self::$handler->prepare(
            "SELECT name " .
            "FROM sqlite_master " .
            "WHERE type = 'table' " .
        "");
        $tables = $statement->execute();
        
        $cache_table_exists = false;
        while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {
            if ($table['name'] === "cache_data") {
                $cache_table_exists = true;
            }
        }
        if (!$cache_table_exists) {
            $create_table = self::$handler->prepare(
                "CREATE TABLE cache_data ( " .
                    "category VARCHAR(128) NOT NULL, " .
                    "parameter VARCHAR(1024) NOT NULL, " .
                    "value TEXT NOT NULL, " .
                    "expires BIGINT NOT NULL " .
                ")" .
            "");
            $create_table->execute();
            $create_index = self::$handler->prepare(
                "CREATE UNIQUE INDEX cache_index " .
                "ON cache_data ( category, parameter )" .
            "");
            $create_index->execute();
        }

        $let_expire = self::$handler->prepare(
            "DElETE FROM cache_data " .
            "WHERE expires <= :now " .
        "");
        $let_expire->bindValue(':now', time());
        $let_expire->execute();
        return true;
    }
}


