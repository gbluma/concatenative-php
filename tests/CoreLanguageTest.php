<?php

namespace Concatenative;

require_once ("src/Concatenative/language.php");

/**
 * @backupGlobals disabled
 */
class CoreLanguageTest extends \PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        read( "clear" );
    }

    public function test_function_definition_cleans_up_stack()
    {
        global $stack;
        
        read( ": test ( -- ) 3 ;" );
        $this->assertEmpty( $stack );
        
        read( ": test ( -- ) [ dup dup * * ] ;" );
        $this->assertEmpty( $stack );
        
        read( ": test ( -- ) 300 iota ;" );
        $this->assertEmpty( $stack );
    }

    public function test_tokenization()
    {
        global $stack;
        
        $this->assertEquals( 0, count( $stack ) );
        read( "3" );
        $this->assertEquals( 1, count( $stack ) );
        read( "3" );
        $this->assertEquals( 2, count( $stack ) );
        read( "3 3" );
        $this->assertEquals( 4, count( $stack ) );
    }

    public function test_that_clear_works()
    {
        global $stack;
        read( "3 3" );
        $this->assertEquals( 2, count( $stack ) );
        read( "clear" );
        $this->assertEquals( 0, count( $stack ) );
    }

    public function test_function_exists_after_definition()
    {
        global $stack, $funcs;
        
        $this->assertFalse( isset( $funcs['testing'] ) );
        read( ": testing ( -- ) 3 ;" );
        $this->assertTrue( isset( $funcs['testing'] ) );
    }

    public function test_function_application()
    {
        global $stack;
        read( ": square ( -- ) dup * ; " );
        read( "15 square" );
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertEquals( 225, $stack[0] );
    }

    public function test_stack_is_still_empty_after_teardown()
    {
        global $stack;
        $this->assertEmpty( $stack );
    }

    public function test_that_quotation_is_callable()
    {
        global $stack;
        read( "[ dup dup * * ]" );
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertTrue( is_callable( $stack[0] ) );
    }

    public function test_that_quotation_executionn()
    {
        global $stack;
        read( "3 [ dup dup * * ] call " );
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertTrue( $stack[0] === 27 );
    }
}

