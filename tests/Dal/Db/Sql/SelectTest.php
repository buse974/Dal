<?php

namespace DalTest\Db\Sql;

use \PHPUnit_Framework_TestCase;
use Dal\Db\Sql\Select;

/**
 * 
 * Test columns and join depends of testAliasing methode
 *
 */
class SelectTest extends PHPUnit_Framework_TestCase
{
    public function testAliasing()
    {
    	$cloums = array('colum1','colum2','*');
    	
    	$select = new Select();
    	
    	$reflectionClass = new \ReflectionClass($select);
    	$reflectionMethod = $reflectionClass->getMethod('aliasing');
    	$reflectionMethod->setAccessible(true);
    	
    	$out = $reflectionMethod->invokeArgs($select, array($cloums, array('prefix'=>'prefixname')));
    	
    	$this->assertArrayNotHasKey('prefix$*', $out);
    	$this->assertArrayHasKey('prefix$colum1', $out);
    	$this->assertArrayHasKey('prefix$colum2', $out);
    	$this->assertEquals('colum1', $out['prefix$colum1']);
    	$this->assertEquals('colum2', $out['prefix$colum2']);
    }
}
