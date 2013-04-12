<?php

/**
 * TODO: 
 *  - nested expressions (concat on ';', don't just evaluate )
 *  - anonymous functions
 *  - beta-reduction in lists
 *  _ perfix-style operations ": a 5 ;"
 */
class Parser 
{

    /**
     * Converts the document into a set of tokens, disregarding comments.
     */
    public static function tokenize($str) 
    {
        // counters
        $tokens = array();   
        $isInSingleQuote 
            = $isInDoubleQuote 
            = $isInComment
            = false;
        $currentToken    = '';

        foreach(str_split($str) as $c) {
            switch($c) {

                case "\r":
                case "\n":
                case "\t":
                case ' ': 
                    if (!$isInComment) {
                        if (!$isInSingleQuote && !$isInDoubleQuote) {
                            if ($currentToken != '') {
                                $tokens[] = $currentToken;
                                $currentToken = '';
                            }
                        } else {
                            // ... capture these characters while in quotes
                            $currentToken .= $c;
                        }
                    }
                    if ($c == "\n") {
                        $isInComment = false;
                    }
                    break;

                case "'":
                    if (!$isInComment) {
                        $isInSingleQuote = !$isInSingleQuote;
                        $currentToken .= $c;
                    }
                    break;

                case '"':
                    if (!$isInComment) {
                        $isInDoubleQuote = !$isInDoubleQuote;
                        $currentToken .= $c;
                    }
                    break;

                case '!':
                case '#':
                    if (!$isInSingleQuote && !$isInDoubleQuote) {
                        $isInComment = true;
                    }
                    break;

                default:
                    if (!$isInComment) {
                        $currentToken .= $c;
                    }
            }
            
        }
        
        // append whatever is at the end
        $tokens[] = $currentToken;   
        return $tokens;
    }

    /**
     * Taking the tokens in, build semantics from the items given.
     */
    public static function evaluate($tokens) 
    {
        $stack = null;
        $tmp = array();
        $level = 0;

        foreach($tokens as $token) {
            switch($token) {

                case "{":
                case "[": 
                    // ... open a new substack
                    $level++;
                    if ($stack !== null) {
                        $tmp[] = $stack;
                    }
                    $stack = array();
                    break;

                case "}":
                case "]":
                    // ... close the most recent substack
                    $level--;
                    $tmp[] = $stack;
                    $last     = array_pop($tmp);
                    $second   = array_pop($tmp);
                    $second[] = $last;
                    $stack = $second;
                    break;

                case ";":
                    // ... execute 

                    $e = self::translate($stack);

                    // top level expressions can be executed,
                    if ($level == 0) {
                        $e .= "; \n";
                        echo $e;
                        eval($e);
                        $stack = array();
                    } else {
                        // ... inner blocks are delayed, don't execute.
                        $stack = $e;
                    }
                    break;

                default:
                    $stack[] = $token;
            }
        }

        return $stack;
    }

    /**
     * Handle some details of converting data to PHP..
     */
    public static function export($args) 
    {
        $isFirst = true; 
        $output = "";
        foreach($args as $arg) {

            if (!$isFirst) 
                $output .= ", ";

            $isFirst = false;
            
            if (is_array($arg)) {
                $tmp = 'array('. self::export($arg) . ')';
                $output .= str_replace(', =>,', ' =>', $tmp);
            } else {
                $output .= "$arg";
            }
        }
        return $output;
    }

    /**
     * Work around some issues with PHP (i.e. assignment can't be done via a function, so we
     * convert the function syntax `set(x,y)` to `x = y`.)
     */
    public static function translate($stack) 
    {
        $stack = array_reverse($stack);
        $func = array_shift($stack);
        $expr = "$func(" . self::export($stack) . ")";

        // special case (assignment)
        $expr = preg_replace("/set\(([^,]+),([^)]+)\)/",
                             '$1 =$2', 
                             $expr);

        // special case (class definition)
        $expr = preg_replace("/class\(([^)]+)\)/",
                             'class $1 extends Prototype {}', 
                             $expr);

        // special case (object instantiation)
        $expr = preg_replace("/new\(([^,]+),([^,]+),([^)]+)\)/",
                             '$1 = new $2($3 )', 
                             $expr);

        // special case (anonymous functions)
        $expr = preg_replace("/function\(([^,]+),(.*)\)/",
                             '$1 = function ($args) { extract($args); $2; }', 
                             $expr);

        // special case (tru lambdas functions)
        $expr = preg_replace("/lambda\((.*)\)/",
                             'function ($args) { extract($args); $1; }', 
                             $expr);

        // special case (return)
        $expr = preg_replace("/return\((.*)\)/", 'return ($1) ', $expr);

        // special case (concatenatino)
        $expr = preg_replace("/\.\((.*)\)/", 'Prelude::concat($1)', $expr);

        // echo "\nexpr: $expr\n";
        return $expr;
    }
}

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
            return call_user_func_array($prop, $args);
        }
    }
}

/**
 * Library functions
 */
class Prelude {
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
}

// ---------- start -------------
if (count($argv) < 2) 
    die("usage: php forth.php <filename>\n");

$prog = file_get_contents( $argv[1] );
Parser::evaluate(Parser::tokenize($prog));


