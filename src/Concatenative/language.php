<?php

/**
 * Concatenative-PHP, a lightweight functional language built on PHP.
 * Copyright (C) 2013 Garrett Bluma
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Concatenative;

require_once ("prototype.php");

class EmptyStackException extends \Exception {}

$funcs = array();
$stack = array();
$defer = 0;

function process($word)
{
    global $stack, $funcs, $defer;
    if ( $word == ';' || $defer <= 0 ) {
        if ( isset( $funcs[$word] ) ) {
            $funcs[$word]();
        }
    }
}

function push($x)
{
    global $stack, $funcs;
    return $stack[] = $x;
}

function pop()
{
    global $stack, $funcs;
    if ( count( $stack ) > 0 ) { 
        return array_pop( $stack );
    } else {
        throw new EmptyStackException( "unable to pop value from stack: stack empty" );
    }
}

function pop_back_to($down, $up)
{
    global $stack, $funcs;
    $words = array();
    $counter = 1;
    while( count( $stack ) > 0 ) {
        $word = pop();
        if ( $word === $down ) {
            $counter--;
            if ( $counter <= 0 ) {return array_reverse( $words );}
        } else if ( $word === $up ) {
            $counter++;
        }
        $words[] = $word;
    }
    return array_reverse( $words );
}

function read($str)
{
    global $defer;
    $squote = $dquote = $inFFI = false;
    $word = '';
    foreach ( str_split( $str ) as $c ) {
        switch ( $c )
        {
            case "[":
            case "(":
            case ":":
                if ( !$squote && !$dquote && !$inFFI ) {
                    $defer++;
                }
                $word .= $c;
                break;
            case "]":
            case ")":
            case ";":
                if ( !$squote && !$dquote && !$inFFI ) {
                    $defer--;
                }
                $word .= $c;
                break;
            
            case " ":
            case "\r":
            case "\n":
                if ( !$squote && !$dquote && !$inFFI ) {
                    if ( !empty( $word ) ) {
                        push( $word );
                        process( $word );
                        $word = '';
                    }
                } else {
                    if ($word == "FFI{") $inFFI = true;
                    if ($word == "}FFI") { $inFFI = false; push($word); process($word); }
                    $word .= $c;
                }
                break;
            // case "'": $squote = !$squote; break;
            case '"':
                $dquote = !$dquote;
                $word .= $c;
                break;
            
            default :
                $word .= $c;
        }
    }
    if ( !empty( $word ) ) {
        push( $word );
        process( $word );
    }
}

// ---------------- std library -----------------

$funcs[']'] = function () {
    pop();
    $words = pop_back_to( '[', ']' );
    push( function () use($words) {
        return read( implode( " ", $words ) );
    });
};
$funcs[';'] = function () {
    global $funcs;
    pop();
    $words = pop_back_to( ':', ';' );
    $name = array_shift( $words );
    $funcs[$name] = function () use($words) {
        pop();
        return read( implode( " ", $words ) );
    };
};
$funcs[')'] = function () {
    pop();
    $words = pop_back_to( '(', ')' );
};
$funcs['}'] = function () {
    pop();
    $words = pop_back_to( '{', '}' );
    if ( in_array( '=>', $words ) ) {
        // ... this is an associative array
        $output = array();
        for ($i = 0, $ii = count( $words ); $i < $ii; $i++) {
            if ( $words[$i] == "=>" ) {
                if ( $i - 1 < 0 || $i + 1 >= $ii ) {
                    throw new \Exception( "Syntax error on associative array (" . implode( ' ', $words ) . ")" );}
                $key = $words[$i - 1];
                $value = $words[$i + 1];
                $output[$key] = $value;
            }
        }
        push( $output );
    } else {
        push( $words );
    }
};

$funcs['var_dump'] = function () {
    pop();
    var_dump( pop() );
};
$funcs['println'] = function () {
    pop();
    echo (pop() . "\n");
};

$funcs['.stack'] = function () {
    global $stack;
    pop();
    echo "\n----Stack----\n";
    ;
    foreach ( $stack as $s ) {
        echo var_export( $s ) . "\n";
    }
};
$funcs['}FFI'] = function () {
    pop();
    $words = pop_back_to( 'FFI{', '}FFI' );
    push(function () use($words)
    {
        return eval( "namespace Concatenative; " . implode( " ", $words ) );
    });
};

$funcs['load'] = function () {
    pop();
    $c = file_get_contents( pop() );
    read( $c );
};
$funcs['clear'] = function () {
    global $stack;
    $stack = array();
};
$funcs['cond'] = function () {
    pop();
    $key = pop();
    $dict = pop();
    push( $dict[$key] );
    ;
};
$funcs['>'] = function() {
    pop();
    push(pop() < pop());
};
$funcs['>='] = function() {
    pop();
    push(pop() <= pop());
};
$funcs['<'] = function() {
    pop();
    push(pop() > pop());
};
$funcs['<='] = function() {
    pop();
    push(pop() >= pop());
};
$funcs['=='] = function() {
    pop();
    push(pop() === pop());
};
$funcs['if'] = function() {
    pop();
    $else = pop();
    $if = pop();
    $cond = pop();
    if ($cond) $if(); else $else();
};
$funcs['call'] = function () {
    pop();
    $a = pop();
    $a();
};
$funcs['swap'] = function () {
    pop();
    $a = pop();
    $b = pop();
    push( $a );
    push( $b );
};
$funcs['dup'] = function () {
    pop();
    $a = pop();
    push( $a );
    push( $a );
};
$funcs['over'] = function () {
    pop();
    $b = pop();
    $a = pop();
    push( $a );
    push( $b );
    push( $a );
};
$funcs['pick'] = function () {
    pop();
    $z = pop();
    $y = pop();
    $x = pop();
    push( $x );
    push( $y );
    push( $z );
    push( $x );
};
$funcs['rot'] = function () {
    pop();
    $z = pop();
    $y = pop();
    $x = pop();
    push( $y );
    push( $z );
    push( $x );
};
$funcs['-rot'] = function () {
    pop();
    $z = pop();
    $y = pop();
    $x = pop();
    push( $z );
    push( $x );
    push( $y );
};
$funcs['2dup'] = function () {
    pop();
    $a = pop();
    $b = pop();
    push( $b );
    push( $a );
    push( $b );
    push( $a );
};
$funcs['drop'] = function () {
    pop();
    pop();
};
$funcs['2drop'] = function () {
    pop();
    pop();
    pop();
};
$funcs['+'] = function () {
    pop();
    push( pop() + pop() );
};
$funcs['-'] = function () {
    pop();
    $a = pop();
    $b = pop();
    push( $b - $a );
};
$funcs['*'] = function () {
    pop();
    push( pop() * pop() );
};
$funcs['/'] = function () {
    pop();
    $a = pop();
    $b = pop();
    push( $b / $a );
};
$funcs['mod'] = function () {
    pop();
    $a = pop();
    $b = pop();
    push( $b % $a );
};
$funcs['.'] = function () {
    pop();
    echo pop();
};
$funcs['echo'] = $funcs['.'];
$funcs['length'] = function ()
{
    pop();
    push( count( pop() ) );
};
$funcs['max'] = function () {
    pop();
    $b = pop();
    $a = pop();
    push( ($a > $b) ? $a : $b );
};
$funcs['reduce'] = function () {
    global $stack;
    pop();
    $op = pop();
    $ii = count( $stack ) - 1;
    for ($i = 0; $i < $ii; $i++) {
        push( $op );
        read( "call" );
    }
};
$funcs['++'] = function () {
    pop();
    $b = pop();
    $a = pop();
    push( $a . $b );
};
$funcs['concat'] = $funcs['++'];
$funcs['iota'] = function ()
{
    pop();
    for ($i = 0, $ii = pop(); $i < $ii; $i++) {
        push( $i );
    }
};

$funcs['class'] = function () {
    pop();
    $name = pop();
    eval( "class $name extends Concatenative\Prototype {}" );
};
$funcs['new'] = function ()
{
    pop();
    $classname = pop();
    $internals = pop();
    push( new $classname( $internals ) );
};

read( ": 2over ( x y z -- x y z x y ) pick pick ;" );

// load a file if we have one
if ( isset( $argv ) && count( $argv ) > 1 ) {
    $prog = file_get_contents( $argv[1] );
    read( $prog );
}






