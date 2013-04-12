<?php

/**
 * TODO: 
 *  - nested lists
 *  - anonymous functions
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
        $stack = array();
        $current_stack = null;

        foreach($tokens as $token) {
            switch($token) {
                case "[": 
                    // ... open a new substack
                    if ($current_stack !== null)
                        array_push($stack, $current_stack);
                    $current_stack = array();
                    break;

                case "]":
                    // ... close the most recent substack
                    if ($current_stack === null)
                        throw new Exception("Syntax error: invalid closed expression.");
                    
                    $stack[] = $current_stack;
                    $current_stack = null;
                    break;

                case ";":
                    // ... execute stack
                    $e = self::translate($stack) . ";\n";
                    // echo $e;
                    eval($e);
                    $stack = array();
                    break;

                default:
                    if ($current_stack === null)
                        $stack[] = $token;
                    else
                        $current_stack[] = $token;
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
        $expr = preg_replace("/set\(([^,]+),([^)]+)\)/","\$1 =\$2", $expr);

        // special case (class definition)
        $expr = preg_replace("/class\(([^)]+)\)/","class \$1 extends Prototype {}", $expr);

        // special case (object instantiation)
        $expr = preg_replace("/new\(([^,]+),([^,]+),([^)]+)\)/","\$1 = new \$2(\$3 )", $expr);

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
}

// ---------- start -------------
if (count($argv) < 2) 
    die("usage: php forth.php <filename>\n");

$prog = file_get_contents( $argv[1] );
Parser::evaluate(Parser::tokenize($prog));


