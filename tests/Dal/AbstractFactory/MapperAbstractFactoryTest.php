<?php

namespace DalTest\AbstractFactory;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Dal\AbstractFactory\MapperAbstractFactory;

class MapperAbstractFactoryTest extends AbstractHttpControllerTestCase
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
        $model_abstract_factory = new MapperAbstractFactory();
        $serviceManager = $this->getApplicationServiceLocator();
        $out = $model_abstract_factory->canCreateServiceWithName($serviceManager, 'dal-test_mapper_table', 'dal-test_mapper_table');

        $this->assertTrue($out);
    }

    public function testCreateServiceWithName()
    {
        $m_adapter = $this->getMockBuilder('Zend\Db\Adapter\Adapter')->disableOriginalConstructor()->setMethods(array('getPlatform', 'getName'))->getMock();
        $m_adapter->expects($this->any())->method('getPlatform')->will($this->returnSelf());
        $m_adapter->expects($this->any())->method('getName')->will($this->returnValue('mysql'));

        $m_model = $this->getMockBuilder('Mock\Model')->setMethods(array('exchangeArray'))->getMock();
        $m_model->expects($this->any())->method('exchangeArray')->will($this->returnSelf());

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('adapter', $m_adapter);
        $serviceManager->setService('dal-test_model_table', $m_model);

        $model_abstract_factory = new MapperAbstractFactory();
        $serviceManager = $this->getApplicationServiceLocator();
        $out = $model_abstract_factory->createServiceWithName($serviceManager, 'dal-test_mapper_table', 'dal-test_mapper_table');

        $this->assertInstanceOf('Mock\Mapper\Table', $out);
    }
}
