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
function pop_back_to($x) { 
    global $stack, $funcs; 
    $words = array();
    while( count($stack) > 0 ) {
        $word = pop();
        if ($word === $x) { return array_reverse($words); } 
        else { $words[] = $word; }
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
            case "'": $squote = !$squote; $word .= $c; break;
            case '"': $dquote = !$dquote; $word .= $c; break;
            default: $word .= $c;
        }
    }
    if (!empty($word)) { push($word); process($word); }
}

// ---------------- std library -----------------

$funcs[']'] = function() { 
    global  $in_definition;
    pop(); 
    $words = pop_back_to('[');
    push(function() use ($words) { return read(implode(" ", $words)); });
};
$funcs[':'] = function() { global $in_definition; $in_definition = true; };
$funcs[';'] = function() { 
    global $funcs, $in_definition;
    pop(); 
    $words = pop_back_to(':');
    $name = array_shift($words);
    $funcs[$name] = function() use ($words){ pop(); return read(implode(" ", $words)); };
    $in_definition = false;
};

$funcs[')'] = function() { pop(); $words = pop_back_to('('); };
$funcs['clear'] = function() { global $stack; $stack = array(); };
$funcs['call'] = function() { pop(); $a = pop(); $a(); };
$funcs['swap'] = function() { pop(); $a = pop(); $b = pop(); push($a); push($b); };
$funcs['dup'] = function() { pop(); $a = pop(); push($a); push($a); };
$funcs['+'] = function() { pop(); push(pop() + pop()); };
$funcs['-'] = function() { pop(); $a = pop(); $b = pop(); push($b - $a); };
$funcs['*'] = function() { pop(); push(pop() * pop()); };
$funcs['/'] = function() { pop(); $a = pop(); $b = pop(); push($b / $a); };
$funcs['.'] = function() { pop(); echo pop(); };


// start repl
while(true) {
    echo ">>> ";
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    read($line);
    echo "\n";
}







