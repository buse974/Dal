<?php

namespace DalTest\Service;

use \PHPUnit_Framework_TestCase;

class AbstractServiceTest extends PHPUnit_Framework_TestCase
{
	public function testCallMapper()
	{
		$mock_mapper = $this->getMockForAbstractClass('\Dal\Mapper\AbstractMapper', array(), '', false, true, true, array());

		$mock_service_locator_interface = $this->getMockForAbstractClass('\Zend\ServiceManager\ServiceLocatorInterface');
		$mock_service_locator_interface->expects($this->any())->method('get')->will($this->returnValue($mock_mapper));

		$mock = $this->getMockForAbstractClass('\Dal\Service\AbstractService');
		$mock->setServiceLocator($mock_service_locator_interface);
		$mock->usePaginator(array('toto'));
		
		$this->assertEquals($mock->getServiceLocator(),$mock_service_locator_interface);
		$this->assertEquals($mock->getMapper(),$mock_mapper);
	}
}
