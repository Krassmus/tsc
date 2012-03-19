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

    public function query($statement) {
        
        return parent::query($statement);
    }
}

class ProtocolPDOStatement extends PDOStatement {
    
}