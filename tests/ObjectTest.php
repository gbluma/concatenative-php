<?php

/**
 * @backupGlobals disabled
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{
    public function test_object_creation() 
    {
        Concatenative\read("
            Document class
            : my-document ( -- doc ) 
              { 'title'  => \"An introduction to Concatenative PHP\" 
                'author' => \"Garrett Bluma\"
                'hiFive' => [ \"Hi Five\" echo ] 
              } Document new ;
        ");
    }
}

