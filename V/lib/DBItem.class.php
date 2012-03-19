<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__)."/DBInfos.class.php";

class DBItem extends DBInfos implements ArrayAccess {

    protected $id = null;
    protected $is_new = false;
    protected $table_name = null;
    protected $new_attributes = array();

    public function __construct($id = null) {
        self::fetchTableInfo($this->table_name);
        if ($id !== null) {
            self::getValues($this->table_name, $id);
            $this->id = $id;
        } else {
            $this->is_new = true;
        }
    }

    public function store() {
        if ($this->isChanged()) {
            self::staticStore($this->table_name);
        }
    }

    public function getId() {
        if (!$this->id) {
            //create ID?
        }
        return $id;
    }

    public function setId($new_id) {
        
    }

    public function getValue($field) {
        if ($this->is_new) {
            return $this->new_attributes[$field];
        } else {
            return self::getStaticValue($this->table_name, $this->getId(), $field);
        }
    }

    public function setValue($field, $value) {
        if ($this->is_new) {
            return $this->new_attributes[$field];
        } else {
            return self::getStaticValue($this->table_name, $this->getId(), $field);
        }
    }

    public function isChanged() {
        return (bool) self::$item_changed[$this->table_name][$this->getId()];
    }

}