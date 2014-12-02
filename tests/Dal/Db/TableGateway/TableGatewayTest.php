<?php 
namespace DalTest\Db\TableGateway;

use \PHPUnit_Framework_TestCase;
use Dal\Db\TableGateway\TableGateway;

class TableGatewayTest extends PHPUnit_Framework_TestCase
{
    public function testSelectPdo()
    {
    	$m_resultset = $this->getMockObjectGenerator()->getMock('resultSet', array('initialize'));
    	$m_resultset->expects($this->once())->method('initialize')->will($this->returnSelf());
    	
    	$m_statement = $this->getMockObjectGenerator()->getMock('statement', array('execute'));
    	
    	$m_adapter = $this->getMockObjectGenerator()->getMock('adapter', array('query'));
    	$m_adapter->expects($this->once())->method('query')->will($this->returnValue($m_statement));
    	
    	$mock_tgw = $this->getMockBuilder('\Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(null)->getMock();

    	$reflection = new \ReflectionClass($mock_tgw);
    	
    	$reflection_property = $reflection->getProperty('resultSetPrototype');
    	$reflection_property->setAccessible(true);
    	$reflection_property->setValue($mock_tgw, $m_resultset);
    	
    	$reflection_property = $reflection->getProperty('adapter');
    	$reflection_property->setAccessible(true);
    	$reflection_property->setValue($mock_tgw, $m_adapter);
    	
    	$this->assertEquals($mock_tgw->selectPdo('SELECT TEST',array('PARAM')), $m_resultset);
   }

    public function testRequestPdo()
    {
    	$m_adapter = $this->getMockObjectGenerator()->getMock('adapter', array('query','execute','count','getDriver', 'getLastGeneratedValue','getResource','closeCursor'));
    	$m_adapter->expects($this->once())->method('query')->will($this->returnSelf());
    	$m_adapter->expects($this->once())->method('execute')->will($this->returnSelf());
    	$m_adapter->expects($this->once())->method('count')->will($this->returnValue(8));
    	$m_adapter->expects($this->once())->method('getDriver')->will($this->returnSelf());
    	$m_adapter->expects($this->once())->method('getLastGeneratedValue')->will($this->returnValue(5));
    	$m_adapter->expects($this->once())->method('getResource')->will($this->returnSelf());
    	
    	$mock_tgw = $this->getMockBuilder('\Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(null)->getMock();
    	
    	$reflection = new \ReflectionClass($mock_tgw);
    	 
    	$reflection_property = $reflection->getProperty('adapter');
    	$reflection_property->setAccessible(true);
    	$reflection_property->setValue($mock_tgw, $m_adapter);
    	 
    	$this->assertEquals($mock_tgw->requestPdo('insert into',array('PARAM')), 8);
    	
    	$reflection_property = $reflection->getProperty('lastInsertValue');
    	$reflection_property->setAccessible(true);
    	
    	$this->assertEquals($reflection_property->getValue($mock_tgw), 5);
    }
    
    public function testSelectNMPdo()
    {
    	$m_adapter = $this->getMockObjectGenerator()->getMock('adapter', array('query','execute'));
    	$m_adapter->expects($this->once())->method('query')->will($this->returnSelf());
    	$m_adapter->expects($this->once())->method('execute')->will($this->returnValue(array()));
    	 
    	$mock_tgw = $this->getMockBuilder('\Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(null)->getMock();
    	
    	$reflection = new \ReflectionClass($mock_tgw);
    	 
    	$reflection_property = $reflection->getProperty('adapter');
    	$reflection_property->setAccessible(true);
    	$reflection_property->setValue($mock_tgw, $m_adapter);
    	 
    	$this->assertInstanceOf('Dal\Db\ResultSet\ResultSet', $mock_tgw->selectNMPdo('SELECT TEST',array('PARAM')));
    }

    /**
     * @todo untestable getPrimaryKey
     * 
     */
    public function getPrimaryKey()
    {
    }
}
