<?php

namespace DalTest\AbstractFactory;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AbstractFactoryTest extends AbstractHttpControllerTestCase
{
	public function setUp()
	{
		$this->setApplicationConfig(
				include __DIR__ . '/../../config/application.config.php'
		);
		parent::setUp();
	}
	
    public function testGetConfig()
    {
    	$serviceManager = $this->getApplicationServiceLocator();
    	$m_abs = $this->getMockForAbstractClass('Dal\AbstractFactory\AbstractFactory');
    	$out = $m_abs->getConfig($serviceManager);
    	
    	$this->assertArrayHasKey('adapter', $out);
    	$this->assertEquals('adapter', $out['adapter']);
    	$this->assertArrayHasKey('namespace', $out);
    	$this->assertTrue(is_array($out['namespace']));
    	
    }

    public function testToCamelCase()
    {
    	$m_abs = $this->getMockForAbstractClass('Dal\AbstractFactory\AbstractFactory');
    	$out = $m_abs->toCamelCase('une_chaine_camel_case');
    	
        $this->assertEquals('UneChaineCamelCase', $out);
    }

}
