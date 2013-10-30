<?php

function read_type($a) {
    $x = gettype($a);
    if ($x === 'object') return get_class($a);
    else return $x;
}

class A {}
$a = new A();

echo read_type(5)."\n";
echo read_type("str")."\n";
echo read_type(array())."\n";
echo read_type($a)."\n";
