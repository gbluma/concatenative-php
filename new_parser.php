<?php

namespace new_parser;

$funcs = array();
$stack = array();
$in_definition = false;

function process($word) { 
    global $stack, $funcs, $in_definition; 
    if ($word == ';' or !$in_definition) 
        if (isset($funcs[$word])) $funcs[$word](); 
}
function push($x) { global $stack, $funcs; return array_push($stack, $x); }
function pop() { global $stack, $funcs; return array_pop($stack); }
function pop_back_to($down,$up) { 
    global $stack, $funcs; 
    $words = array();
    $counter = 1;
    while( count($stack) > 0 ) {
        $word = pop();
        if ($word === $down) {
            $counter--;
            if ($counter <= 0) { return array_reverse($words); } 
        } else if ($word === $up) { $counter++; }
        $words[] = $word; 
    }
    return array_reverse($words);
}

function read($str) {
    $squote = $dquote = false;
    $word = '';
    foreach(str_split($str) as $c) {
        switch($c) {
            case " ": 
            case "\r": 
            case "\n": 
                if (!$squote && !$dquote) {
                    if (!empty($word)) { push($word); process($word); } 
                    $word = ''; 
                } else { $word .= $c; }
                break;
            //case "'": $squote = !$squote; $word .= $c; break;
            case '"': $dquote = !$dquote; $word .= $c; break;
            default: $word .= $c;
        }
    }
    if (!empty($word)) { push($word); process($word); }
}

// ---------------- std library -----------------

$funcs[']'] = function() { 
    pop(); 
    $words = pop_back_to('[', ']');
    push(function() use ($words) { 
        echo "quot: " . implode(" ", $words) . "\n"; 
        return read(implode(" ", $words)); 
    });
};
$funcs[':'] = function() { global $in_definition; $in_definition = true; };
$funcs[';'] = function() { 
    global $funcs, $in_definition;
    pop(); 
    $words = pop_back_to(':',';');
    $name = array_shift($words);
    $funcs[$name] = function() use ($words){ pop(); return read(implode(" ", $words)); };
    $in_definition = false;
};
$funcs[')'] = function() { pop(); $words = pop_back_to('(',')'); };
$funcs['}'] = function() { pop(); $words = pop_back_to('{','}'); push($words); };
$funcs['var_dump'] = function() { pop(); var_dump(pop()); };
$funcs['.stack'] = function() { 
    global $stack; 
    pop();
    echo "\n----Stack----\n";;
    foreach($stack as $s) { echo var_export($s) ."\n"; }
};
$funcs['}FFI'] = function() { pop(); eval("namespace new_parser; " . implode(" ", pop_back_to('FFI{', '}FFI')));  };


$funcs['clear'] = function() { global $stack; $stack = array(); };
$funcs['call'] = function() { pop(); $a = pop(); $a(); };
$funcs['swap'] = function() { pop(); $a = pop(); $b = pop(); push($a); push($b); };
$funcs['dup'] = function() { pop(); $a = pop(); push($a); push($a); };
$funcs['over'] = function() { pop(); $b = pop(); $a = pop(); push($a); push($b); push($a); };
$funcs['pick'] = function() { pop(); $z = pop(); $y = pop(); $x = pop(); push($x); push($y); push($z); push($x); };
$funcs['rot'] = function() { pop(); $z = pop(); $y = pop(); $x = pop(); push($y); push($z); push($x); };
$funcs['-rot'] = function() { pop(); $z = pop(); $y = pop(); $x = pop(); push($z); push($x); push($y); };
$funcs['2dup'] = function() { pop(); $a = pop(); $b = pop(); push($b); push($a); push($b); push($a); };
$funcs['drop'] = function() { pop(); pop(); };
$funcs['2drop'] = function() { pop(); pop(); pop(); };
$funcs['+'] = function() { pop(); push(pop() + pop()); };
$funcs['-'] = function() { pop(); $a = pop(); $b = pop(); push($b - $a); };
$funcs['*'] = function() { pop(); push(pop() * pop()); };
$funcs['/'] = function() { pop(); $a = pop(); $b = pop(); push($b / $a); };
$funcs['mod'] = function() { pop(); $a = pop(); $b = pop(); push($b % $a); };
$funcs['.'] = function() { pop(); echo pop(); };

read(": 2over ( x y z -- x y z x y ) pick pick ;");

// start repl
if (count($argv) < 2) {
    while(true) {
        echo ">>> ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        read($line . " .stack" );
    }
}

$prog = file_get_contents( $argv[1] );
read($prog);






