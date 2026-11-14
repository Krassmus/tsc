<?php

class Items implements ArrayAccess, Iterator, Countable {
    protected $arr = array();
    private $iteratorindex = 0;

    function __construct() {
    }
    
    public function offsetExists(mixed $offset): bool {
        return isset($this->arr[$offset ?: 0]);
    }
    public function offsetGet(mixed $offset): mixed {
        return $this->arr[$offset ?: 0] ?? null;
    }
    public function offsetSet(mixed $offset, mixed $value): void {
        if ($offset === null) {
            $this->arr[] = $value;   // $items[] = $x haengt an
        } else {
            $this->arr[$offset] = $value;
        }
    }
    public function offsetUnset(mixed $offset): void {
        unset($this->arr[$offset ?: 0]);
    }
    public function count(): int {
        return count($this->arr);
    }
    public function rewind(): void {
        $this->iteratorindex = 0;
    }
    public function current(): mixed {
        $k = array_keys($this->arr);
        return $this->arr[$k[$this->iteratorindex]] ?? null;
    }
    public function key(): mixed {
        $k = array_keys($this->arr);
        return $k[$this->iteratorindex] ?? null;
    }
    public function next(): void {
        ++$this->iteratorindex;
    }
    public function valid(): bool {
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
