<?php

/**
 * Concatenative-PHP, a lightweight functional language built on PHP.
 * Copyright (C) 2013  Garrett Bluma
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace new_parser;

$funcs = array();
$stack = array();
$defer = 0;

function process($word) { 
    global $stack, $funcs, $defer; 
    if ($word == ';' || $defer <= 0) {
        if (isset($funcs[$word])) $funcs[$word](); 
    }
}
function push($x) { global $stack, $funcs; return array_push($stack, $x); }
function pop() { 
    global $stack, $funcs; 
    if (count($stack) > 0) return array_pop($stack); 
    else throw new \Exception("unable to pop value from stack: stack empty");
}
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
    global $defer;
    $squote = $dquote = false;
    $word = '';
    foreach(str_split($str) as $c) {
        switch($c) {
            case "[": case "(": case ":": $defer++; $word .= $c; break;
            case "]": case ")": case ";": $defer--; $word .= $c; break;

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
        return read(implode(" ", $words)); 
    });
};
$funcs[';'] = function() { 
    global $funcs;
    pop(); 
    $words = pop_back_to(':',';');
    $name = array_shift($words);
    $funcs[$name] = function() use ($words){ pop(); return read(implode(" ", $words)); };
};
$funcs[')'] = function() { pop(); $words = pop_back_to('(',')'); };
$funcs['}'] = function() { 
    pop(); 
    $words = pop_back_to('{','}'); 
    if (in_array('=>', $words)) {
        // ... this is an associative array
        $output = array();
        for($i=0,$ii=count($words); $i<$ii; $i++) {
            if ($words[$i] == "=>") {
                if ($i-1 < 0 || $i+1 >= $ii) {
                    throw new \Exception("Syntax error on associative array (".implode(' ', $words).")");
                }
                $key = $words[$i-1];
                $value =  $words[$i+1];
                $output[$key] = $value;
            }
        }
        push($output);
    } else {
        push($words); 
    }
};
$funcs['var_dump'] = function() { pop(); var_dump(pop()); };
$funcs['.stack'] = function() { 
    global $stack; 
    pop();
    echo "\n----Stack----\n";;
    foreach($stack as $s) { echo var_export($s) ."\n"; }
};
$funcs['}FFI'] = function() { pop(); $words = pop_back_to('FFI{', '}FFI'); 
    push(function() use ($words) { eval("namespace new_parser; " . implode(" ", $words)); });  };


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
    echo <<<HERE

Concatenative-PHP  Copyright (C) 2013  Garrett Bluma
This program comes with ABSOLUTELY NO WARRANTY; for details read LICENSE.md.
This is free software, and you are welcome to redistribute it.


HERE;

    while(true) {
        echo ">>> ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        try {
            read($line);
            if (count($stack) > 0) read(".stack");
            else echo "\n";
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }
}

$prog = file_get_contents( $argv[1] );
read($prog);






