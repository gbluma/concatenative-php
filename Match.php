<?php 
 
class Matcher {
    private $cases = array(); 
 
    function __construct($target, $source) { 
        $this->target = $target; 
        $this->source = $source; 
    } 
 
    public function option($p,$f) { 
        $this->cases[$p] = $f; 
        return $this; 
    } 
 
    public function exec() { 
        // check that element is in search set 
        if (!isset($this->cases[$this->target])) { 
            // do we have a catch-all? 
            if (isset($this->cases['_'])) { 
                // ... yes, use catch all. 
                return $this->cases["_"](); 
            } else { 
                // ... no, throw error. 
                throw new Exception("Search term not found."); 
            } 
        } 
        if (!isset($this->cases['_'])) { 
            // check for exhaustive matching 
            foreach($this->source as &$s) { 
                if (!isset($this->cases[$s])) { 
                    throw new Exception("$s option not covered" 
                        . " in exhaustive pattern match"); 
                } 
            } 
        }
 
        // we can assume that we have a match return 
        $this->cases[$this->target](); 
    } 
} 
 
// convenience function to simplify implementation 
function match($target, $source) { 
    $o = new Matcher($target, $source); 
    return $o; 
}
