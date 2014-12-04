<?php

namespace DalTest\Db\ResultSet;

use PHPUnit_Framework_TestCase;
use Dal\Db\ResultSet\ResultSet;

class ResultSetTest extends PHPUnit_Framework_TestCase
{
    public function testToArrayByArray()
    {
        $resultset = new ResultSet();

        $resultset->initialize(array(
                array('key' => 'ktoto', 'arr' => 'value'),
                array('key' => 'ktata', 'arr' => 'value'),
        ));

        $out = $resultset->toArray(array('key'), true);

        $this->assertArrayHasKey('ktoto', $out);
        $this->assertArrayHasKey('ktata', $out);

        $this->assertArrayHasKey('arr', $out['ktoto']);
        $this->assertArrayHasKey('arr', $out['ktata']);

        $this->assertEquals('value', $out['ktoto']['arr']);
        $this->assertEquals('value', $out['ktata']['arr']);
    }

    public function testToArrayByObjArray()
    {
        $arr = new \ArrayObject(array(
                array('key' => 'ktoto', 'arr' => 'value'),
                array('key' => 'ktata', 'arr' => 'value'),
        ));

        $resultset = new ResultSet();
        $resultset->initialize($arr);
        $out = $resultset->toArray(array('key'), true);

        $this->assertArrayHasKey('ktoto', $out);
        $this->assertArrayHasKey('ktata', $out);

        $this->assertArrayHasKey('arr', $out['ktoto']);
        $this->assertArrayHasKey('arr', $out['ktata']);

        $this->assertEquals('value', $out['ktoto']['arr']);
        $this->assertEquals('value', $out['ktata']['arr']);
    }

    public function testToArrayParent($chaine_parent = 'parent_id', $chaine_id = 'id', array $indices = array(), $unset_indice = false)
    {
        $resultset = new ResultSet();

        $resultset->initialize(array(
                array('key' => 'ktata', 'id' => 3, 'parent_id' => 2),
                array('key' => 'ktiti', 'id' => 4, 'parent_id' => 3),
                array('key' => 'ktoto', 'id' => 2, 'parent_id' => 1),
                array('key' => 'ktutu', 'id' => 1, 'parent_id' => null),
        ));

        $out = $resultset->toArrayParent();

        $this->assertEquals(1, reset($out)['id']);
        $this->assertEquals(2, next($out)['id']);
        $this->assertEquals(3, next($out)['id']);
        $this->assertEquals(4, next($out)['id']);
    }

    public function testToArrayCurrent()
    {
        $arr = new \ArrayObject();
        $m_model = $this->getMockForAbstractClass('Dal\Model\AbstractModel', array(), '', false, false, true, array('toArray', 'toArrayCurrent'));
        $m_model->expects($this->exactly(1))->method('toArrayCurrent')->will($this->returnValue(array('arraycurent')));
        $m_model->expects($this->exactly(0))->method('toArray')->will($this->returnValue(array('array')));
        $arr->append($m_model);

        $m_model = $this->getMockObjectGenerator()->getMock('mock', array('toArray', 'toArrayCurrent'));
        $m_model->expects($this->exactly(0))->method('toArrayCurrent')->will($this->returnValue(array('arraycurent')));
        $m_model->expects($this->exactly(1))->method('toArray')->will($this->returnValue(array('array')));
        $arr->append($m_model);

        $resultset = new ResultSet();
        $resultset->initialize($arr);
        $out = $resultset->toArrayCurrent();

        $this->assertEquals('arraycurent', $out[0][0]);
        $this->assertEquals('array', $out[1][0]);
    }

    public function testCurrentWihtOnceCallExchangeArray()
    {
        $m_model = $this->getMockForAbstractClass('Dal\Model\AbstractModel', array(), '', false, false, true, array('exchangeArray'));
        $m_model->expects($this->once())->method('exchangeArray')->will($this->returnSelf());

        $arr = new \ArrayObject(array(array('toto'), array('tata')));

        $resultset = new ResultSet();
        $resultset->bufferArrayObjectPrototype();
        $resultset->setArrayObjectPrototype($m_model);
        $resultset->initialize($arr);
        $out = $resultset->current();
        $out = $resultset->current();
        $out = $resultset->current();
    }
}
