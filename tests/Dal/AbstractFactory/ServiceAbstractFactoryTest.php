<?php

namespace DalTest\AbstractFactory;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Dal\AbstractFactory\ServiceAbstractFactory;

class ServiceAbstractFactoryTest extends AbstractHttpControllerTestCase
{
	public function setUp()
	{
		$this->setApplicationConfig(
				include __DIR__ . '/../../config/application.config.php'
		);
		parent::setUp();
	}
	
    public function testCanCreateServiceWithName()
    {
    	$service_abstract_factory = new ServiceAbstractFactory();
    	$serviceManager = $this->getApplicationServiceLocator();
    	$out = $service_abstract_factory->canCreateServiceWithName($serviceManager, 'dal-test_service_table', 'dal-test_service_table');
    	 
    	$this->assertTrue($out);
    }

    public function createServiceWithName()
    {
    	$service_abstract_factory = new ServiceAbstractFactory();
    	$serviceManager = $this->getApplicationServiceLocator();
    	$out = $service_abstract_factory->createServiceWithName($serviceManager, 'dal-test_model_table', 'dal-test_model_table');
    	
    	$this->assertInstanceOf('Mock\Service\Table' , $out);
    }
}
