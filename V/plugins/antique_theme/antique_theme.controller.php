<?php

class antique_theme extends Module {
    
    public function __construct() {
        FileInclude::CSS("tsc_css", "antique.css", $this);
    }
    
}
