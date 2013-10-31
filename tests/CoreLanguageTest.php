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
    
    public function test_array_creation() {
    	global $stack;
    	read("{ 1 2 3 }");
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertEquals( array(1,2,3), $stack[0] );
    }

    public function test_nested_array_creation() {
    	global $stack;
    	read("{ 1 { 5 5 2 } 3 }");
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertEquals( array(1, array(5,5,2),3), $stack[0] );
    }

    public function test_assoc_array_creation() {
    	global $stack;
    	read("{ A => B }");
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertEquals( array('A' => 'B'), $stack[0] );
    }

    public function test_multi_assoc_array_creation() {
    	global $stack;
    	read("{ A => B 
                C => D }");
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertEquals( array('A' => 'B', 'C' => 'D'), $stack[0] );
    }

    public function test_nested_multi_assoc_array_creation() {
    	global $stack;
    	read("{ A => { E => F } 
                C => D }");
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertEquals( array('A' => array('E' => 'F'), 'C' => 'D'), $stack[0] );
    }

    public function test_quot_in_assoc_array() {
    	global $stack;
    	read("{ A => [ dup * ]
                C => D }");
        $this->assertTrue( count( $stack ) === 1 );
        $this->assertTrue( isset($stack[0]['A']) );
        $this->assertTrue( is_callable($stack[0]['A']) );
        $this->assertTrue( isset($stack[0]['C']) );
        $this->assertEquals('D', $stack[0]['C'] );
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
    
    public function test_double_quotes() 
    {
        global $stack;
        read( "\"hello there\"" );
        $this->assertEquals(1, count( $stack ) );
        $this->assertEquals("\"hello there\"", $stack[0]);
    }

    public function test_double_quotes_with_colon() 
    {
        global $stack;
        read( "\"hello: there\"" );
        $this->assertEquals(1, count( $stack ) );
        $this->assertEquals("\"hello: there\"", $stack[0]);
    }

    public function test_double_quotes_with_semicolon() 
    {
        global $stack;
        read( "\"hello; there\"" );
        $this->assertEquals(1, count( $stack ) );
        $this->assertEquals("\"hello; there\"", $stack[0]);
    }
    
    /**
     * @expectedException Concatenative\EmptyStackException
     */
    public function test_that_pop_throws_error_on_empty_stack()
    {
        read("."); // should pop one off the stack
    }
}

