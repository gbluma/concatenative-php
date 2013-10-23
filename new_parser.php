<?php

namespace new_parser;

$funcs = array();
$stack = array();
$in_definition = false;

function process($word) { global $stack, $funcs; if (isset($funcs[$word])) $funcs[$word](); }
function push($x) { global $stack, $funcs; return array_push($stack, $x); }
function pop() { global $stack, $funcs; return array_pop($stack); }
function pop_back_to($x) { global $stack, $funcs; 
    $words = array();
    while( count($stack) > 0 ) {
        $word = pop();
        if ($word === $x) { return $words; } 
        else { $words[] = $word; }
    }
    return $words;
}

function read($str) {
    $word = '';
    foreach(str_split($str) as $c) {
        switch($c) {
            case ' ': if (!empty($word)) { push($word); process($word); } $word = ''; break;
            default:  $word .= $c;
        }
    }
    if (!empty($word)) { push($word); process($word); }
}

// ---------------- std library -----------------

$funcs[']'] = function() { 
    global  $in_definition;
    pop(); 
    if (!$in_definition) {
        $words = pop_back_to('[');
        push(function() use ($words) { return read(implode(" ", $words)); });
    }
};
$funcs[':'] = function() { global $in_definition; $in_definition = true; };
$funcs[';'] = function() { 
    global $funcs, $in_definition;
    pop(); 
    $words = pop_back_to(':');
    $name = array_pop($words);
    $funcs[$name] = function() use ($words){ pop(); return read(implode(" ", $words)); };
    $in_definition = false;
};
$funcs['call'] = function() { pop(); $a = pop(); $a(); };
$funcs['swap'] = function() { pop(); $a = pop(); $b = pop(); push($a); push($b); };
$funcs['dup'] = function() { pop(); $a = pop(); push($a); push($a); };
$funcs['+'] = function() { pop(); push(pop() + pop()); };
$funcs['-'] = function() { pop(); $a = pop(); $b = pop(); push($b - $a); };
$funcs['*'] = function() { pop(); push(pop() * pop()); };
$funcs['/'] = function() { pop(); $a = pop(); $b = pop(); push($b / $a); };

read(": a [ 5 2 / ] ;");
read("a");
var_dump($stack);
