<?php

namespace Concatenative;

$funcs = array();
$types = array();

$funcs[']'] = function () {
    pop();
    $words = pop_back_to( '[', ']' );
    push( function () use($words) {
        return read( implode( " ", $words ) );
    });
};
$funcs[';'] = function () {
    global $funcs, $types;
    pop();
    $words = pop_back_to( ':', ';' );
    $name = array_shift( $words );
    $type = read_effects( $words );
    $types[$name] = $type;
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


