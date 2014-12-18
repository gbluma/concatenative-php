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

require_once("prototype.php");
require_once("interpreter_backend.php");

class EmptyStackException extends \Exception {}

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

function read_type($a) 
{
    $x = gettype($a);
    if ($x === 'object') return get_class($a);
    else return $x;
}

function read_effects($words) {
    $output = array();
    $capture = false;
    foreach($words as $word) {
        if ($word == "(") { $capture = true; continue; }
        if ($word == ")") { $capture = false; break; }
        $output[] = $word;
    }
    return $output;
}


//read( ": 2over ( x y z -- x y z x y ) pick pick ;" );

// load a file if we have one
if ( isset( $argv ) && count( $argv ) > 1 ) {
    $prog = file_get_contents( $argv[1] );
    read( $prog );
}






