<?php

class Items implements ArrayAccess, Iterator, Countable {
    protected $arr = array();
    private $iteratorindex = 0;

    function __construct() {
    }
    
    public function offsetExists ($offset) {
    	return isset($this->arr[$offset ? $offset : 0]);
    }
    public function offsetGet ($offset) {
    	return $this->arr[$offset ? $offset : 0];
    }
    public function offsetSet ($offset, $value) {
    	$this->arr[$offset ? $offset : 0] = $value;
    }
    public function offsetUnset ($offset) {
    	unset($this->arr[$offset ? $offset : 0]);
    }
    public function count() {
        return count($this->arr);
    }
    public function rewind()
    {
        $this->iteratorindex = 0;
    }
    public function current() {
        $k = array_keys($this->arr);
        return $this->arr[$k[$this->iteratorindex]];
    }
    public function key() {
        $k = array_keys($this->arr);
        return $k[$this->iteratorindex];
    }
    public function next() {
        $k = array_keys($this->arr);
        return isset($k[++$this->iteratorindex]) 
                    ? $this->arr[$k[$this->iteratorindex]] 
                    : false;
    }
    public function valid() {
        $k = array_keys($this->arr);
        return isset($k[$this->iteratorindex]);
    }
    
    public function has($value) {
    	return in_array($value, $this->arr);
    }
    public function length() {
    	return count($this->arr);
    }
    
    
}
