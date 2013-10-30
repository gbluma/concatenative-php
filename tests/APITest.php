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
    
    public function test_var_dump()
    {
        ob_start();
        read("{ 1 2 } var_dump");
        $a = ob_get_contents();
        ob_end_clean();
        
        ob_start();
        var_dump( array((string)"1",(string)"2") );  // should this really be a string?
        $b = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals($b, $a);
    }

    public function test_println()
    {
        ob_start();
        read("\"hello world\" println");
        $a = ob_get_contents();
        ob_end_clean();
        
        ob_start();
        echo "\"hello world\"\n"; // should this have quotes around it?
        $b = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals($b, $a);
    }
    
    public function test_cond()
    {
        global $stack;
        read('
          { rtf => "Rich Text Editor" 
            otf => "Open Type Document Format"
            doc => "Microsoft Word"
          } otf cond
        ');
        $this->assertEquals(1, count($stack));
        $this->assertEquals("\"Open Type Document Format\"", $stack[0]); // shouldn't really have quotes.
    }
    
    public function test_reduce_sum()
    {
        global $stack;
        read('30 iota [ + ] reduce');

        $this->assertEquals(1, count($stack));
        $this->assertEquals(435, $stack[0]);
    }

    public function test_reduce_concat()
    {
        global $stack;
        read('10 iota [ ++ ] reduce');

        $this->assertEquals(1, count($stack));
        $this->assertEquals("0123456789", $stack[0]);
    }
    
    public function test_arith_plus()
    {
        global $stack;
        read('3 3 +');

        $this->assertEquals(1, count($stack));
        $this->assertEquals("6", $stack[0]);
    }
    
    public function test_arith_subtract()
    {
        global $stack;
        read('30 10 -');

        $this->assertEquals(1, count($stack));
        $this->assertEquals("20", $stack[0]);
    }

    public function test_arith_multiply()
    {
        global $stack;
        read('5 4 *');

        $this->assertEquals(1, count($stack));
        $this->assertEquals("20", $stack[0]);
    }

    public function test_arith_divide()
    {
        global $stack;
        read('30 3 /');

        $this->assertEquals(1, count($stack));
        $this->assertEquals("10", $stack[0]);
    }

    public function test_arith_mod()
    {
        global $stack;
        read('7 5 mod');

        $this->assertEquals(1, count($stack));
        $this->assertEquals("2", $stack[0]);
    }

    public function test_length()
    {
        global $stack;
        read('{ 1 2 3 4 5 } length');

        $this->assertEquals(1, count($stack));
        $this->assertEquals("5", $stack[0]);
    }

    public function test_max()
    {
        global $stack;
        read('5 4 max ');

        $this->assertEquals(1, count($stack));
        $this->assertEquals("5", $stack[0]);
    }

    public function test_drop()
    {
        global $stack;
        read('1 2 3 4 5 drop');
        $this->assertEquals(4, count($stack));
    }

    public function test_2drop()
    {
        global $stack;
        read('1 2 3 4 5 2drop');
        $this->assertEquals(3, count($stack));
    }
    
}