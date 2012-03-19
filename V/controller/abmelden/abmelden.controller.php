<?php

class abmelden extends Module {
    
    public function __construct() {
        parent::__construct();
        FileInclude::JS("abmelden_js", "abmelden.js", $this);
    }
    
    public function sendData() {
        global $stil;
        $url = get_class($this) .
                       "/media" .
                       "/title_".($stil['headercolor'] === "1" ? "blue" : ($stil['headercolor'] === "2" ? "green" : "orange")).".png";
        $url = "controller/".$url;
        return array(
            'image_url' => $url
        );
    }
}