<?php

/**
 * Library functions
 */
class Prelude {
    public static function printIt($a) { 
        echo $a;
        return $a;
    }
    public static function println() { 
        foreach(func_get_args() as $arg) {
            if (is_array($arg)) 
                echo implode("\n", $arg) . "\n";
            else
                echo $arg . "\n";
        }
    }
    public static function concat() { 
        $args = func_get_args();
        return implode('', $args[0] );
    }

    public static function map($f, $arr) {
        $output = array();
        foreach($arr as $a) {
            $output[] = $f($a);
        }
        return $output;
    }
}

