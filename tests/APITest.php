<?php

namespace Concatenative;

require_once ("src/Concatenative/language.php");

/**
 * @backupGlobals disabled
 */
class APITest extends \PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        read( "clear" );
    }

    public function test_swap()
    {
        global $stack;
        read("3 1 swap");
        $this->assertGreaterThan(1, count($stack));
        $this->assertEquals(1, $stack[0]);
        $this->assertEquals(3, $stack[1]);
    }

    public function test_dup()
    {
        global $stack;
        read("1 dup");
        $this->assertGreaterThan(1, count($stack));
        $this->assertEquals(1, $stack[0]);
        $this->assertEquals(1, $stack[1]);
    }
    
    
}