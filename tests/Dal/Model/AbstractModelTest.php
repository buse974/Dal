<?php

namespace DalTest\Model;

use PHPUnit_Framework_TestCase;
use Zend\Db\Sql\Predicate\IsNull;

class AbstractModelTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $mock_mapper = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(), 'mockClassName', false);
        $this->assertEquals($mock_mapper->__toString(), 'mockClassName');
    }

    public function testExchangeArrayBasic()
    {
        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(), '', false, true, true, array('setToto', 'setTata', 'setNull'));

        $mock_model->expects($this->once())
                   ->method('setToto')
                   ->with($this->equalTo('vtoto'))
                   ->will($this->returnSelf());

        $mock_model->expects($this->once())
                   ->method('setTata')
                   ->with($this->equalTo('vtata'))
                   ->will($this->returnSelf());

        $mock_model->expects($this->once())
                   ->method('setNull')
                   ->with($this->equalTo(new IsNull()))
                   ->will($this->returnSelf());

        $datas = array('toto' => 'vtoto', 'tata' => 'vtata', 'null' => null);
        $this->assertEquals($mock_model->exchangeArray($datas), $mock_model);
        $this->assertEmpty($datas);
    }

    public function testExchangeArrayPrefix()
    {
        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'unprefix'), '', true, true, true, array('setToto', 'setTata'));
        $mock_model->expects($this->any())->method('setToto')->with($this->equalTo('vtoto'))->will($this->returnSelf());
        $mock_model->expects($this->any())->method('setTata')->with($this->equalTo('vtata'))->will($this->returnSelf());

        $datas = array('unprefix$toto' => 'vtoto', 'unprefix$tata' => 'vtata');
        $this->assertEquals($mock_model->exchangeArray($datas), $mock_model);
        $this->assertEmpty($datas);
    }

    public function testExchangeArrayPrefixParent()
    {
        $mock_model_parent = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'parent_prefix'), 'parentModel', true, true, true, array('setToto', 'setTata'));
        $mock_model_parent->expects($this->any())->method('setToto')->with($this->equalTo('vtotop'))->will($this->returnSelf());
        $mock_model_parent->expects($this->any())->method('setTata')->with($this->equalTo('vtatap'))->will($this->returnSelf());

        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array($mock_model_parent, 'unprefix'), '', true, true, true, array('setToto', 'setTata'));
        $mock_model->expects($this->any())->method('setToto')->with($this->equalTo('vtoto'))->will($this->returnSelf());
        $mock_model->expects($this->any())->method('setTata')->with($this->equalTo('vtata'))->will($this->returnSelf());

        $datas = array('parent_prefix_unprefix$toto' => 'vtoto', 'parent_prefix_unprefix$tata' => 'vtata', 'parent_prefix$toto' => 'vtotop', 'unprefix$tata' => 'vtata');

        $this->assertEquals($mock_model->exchangeArray($datas), $mock_model);
        $this->assertEquals($mock_model_parent->exchangeArray($datas), $mock_model_parent);
        $this->assertEmpty($datas);
    }

    public function testAllParent()
    {
        $mock_model_parent = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'parent_prefix'), 'parentModel', true, true, true);
        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array($mock_model_parent, 'unprefix'), '', true, true, true);

        $this->assertEquals($mock_model->allParent(), 'parent_prefix_unprefix');
    }

    public function testToArray()
    {
        $m_array = $this->getMockBuilder('stdClass')
                        ->setMethods(array('toArray'))
                        ->getMock();

        $m_array->expects($this->once())
                ->method('toArray')
                ->will($this->returnValue(array()));

        $mock_model_child = $this->getMockObjectGenerator()
                                 ->getMock('mock', array('toArray'));

        $mock_model_child->expects($this->any())
                         ->method('toArray')
                         ->will($this->returnValue(array('key' => 'vchild')));

        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'prefix'), '', true, true, true, array('getObjArrayEmpty', 'getArray', 'getBool', 'getToto', 'getTata', 'getNothing', 'getNull', 'getChild', 'getOtherObj'));
        $mock_model->expects($this->any())->method('getToto')->will($this->returnValue('vtoto'));
        $mock_model->expects($this->any())->method('getBool')->will($this->returnValue(true));
        $mock_model->expects($this->any())->method('getArray')->will($this->returnValue(array('array')));
        $mock_model->expects($this->any())->method('getTata')->will($this->returnValue('vtata'));
        $mock_model->expects($this->any())->method('getNothing')->will($this->returnValue(new IsNull()));
        $mock_model->expects($this->any())->method('getNull')->will($this->returnValue(null));
        $mock_model->expects($this->any())->method('getChild')->will($this->returnValue($mock_model_child));
        $mock_model->expects($this->any())->method('getOtherObj')->will($this->returnValue(new \stdClass()));
        $mock_model->expects($this->any())->method('getObjArrayEmpty')->will($this->returnValue($m_array));

        $out = $mock_model->toArray();

        $this->assertCount(6, $out);

        $this->assertArrayHasKey('toto', $out);
        $this->assertArrayHasKey('bool', $out);
        $this->assertArrayHasKey('array', $out);
        $this->assertArrayHasKey('tata', $out);
        $this->assertArrayHasKey('nothing', $out);
        $this->assertArrayHasKey('child', $out);

        $this->assertArrayNotHasKey('null', $out);
        $this->assertArrayNotHasKey('obj_array_empty', $out);
        $this->assertArrayNotHasKey('other_obj', $out);

        $this->assertEquals($out['nothing'], null);
        $this->assertEquals($out['toto'], 'vtoto');
        $this->assertEquals($out['bool'], 1);
        $this->assertEquals($out['array'], array('array'));
        $this->assertEquals($out['tata'], 'vtata');

        $this->assertArrayHasKey('key', $out['child']);
        $this->assertEquals($out['child']['key'], 'vchild');
    }

    public function testToArrayCurrent()
    {
        $mock_model_child = $this->getMockObjectGenerator()->getMock('mock', array('toArray'));
        $mock_model_child->expects($this->any())->method('toArray')->will($this->returnValue(array('key' => 'vchild')));

        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'prefix'), '', true, true, true, array('getBool', 'getToto', 'getTata', 'getNothing', 'getNull', 'getChild', 'getOtherObj'));
        $mock_model->expects($this->any())->method('getToto')->will($this->returnValue('vtoto'));
        $mock_model->expects($this->any())->method('getBool')->will($this->returnValue(true));
        $mock_model->expects($this->any())->method('getTata')->will($this->returnValue('vtata'));
        $mock_model->expects($this->any())->method('getNothing')->will($this->returnValue(new IsNull()));
        $mock_model->expects($this->any())->method('getNull')->will($this->returnValue(null));
        $mock_model->expects($this->any())->method('getChild')->will($this->returnValue($mock_model_child));
        $mock_model->expects($this->any())->method('getOtherObj')->will($this->returnValue(new \stdClass()));

        $out = $mock_model->toArrayCurrent();

        $this->assertCount(4, $out);

        $this->assertArrayHasKey('toto', $out);
        $this->assertArrayHasKey('bool', $out);
        $this->assertArrayHasKey('tata', $out);
        $this->assertArrayNotHasKey('nothing', $out);

        $this->assertArrayNotHasKey('child', $out);
        $this->assertArrayNotHasKey('null', $out);
        $this->assertArrayNotHasKey('other_obj', $out);

        $this->assertEquals($out['toto'], 'vtoto');
        $this->assertEquals($out['bool'], 1);
        $this->assertEquals($out['tata'], 'vtata');
    }

    public function testJsonSerialize()
    {
        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'prefix'), '', true, true, true, array('toArray'));
        $mock_model->expects($this->any())->method('toArray')->will($this->returnValue('toarray'));

        $this->assertEquals('toarray', $mock_model->jsonSerialize());
    }

    public function testGetParentArray()
    {
        $mock_model_ppp = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'unprefix'), 'parentModel', true, true, true);
        $mock_model_pp = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array($mock_model_ppp, 'unprefix'), 'parentModel', true, true, true);
        $mock_model_p = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array($mock_model_pp, 'parent_prefix'), 'parentModel', true, true, true);
        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array($mock_model_p, 'unprefix'), '', true, true, true);

        $reflectionClass = new \ReflectionClass($mock_model);
        $reflectionMethod = $reflectionClass->getMethod('getParentArray');
        $reflectionMethod->setAccessible(true);

        $out = $reflectionMethod->invoke($mock_model);

        $this->assertCount(4, $out);
        $this->assertEquals($out[0], 'unprefix');
        $this->assertEquals($out[1], 'unprefix');
        $this->assertEquals($out[2], 'parent_prefix');
        $this->assertEquals($out[3], 'unprefix');
    }

    public function testIsRepeatRelationalFalse()
    {
        $in = array('prefix', 'prefix', 'autre_prefix');

        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'autre_prefix'), '', true, true, true, array('getParentArray'));
        $mock_model->expects($this->any())->method('getParentArray')->will($this->returnValue($in));

        $reflectionClass = new \ReflectionClass($mock_model);
        $reflectionMethod = $reflectionClass->getMethod('isRepeatRelational');
        $reflectionMethod->setAccessible(true);

        $out = $reflectionMethod->invokeArgs($mock_model, array('isRepeatRelational'));

        $this->assertFalse($out);
    }

    public function testIsRepeatRelationalTrue()
    {
        $in = array('prefix', 'prefix', 'autre_prefix');

        $mock_model = $this->getMockForAbstractClass('\Dal\Model\AbstractModel', array(null, 'prefix'), '', true, true, true, array('getParentArray'));
        $mock_model->expects($this->any())->method('getParentArray')->will($this->returnValue($in));

        $reflectionClass = new \ReflectionClass($mock_model);
        $reflectionMethod = $reflectionClass->getMethod('isRepeatRelational');
        $reflectionMethod->setAccessible(true);

        $out = $reflectionMethod->invokeArgs($mock_model, array('isRepeatRelational'));

        $this->assertTrue($out);
    }
}
