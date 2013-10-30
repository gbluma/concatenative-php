<?php
namespace Concatenative;

require_once("src/Concatenative/language.php");

/**
 * @backupGlobals disabled
 */
class CoreLanguageTest extends \PHPUnit_Framework_TestCase 
{
    public function test_function_definition_cleans_up_stack()
    {
        global $stack;

        read(": test ( -- ) 3 ;"); 
        $this->assertEmpty($stack);

        read(": test ( -- ) [ dup dup * * ] ;"); 
        $this->assertEmpty($stack);

        read(": test ( -- ) 300 iota ;"); 
        $this->assertEmpty($stack);
    }

    public function test_function_exists_after_definition() 
    {
        global $stack, $funcs;

        $this->assertFalse( isset($funcs['testing']) );
        read(": testing ( -- ) 3 ;"); 
        $this->assertTrue(  isset($funcs['testing']) );
    }

}

