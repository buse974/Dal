<?php

namespace DalTest\AbstractFactory;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AbstractFactoryTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
                include __DIR__.'/../../config/application.config.php'
        );
        parent::setUp();
    }

    
    public function testGetConfig()
    {
        //@
        $container = $this->getApplication()->getServiceManager();
        $m_abs = $this->getMockBuilder('Dal\AbstractFactory\AbstractFactory')
                      ->setMethods(['__invoke', 'canCreate'])
                      ->getMock();

        $out = $m_abs->getConfig($container);

        $this->assertArrayHasKey('adapter', $out);
        $this->assertEquals('adapter', $out['adapter']);
        $this->assertArrayHasKey('namespace', $out);
        $this->assertTrue(is_array($out['namespace']));
    }

    public function testNotGetConfig()
    {
        $container = $this->getMockBuilder('Interop\Container\ContainerInterface')
                              ->setMethods(array('has', 'get'))
                              ->disableOriginalConstructor()
                              ->getMock();

        $container->expects($this->once())
                      ->method('has')
                      ->with('Config')
                      ->will($this->returnValue(false));

        $m_abs = $this->getMockBuilder('Dal\AbstractFactory\AbstractFactory')
                      ->setMethods(array('__invoke', 'canCreate'))
                      ->getMock();

        $this->assertEquals(false, $m_abs->getConfig($container));
    }

    public function testToCamelCase()
    {
        $m_abs = $this->getMockForAbstractClass('Dal\AbstractFactory\AbstractFactory');
        $out = $m_abs->toCamelCase('une_chaine_camel_case');

        $this->assertEquals('UneChaineCamelCase', $out);
    }
}
