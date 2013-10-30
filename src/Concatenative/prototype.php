<?php

namespace Concatenative;

/**
 * This allows the classes to be constructed from lists using prototype-style objects.
 */
class Prototype {
    /** take in an array and create methods/properties from them */
    public function __construct($args){
        foreach($args as $key=>$value) {
            $this->$key = $value;
        }
    }

    /** avoid issues with calling member variables that are anonymous functions */
    public function __call($method, $args) {
        if(property_exists($this, $method)) {
            $prop = $this->$method;
            if (is_callable($prop)) {
                return call_user_func_array($prop, $args);
            } else if (is_array($prop) && count($prop) > 0) {
                return call_user_func_array($prop[0], $args);
            } else {
                return $prop;
            }
        } else {
            throw new Exception("$method method not callable");
        }
    }
}
