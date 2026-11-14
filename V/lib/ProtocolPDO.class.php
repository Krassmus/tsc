<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProtocolPDO
 *
 * @author Krassmus
 */
class ProtocolPDO extends PDO {
    static protected $protocol = array();

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false {
        return parent::query($query, $fetchMode, ...$fetchModeArgs);
    }
}

class ProtocolPDOStatement extends PDOStatement {
    
}