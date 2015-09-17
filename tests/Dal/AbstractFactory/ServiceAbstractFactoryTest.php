<?php

namespace DalTest\AbstractFactory;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Dal\AbstractFactory\ServiceAbstractFactory;

class ServiceAbstractFactoryTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
                include __DIR__.'/../../config/application.config.php'
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

    public function testCreateServiceWithName()
    {
        $service_abstract_factory = new ServiceAbstractFactory();
        $serviceManager = $this->getApplicationServiceLocator();
        $out = $service_abstract_factory->createServiceWithName($serviceManager, 'dal-test_service_table', 'dal-test_service_table');

        $reflexionClass = new \ReflectionClass($out);
        $mapper = $reflexionClass->getProperty('mapper');
        $mapper->setAccessible(true);

        $model = $reflexionClass->getProperty('model');
        $model->setAccessible(true);

        $this->assertEquals('dal-test_mapper_table', $mapper->getValue($out));
        $this->assertEquals('dal-test_model_table', $model->getValue($out));
        $this->assertInstanceOf('Mock\Service\Table', $out);
    }

    public function testCreateServiceWithNameEception()
    {
        $this->setExpectedException(
                'Exception', 'Class does not exist : Mock\Service\TableNoExist'
        );

        $model_abstract_factory = new ServiceAbstractFactory();
        $serviceManager = $this->getApplicationServiceLocator();
        $out = $model_abstract_factory->createServiceWithName($serviceManager, 'dal-test_service_table_no_exist', 'dal-test_service_table_no_exist');

        $this->assertInstanceOf('Mock\Model\Table', $out);
    }
}
