<?php

require_once dirname(__file__)."/Module.class.php";

class FileInclude {
    
    static $scripts = array();
    
    public static function getHeaderFiles() {
        $output;
        if (isset(self::$scripts["js"])) {
            foreach (self::$scripts["js"] as $jscript) {
                $firstFolder = file_exists(dirname(__file__)."/../controller/".$jscript) ? "controller/" : "plugins/";
                $output .= '    <script src="'.$firstFolder.$jscript.'" type="text/javascript"></script>'."\n";
            }
        }
        if (isset(self::$scripts["css"])) {
            foreach (self::$scripts["css"] as $cssfile) {
                $firstFolder = file_exists(dirname(__file__)."/../controller/".$cssfile) ? "controller/" : "plugins/";
                $output .= '    <link rel="stylesheet" href="'.$firstFolder.$cssfile.'" type="text/css">'."\n";
            }
        }
        if (isset(self::$scripts['predef'])) {
            $output .= '    <script type="text/javascript">' . self::$scripts['predef'] . '    </script>'."\n";
        }
        return $output;
    }
    
    public static function JS($definition, $filename, $module) {
        if ($module instanceof Module) {
            if (!isset(self::$scripts["js"])) {
                self::$scripts["js"] = array();
            }
            self::$scripts["js"][$definition] = get_class($module)."/media/".$filename;
        }
    }
    
    public static function CSS($definition, $filename, $module) {
        if ($module instanceof Module) {
            if (!isset(self::$scripts["css"])) {
                self::$scripts["css"] = array();
            }
            self::$scripts["css"][$definition] = get_class($module)."/media/".$filename;
        }
    }

    public static function Variables($js_text) {
        self::$scripts["predef"] .= $js_text . "\n";
    }
    
}