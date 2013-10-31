<?php

namespace Concatenative;

require_once ("src/Concatenative/language.php");

/**
 * @backupGlobals disabled
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    public function test_type_access() 
    {
        global $funcs, $types, $stack;

        read(": type1 ( int -> int ) dup * ;");
        $this->assertEquals( array('int', '->', 'int'), $types['type1'] );

    }
}

