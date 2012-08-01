<?php

require_once dirname(__file__)."/Module.class.php";

class FileInclude {
    
    static $headelements = array();
    
    public static function init() {
        self::JS("jquery", "jquery-1.4.2.min.js");
        self::JS("jquery-ui", "jquery-ui-1.8.5.custom.min.js");
        self::JS("autoresize", "autoresize.jquery.min.js");
        self::JS("jquery_scrollto", "jquery.scrollTo-min.js");
        self::JS("qqupload", "qqUpload/fileuploader.js");
        self::JS("tsc_js", "tsc.js");
        
        self::CSS("qqupload", "qqUpload/fileuploader.css");
        self::CSS("jquery-ui", "jquery-ui-1.8.5.custom.css");
        self::CSS("tsc_css", "tsc.css");
    }
    
    public static function getHeaderFiles() {
        $output = "";
        if (isset(self::$headelements["js"])) {
            foreach (self::$headelements["js"] as $jscript) {
                $firstFolder = "";
                if (file_exists(dirname(__file__)."/../controller/".$jscript)) {
                    $firstFolder = "controller/";
                } elseif(file_exists(dirname(__file__)."/../plugins/".$jscript)) {
                    $firstFolder = "plugins/";
                }
                $output .= '    <script src="'.$firstFolder.$jscript.'" type="text/javascript"></script>'."\n";
            }
        }
        if (isset(self::$headelements["css"])) {
            foreach (self::$headelements["css"] as $cssfile) {
                $firstFolder = "";
                if (file_exists(dirname(__file__)."/../controller/".$cssfile)) {
                    $firstFolder = "controller/";
                } elseif(file_exists(dirname(__file__)."/../plugins/".$cssfile)) {
                    $firstFolder = "plugins/";
                }
                $output .= '    <link rel="stylesheet" href="'.$firstFolder.$cssfile.'" type="text/css">'."\n";
            }
        }
        if (isset(self::$headelements['predef'])) {
            $output .= '    <script type="text/javascript">' . self::$headelements['predef'] . '    </script>'."\n";
        }
        return $output;
    }
    
    public static function JS($definition, $filename, $module = null) {
        if ($module instanceof Module) {
            if (!isset(self::$headelements["js"])) {
                self::$headelements["js"] = array();
            }
            self::$headelements["js"][$definition] = get_class($module)."/media/".$filename;
        } else {
            self::$headelements["js"][$definition] = "media/".$filename;
        }
    }
    
    public static function CSS($definition, $filename, $module = null) {
        if ($module instanceof Module) {
            if (!isset(self::$headelements["css"])) {
                self::$headelements["css"] = array();
            }
            self::$headelements["css"][$definition] = get_class($module)."/media/".$filename;
        } else {
            self::$headelements["css"][$definition] = "media/".$filename;
        }
    }
    
    public static function DeleteJS($definition) {
        unset(self::$headelements['js'][$definition]);
    }

    public static function DeleteCSS($definition) {
        unset(self::$headelements['css'][$definition]);
    }

    public static function Variables($js_text) {
        self::$headelements["predef"] .= $js_text . "\n";
    }
    
}