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
        global $stil;
        if ($this->isSomethingNew()) {
            $url = get_class($this)."/media/title_yellow.png";
        } else {
            $url = get_class($this) .
                           "/media" .
                           "/title_".($stil['headercolor'] === "1" ? "blue" : ($stil['headercolor'] === "2" ? "green" : "orange")).".png";
        }
        if (file_exists(dirname(__file__)."/../controller/".$url)) {
            $url = "controller/".$url;
        } else {
            $url = "plugins/".$url;
        }
        return array(
            'image_url' => $url,
			'title' => get_class($this)
        );
    }
    
    
    /**
     * braucht man eventuell gar nicht, wenn nur getTitle die Titelzeile ausmacht
     */
    public function isSomethingNew() {
        return false;
    }
    
}