<?php

namespace DalTest\Service;

use PHPUnit_Framework_TestCase;

class AbstractServiceTest extends PHPUnit_Framework_TestCase
{
    public function testCallUsePaginator()
    {
        $mock_mapper = $this->getMockForAbstractClass('\Dal\Mapper\AbstractMapper', array(), '', false, true, true, array('usePaginator'));
        $mock_mapper->expects($this->once())->method('usePaginator')->with(array('toto'));

        $mock_service_locator_interface = $this->getMockForAbstractClass('\Zend\ServiceManager\ServiceLocatorInterface');
        $mock_service_locator_interface->expects($this->any())->method('get')->will($this->returnValue($mock_mapper));

        $mock = $this->getMockForAbstractClass('\Dal\Service\AbstractService');
        $mock->setContainer($mock_service_locator_interface);
        $out = $mock->usePaginator(array('toto'));

        $this->assertEquals($mock->getMapper(), $mock_mapper);
    }

    public function testCallModel()
    {
        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(), '', false, true, true, array());

        $mock_service_locator_interface = $this->getMockForAbstractClass('\Zend\ServiceManager\ServiceLocatorInterface');
        $mock_service_locator_interface->expects($this->once())->method('get')->with('model-property')->will($this->returnValue($mock_model));

        $mock = $this->getMockForAbstractClass('\Dal\Service\AbstractService');
        $mock->setContainer($mock_service_locator_interface);

        $reflexionClass = new \ReflectionClass($mock);
        $model = $reflexionClass->getProperty('model');
        $model->setAccessible(true);
        $model->setValue($mock, 'model-property');

        $this->assertEquals($mock->getModel(), $mock_model);
    }

    public function testCallMapper()
    {
        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(), '', false, true, true, array());

        $mock_service_locator_interface = $this->getMockForAbstractClass('\Zend\ServiceManager\ServiceLocatorInterface');
        $mock_service_locator_interface->expects($this->once())->method('get')->with('mapper-property')->will($this->returnValue($mock_model));

        $mock = $this->getMockForAbstractClass('\Dal\Service\AbstractService');
        $mock->setContainer($mock_service_locator_interface);

        $reflexionClass = new \ReflectionClass($mock);
        $mapper = $reflexionClass->getProperty('mapper');
        $mapper->setAccessible(true);
        $mapper->setValue($mock, 'mapper-property');

        $this->assertEquals($mock->getMapper(), $mock_model);
    }
}
