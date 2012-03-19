<?php

class Module {

    protected static $firepoints = array();

    public function __construct() {
    }
    
    public function sendData() {
        return null;
    }

    public function activateAction($action) {
        if (method_exists($this, "action_".$action)) {
            $this->{"action_".$action}();
        } else {
            throw new Exception("Modul ".get_class($this)." Seite ".$action." existiert nicht.");
        }
    }

    /**
     * With this method you can create small plugins for any module. The only matter
     * is if the module listens to that point. When a module B uses once the function
     * B::fire('get_information'), your module A can call the
     * function B::registerFirepoint('get_information', 'A::return_information')
     * in its constructor. Then module B gets some information from A. Nice, isn't it?
     * @param string $point : any name of some firepoint - there is no check if it exists
     * @param string $function : name of a function like "matrix::return_information"
     */
    static public function registerFirepoint($point, $function) {
        self::$firepoints[$point][] = $function;
    }

    /**
     * @param string $point : any name of a firepoint
     * @param string $expected : 'string','integer','number','boolean','array'
     * @param array $parameter :
     * @return mixed : depends on $expected, null if no function was registered
     */
    protected function fire($point, $expected = "string", $parameter = array()) {
        is_array(self::$firepoints[$point]) OR self::$firepoints[$point] = array();
        switch ($expected) {
            case "integer":
                $ret = 0;
                foreach (self::$firepoints[$point] as $function) {
                    $ret += (int) call_user_func($function, $parameter);
                }
                break;
            case "number":
                $ret = 0;
                foreach (self::$firepoints[$point] as $function) {
                    $ret += call_user_func($function, $parameter);
                }
                break;
            case "boolean":
                $ret = true;
                foreach (self::$firepoints[$point] as $function) {
                    $ret = $ret && call_user_func($function, $parameter) ? true : false;
                }
                break;
            case "array":
                $ret = array();
                foreach (self::$firepoints[$point] as $function) {
                    $ret = array_merge($ret, call_user_func($function, $parameter));
                }
                break;
            case "string":
            default:
                $ret = "";
                foreach (self::$firepoints[$point] as $function) {
                    $ret .= call_user_func($function, $parameter);
                }
        }
        return $ret;
    }

}