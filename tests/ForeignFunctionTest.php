<?php

namespace Concatenative;

require_once ("src/Concatenative/language.php");

/**
 * @backupGlobals disabled
 */
class ForeignFunctionTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
    	read( "clear" );
    } 
    
    function test_FFI_basic() 
    {
        global $stack, $funcs;
        read(": test ( -- ) FFI{ push(5); }FFI ;");
        $this->assertTrue( isset($funcs['test'] ), "function not added to funcs" );
        $this->assertTrue( is_callable($funcs['test'] ), "function is not callable" );
        $this->assertEmpty( $stack, "stack is not empty" );
    }

    function test_FFI_should_be_callable() 
    {
        global $stack, $funcs;
        read(": ffi_test ( -- ) FFI{ push(5); }FFI ;");
        read(".stack");
        //$this->assertTrue( isset($stack[0]), "stack[0] should not be empty" );
        //$this->assertEquals(5, $stack[0], "stack[0] == 5");
    }
    
    function test_FFI_accumulation() 
    {
        global $stack;

        read("FFI{ echo \"hello\"; push(5); }FFI");
        $this->assertNotEmpty($stack);
    }

}