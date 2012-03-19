<?php

require_once dirname(__file__)."/classes/Matrix3D.class.php";

class space_formatierung extends Module {
    
    public function __construct() {
        FileInclude::JS("three_js", "Three.js", $this);
        FileInclude::JS("requestanimationframe_js", "RequestAnimationFrame.js", $this);
        Text::addFormatRule("Matrix3D::special_format_stargroup", "matrix");
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //                                actions                                 // 
    ////////////////////////////////////////////////////////////////////////////
    
    
}
