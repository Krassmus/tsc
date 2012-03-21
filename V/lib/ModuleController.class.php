<?php

require_once dirname(__file__)."/Module.class.php";

class ModuleController extends Module {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * sets the navigation for the user in the "bridge" or second navigation
     * @return: array('actionname1' => $clearName, 'actionname2' => $clearName, ...)
     */
    public function getNavigation() {
        return array();
    }
    
    public function defaultAction() {
        return null;
    }
    
    /**
     * gets the title-graphic - this is called repeatedly
     */
    public function getTitle() {
        return get_class($this);
    }
    
    
    /**
     * braucht man eventuell gar nicht, wenn nur getTitle die Titelzeile ausmacht
     */
    public function isSomethingNew() {
        return false;
    }
    
}