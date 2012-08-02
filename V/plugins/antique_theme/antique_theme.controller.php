<?php

class antique_theme extends Module {
    
    public function __construct() {
        FileInclude::CSS("tsc_css", "antique.css", $this);
        FileInclude::setHeaderColor(0, "#aa1122");
        FileInclude::setHeaderColor(1, "#111155");
        FileInclude::setHeaderColor(2, "#115555");
    }
    
}
