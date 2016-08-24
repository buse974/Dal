<?php

namespace DalTest\Stdlib\Hydrator;

use PHPUnit_Framework_TestCase;
use Dal\Stdlib\Hydrator\ClassMethods;

class ClassMethodsTest extends PHPUnit_Framework_TestCase
{
    public function testextract()
    {
        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['getToto', 'getTata'])
            ->getMock();
        
            $mock->expects($this->any())->method('getToto')->will($this->returnValue('vtoto'));
            $mock->expects($this->any())->method('getTata')->will($this->returnValue('vtata'));

        $classmethode = new ClassMethods();
        $out = $classmethode->extract($mock);

        $this->assertTrue(isset($out['toto']));
        $this->assertTrue(isset($out['tata']));
        $this->assertEquals($out['toto'], 'vtoto');
        $this->assertEquals($out['tata'], 'vtata');
    }

    public function testHydrate()
    {
        $datas = array('toto' => 'vtoto', 'tata' => 'vtata');

        $mock = $this->getMockBuilder('stdClass')
            ->setMethods(['setToto', 'setTata'])
            ->getMock();
        
        $mock->expects($this->any())->method('setToto')->with($this->equalTo('vtoto'))->will($this->returnSelf());
        $mock->expects($this->any())->method('setTata')->with($this->equalTo('vtata'))->will($this->returnSelf());

        $classmethode = new ClassMethods();
        $classmethode->hydrate($datas, $mock);
    }
}
