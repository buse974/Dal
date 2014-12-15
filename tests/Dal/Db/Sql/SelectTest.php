<?php

namespace DalTest\Db\Sql;

use PHPUnit_Framework_TestCase;
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

        $out = $reflectionMethod->invokeArgs($select, array($cloums, array('prefix' => 'prefixname')));

        $this->assertArrayNotHasKey('prefix$*', $out);
        $this->assertArrayHasKey('prefix$colum1', $out);
        $this->assertArrayHasKey('prefix$colum2', $out);
        $this->assertEquals('colum1', $out['prefix$colum1']);
        $this->assertEquals('colum2', $out['prefix$colum2']);
    }
    
    public function testColumns()
    {
    	$select = $this->getMockBuilder('Dal\Db\Sql\Select')
    	               ->disableOriginalConstructor()
    	               ->setMethods(array('aliasing'))
    	               ->getMock();
    	
    	$reflection = new \ReflectionClass('Dal\Db\Sql\Select');
    	$table = $reflection->getProperty('table');
    	$table->setAccessible(true);
    	$table->setValue($select, 'une_table');
    	
    	
    	$select->expects($this->once())
    	       ->method('aliasing')
    	       ->with(array('colums'),'une_table')
    	       ->will($this->returnValue(array('foo')));
    	
    	$this->assertEquals($select, $select->columns(array('colums'),false));
    }
    
    public function testJoin()
    {
    	$select = $this->getMockBuilder('Dal\Db\Sql\Select')
    	               ->disableOriginalConstructor()
    	               ->setMethods(array('aliasing'))
    	               ->getMock();
    	 
    	$select->expects($this->once())
    	       ->method('aliasing')
    	       ->with(array('colums'),'une_table')
    	       ->will($this->returnValue(array('foo')));
    	 
    	$this->assertEquals($select, $select->join('une_table', 'on', 'colums'));
    }
}
