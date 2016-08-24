<?php
namespace DalTest\Db\ResultSet;

use PHPUnit_Framework_TestCase;
use Dal\Db\ResultSet\ResultSet;

class ResultSetTest extends PHPUnit_Framework_TestCase
{

    public function testToArrayByArray()
    {
        $resultset = new ResultSet();
        $resultset->initialize([['key' => 'ktoto','arr' => 'value'],['key' => 'ktata','arr' => 'value']]);
        
        $out = $resultset->toArray(['key'], true);
        
        $this->assertArrayHasKey('ktoto', $out);
        $this->assertArrayHasKey('ktata', $out);
        
        $this->assertArrayHasKey('arr', $out['ktoto']);
        $this->assertArrayHasKey('arr', $out['ktata']);
        
        $this->assertEquals('value', $out['ktoto']['arr']);
        $this->assertEquals('value', $out['ktata']['arr']);
    }

    public function testToArrayByObjArray()
    {
        $arr = new \ArrayObject([
            ['key' => 'ktoto','arr' => 'value'],
            ['key' => 'ktata','arr' => 'value']
        ]);
        
        $resultset = new ResultSet();
        $resultset->initialize($arr);
        
        $out = $resultset->toArray(['key']);

        $this->assertArrayHasKey('ktoto', $out);
        $this->assertArrayHasKey('ktata', $out);
        
        $this->assertArrayHasKey('arr', $out['ktoto']);
        $this->assertArrayHasKey('arr', $out['ktata']);
        
        $this->assertEquals('value', $out['ktoto']['arr']);
        $this->assertEquals('value', $out['ktata']['arr']);
    }

    public function testToArrayParent()
    {
        $resultset = new ResultSet();
        
        $resultset->initialize(array(array('key' => 'ktata','id' => 3,'parent_id' => 2),array('key' => 'ktiti','id' => 4,'parent_id' => 3),array('key' => 'ktoto','id' => 2,'parent_id' => 1),array('key' => 'ktutu','id' => 1,'parent_id' => null)));
        
        $out = $resultset->toArrayParent();
        
        $this->assertEquals(1, reset($out)['id']);
        $this->assertEquals(2, next($out)['id']);
        $this->assertEquals(3, next($out)['id']);
        $this->assertEquals(4, next($out)['id']);
    }

    public function testToArrayParentByIndice()
    {
        $resultset = new ResultSet();
        
        $resultset->initialize(array(array('key' => 'ktata','id' => 3,'parent_id' => 2),array('key' => 'ktiti','id' => 4,'parent_id' => 3),array('key' => 'ktoto','id' => 2,'parent_id' => 1),array('key' => 'ktutu','id' => 1,'parent_id' => null)));
        
        $out = $resultset->toArrayParent('parent_id', 'id', array('key'), true);
        
        $this->assertArrayHasKey('ktutu', $out);
        $this->assertArrayNotHasKey('key', $out['ktutu']);
        
        $this->assertArrayHasKey('ktiti', $out);
        $this->assertArrayNotHasKey('key', $out['ktiti']);
        
        $this->assertArrayHasKey('ktoto', $out);
        $this->assertArrayNotHasKey('key', $out['ktoto']);
        
        $this->assertArrayHasKey('ktata', $out);
        $this->assertArrayNotHasKey('key', $out['ktata']);
    }

    public function testToArrayCurrent()
    {
        $arr = new \ArrayObject();
        
        $m_model = $this->getMockBuilder('Dal\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(array('toArray','toArrayCurrent'))
            ->getMock();
        
        $m_model->expects($this->exactly(1))
            ->method('toArrayCurrent')
            ->will($this->returnValue(array('id' => 'toto','value' => 'vtoto')));
        
        $m_model->expects($this->exactly(0))
            ->method('toArray')
            ->will($this->returnValue(array('array')));
        
        $arr->append($m_model);
        
        $m_model = $this->getMockBuilder('ArrayObject')
            ->setMethods(array('getArrayCopy'))
            ->getMock();
        
        $m_model->expects($this->exactly(1))
            ->method('getArrayCopy')
            ->will($this->returnValue(array('id' => 'titi','value' => 'vtiti')));
        
        $arr->append($m_model);
        
        $resultset = new ResultSet();
        $resultset->initialize($arr);
        $out = $resultset->toArrayCurrent(array('id'), true);
        
        $this->assertEquals(array('value' => 'vtoto'), $out['toto']);
        $this->assertEquals(array('value' => 'vtiti'), $out['titi']);
    }

    public function testToArrayCurrentWihtoutIndice()
    {
        $arr = new \ArrayObject();
        
        $m_model = $this->getMockBuilder('Dal\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(array('toArray','toArrayCurrent'))
            ->getMock();
        
        $m_model->expects($this->exactly(1))
            ->method('toArrayCurrent')
            ->will($this->returnValue(array('id' => 'toto','value' => 'vtoto')));
        
        $m_model->expects($this->exactly(0))
            ->method('toArray')
            ->will($this->returnValue(array('array')));
        
        $arr->append($m_model);

        $m_model = $this->getMockBuilder('ArrayObject')
            ->setMethods(array('getArrayCopy'))
            ->getMock();
        
        $m_model->expects($this->exactly(1))
            ->method('getArrayCopy')
            ->will($this->returnValue(array('id' => 'titi','value' => 'vtiti')));
        
        $arr->append($m_model);
        
        $resultset = new ResultSet();
        $resultset->initialize($arr);
        $out = $resultset->toArrayCurrent();
        
        $this->assertEquals(array('id' => 'toto','value' => 'vtoto'), $out[0]);
        $this->assertEquals(array('id' => 'titi','value' => 'vtiti'), $out[1]);
    }

    public function testToArrayCurrentException()
    {
        $this->setExpectedException('RuntimeException', 'Rows as part of this DataSource, with type object cannot be cast to an array Current', 0);
        
        $arr = new \ArrayObject();
        
        $m_model = $this->getMockBuilder('mock')
            ->setMethods(array())
            ->getMock();
        $arr->append($m_model);
        
        $resultset = new ResultSet();
        $resultset->initialize($arr);
        $out = $resultset->toArrayCurrent();
    }

    public function testCurrentWihtOnceCallExchangeArray()
    {
        $m_model = $this->getMockBuilder('Dal\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->setMethods(array('exchangeArray'))
            ->getMock();
        
        $m_model->expects($this->once())
            ->method('exchangeArray')
            ->will($this->returnSelf());
        
        $arr = new \ArrayObject(array(array('toto'),array('tata')));
        
        $resultset = new ResultSet();
        $resultset->bufferArrayObjectPrototype();
        $resultset->setArrayObjectPrototype($m_model);
        $resultset->initialize($arr);
        $out = $resultset->current();
        $out = $resultset->current();
        $out = $resultset->current();
    }

    public function testJsonSerialize()
    {
        $m_resultset = $this->getMockBuilder('Dal\Db\ResultSet\ResultSet')
            ->setMethods(array('toArray'))
            ->getMock();
        
        $m_resultset->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue('jsonarray'));
        
        $this->assertEquals('jsonarray', $m_resultset->jsonSerialize());
    }
}
