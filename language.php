<?php


/* TODO:
 * - Conditionals
 * - Scoped let-binding
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

                case '[':
                    if (!$isInComment && !$isInSingleQuote && !$isInDoubleQuote) { 
                        $tokens[] = '{';
                        // ... do nothing
                    }
                    break;

                case ']':
                    if (!$isInComment && !$isInSingleQuote && !$isInDoubleQuote) { 
                        // yep... pretty messed up.
                        $tokens[] = '}';
                        $tokens[] = 'lambda';
                        $tokens[] = '|>';
                    }
                    break;

                default:
                    if (!$isInComment) { $currentToken .= $c; }
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
        $thunkCounter = 0;

        $isPHP = false;
        $inLambda = false;
        $rawPHP = "";

        foreach($tokens as $token) {
            switch($token) {

                case "[":
                    $inLambda = true;
                    // no break (continue with '[' case)
                    
                case "{": 
                    // ... open a new substack
                    $level++;
                    if ($stack !== null) {
                        $tmp[] = $stack;
                    }
                    $stack = array();
                    break;

                case "]":
                    $inLambda = false;
                    // no break (continue with ']' case)
                    
                case "}":
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

                    $e = self::translate($stack) . "; \n";

                    // is this expression at the top level?
                    if ($level == 0) {
                        // ... yes, top level expressions can be executed
                        $e = $rawPHP . $e;
                        $rawPHP = '';
                        //echo "###{$e}###\n"; // debugging
                        eval($e);
                        $stack = array();
                    } else {
                        // ... inner blocks are delayed, don't directly execute.
                        $thunkCounter += 1;
                        $rawPHP .= "\$GLOBALS['thunks']['t$thunkCounter'] = function(\$args=array()) { extract(\$args); return $e };\n" ;
                        $stack = array("Prelude::evalThunk(\$GLOBALS['thunks']['t$thunkCounter'], \$args)");
                    }
                    break;

                case "|>":
                    // ... special execute
                    $e = self::translate($stack);
                    $stack = array($e);
                    break;

                default:
                    if ($isPHP) 
                        $rawPHP .= $token;
                    else
                        $stack[] = $token;
            }
        }

        return $stack;
    }

    /**
     * Handle some details of converting parser data to PHP..
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
     * convert the function syntax `let(x,y)` to `x = y`.)
     */
    public static function translate($stack) 
    {
        $stack = array_reverse($stack);
        $func = array_shift($stack);
        $expr = "$func(" . self::export($stack) . ")";

        // special case (arithmatic)
        $expr = preg_replace("/\*\((.*)\)/", 'Prelude::product($1) ', $expr);
        $expr = preg_replace("/\+\((.*)\)/", 'Prelude::sum($1) ', $expr);
        $expr = preg_replace("/\-\((.*)\)/", 'Prelude::subtraction($1) ', $expr);
        $expr = preg_replace("/\/\((.*)\)/", 'Prelude::division($1) ', $expr);

        // special case (assignment)
        $expr = preg_replace("/let\(([^,]+),(.*)\)/", '$1 =$2', $expr);

        // special case (class definition)
        $expr = preg_replace("/class\(([^)]+)\)/", 'class $1 extends Prototype {}', $expr);

        // special case (object instantiation)
        $expr = preg_replace("/new\(([^,]+),([^,]+),([^)]+)\)/", '$1 = new $2($3 )', $expr);

        // special case (anonymous functions)
        $expr = preg_replace("/function\(([^,]+),(.*)\)/", '$1 = function ($args=array()) { extract($args); return $2; }', $expr);

        // special case (tru lambdas functions)
        $expr = preg_replace("/lambda\((.*)\)/", 'function ($args=array()) { extract($args); return $1; }', $expr);

        // special case (return)
        $expr = preg_replace("/echo\(/", 'Prelude::printIt(', $expr);

        // special case (return)
        $expr = preg_replace("/return\((.*)\)/", 'return ($1) ', $expr);

        // special case (concatenatino)
        $expr = preg_replace("/\.\((.*)\)/", 'Prelude::concat($1)', $expr);

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

    public static function cond( $target, $arr ) {
        foreach($arr as $key => $val) {
            if ($target == $key) {
                return $val;
            }
        }
        return null;
    }

    public static function product() {
        $args = func_get_args();
        $prod = 1;
        foreach($args as $arg) {
            $prod *= $arg;
        }
        return $prod;
    }

    public static function sum() {
        $args = func_get_args();
        $sum = 0;
        foreach($args as $arg) {
            $sum += $arg;
        }
        return $sum;
    }

    public static function subtraction() {
        $args = func_get_args();
        $sum = 0;
        foreach($args as $arg) {
            $sum -= $arg;
        }
        return $sum;
    }

    public static function division() {
        $args = func_get_args();
        $prod = 1;
        foreach($args as $arg) {
            $prod /= $arg;
        }
        return $prod;
    }

    public static function evalThunk($f, $args) {
        if (is_callable($f)) {
          $r = $f($args);
          return Prelude::evalThunk($r, $args);
        } else {
          return $f;
        }
    }

    public static function ignore($args) {
        return;
    }
}


// ---------- start -------------
if (count($argv) < 2) {
    while(true) {
        echo ">>> ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        Parser::evaluate(Parser::tokenize($line));
        echo "\n";
    }
    exit(0);
}

$prog = file_get_contents( $argv[1] );
Parser::evaluate(Parser::tokenize($prog));


