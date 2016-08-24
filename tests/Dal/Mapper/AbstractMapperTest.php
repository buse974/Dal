<?php
namespace DalTest\Mapper;

use PHPUnit_Framework_TestCase;
use Dal\Db\ResultSet\ResultSet;

class AbstractMapperTest extends PHPUnit_Framework_TestCase
{

    public function testSelect()
    {
        $m_select = $this->getMockForAbstractClass('Dal\Db\Sql\Select');
        
        $m_sql = $this->getMockBuilder('Dal\Db\Sql\Sql')
            ->setMethods(['where','select','order'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_sql->expects($this->any())
            ->method('select')
            ->will($this->returnSelf());
        
        $m_sql->expects($this->any())
            ->method('where')
            ->will($this->returnValue($m_select));
        
        $m_sql->expects($this->any())
            ->method('order')
            ->will($this->returnValue($m_select));
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('select')
            ->will($this->returnValue('resultset'));
        
        $m_table_gateway->expects($this->any())
            ->method('getSql')
            ->will($this->returnValue($m_sql));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(array('initPaginator'))
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $m_abstract_mapper->expects($this->any())
            ->method('initPaginator')
            ->will($this->returnValue('paginition'));
        
        $m_abstract_mapper->usePaginator([]);
        $m_model = $this->getMockForAbstractClass('Dal\Model\AbstractModel');
        $out = $m_abstract_mapper->select($m_model, array('order'));
        
        $this->assertEquals('paginition', $out);
    }

    public function testSelectWhitoutPagination()
    {
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('select')
            ->will($this->returnValue('resultset'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(['initPaginator'])
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $m_abstract_mapper->expects($this->any())
            ->method('initPaginator')
            ->will($this->returnValue('paginition'));
        
        $m_model = $this->getMockForAbstractClass('Dal\Model\AbstractModel');
        
        $this->assertEquals('resultset', $m_abstract_mapper->select($m_model));
    }

    public function testRequestPdo()
    {
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('requestPdo')
            ->with('request', 'param')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $this->assertEquals('result', $m_abstract_mapper->requestPdo('request', 'param'));
    }

    public function testSelectPdo()
    {
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('selectPdo')
            ->with('requestPagination', 'param')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(array('initPaginator'))
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $m_abstract_mapper->expects($this->once())
            ->method('initPaginator')
            ->with(array('request','param'))
            ->will($this->returnValue('requestPagination'));
        
        $reflection = new \ReflectionClass('Dal\Mapper\AbstractMapper');
        $usePaginator = $reflection->getProperty('usePaginator');
        $usePaginator->setAccessible(true);
        $usePaginator->setValue($m_abstract_mapper, true);
        
        $out = $m_abstract_mapper->selectPdo('request', 'param');
        
        $this->assertEquals('requestPagination', $out);
    }

    public function testSelectNMPdo()
    {
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('selectNMPdo')
            ->with('requestPagination', 'param')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(array('initPaginator'))
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $m_abstract_mapper->expects($this->once())
            ->method('initPaginator')
            ->with(array('request','param'))
            ->will($this->returnValue('requestPagination'));
        
        $reflection = new \ReflectionClass('Dal\Mapper\AbstractMapper');
        $usePaginator = $reflection->getProperty('usePaginator');
        $usePaginator->setAccessible(true);
        $usePaginator->setValue($m_abstract_mapper, true);
        
        $out = $m_abstract_mapper->selectNMPdo('request', 'param');
        
        $this->assertEquals('requestPagination', $out);
    }

    public function testSelectWith()
    {
        $m_select = $this->getMockBuilder('Dal\Db\Sql\Select')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('selectWith')
            ->with($m_select)
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $this->assertEquals('result', $m_abstract_mapper->selectWith($m_select));
    }

    public function testSelectWithWihtPagination()
    {
        $m_select = $this->getMockBuilder('Dal\Db\Sql\Select')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(array('initPaginator'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_abstract_mapper->expects($this->once())
            ->method('initPaginator')
            ->with($m_select)
            ->will($this->returnValue('requestPagination'));
        
        $reflection = new \ReflectionClass('Dal\Mapper\AbstractMapper');
        $usePaginator = $reflection->getProperty('usePaginator');
        $usePaginator->setAccessible(true);
        $usePaginator->setValue($m_abstract_mapper, true);
        
        $this->assertEquals('requestPagination', $m_abstract_mapper->selectWith($m_select));
    }

    public function testFetchAll()
    {
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('select')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $this->assertEquals('result', $m_abstract_mapper->fetchAll());
    }

    public function testFetchAllWithPagination()
    {
        $m_select = $this->getMockBuilder('Dal\Db\Sql\Select')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->setMethods(array('getSql','select'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('getSql')
            ->will($this->returnSelf());
        
        $m_table_gateway->expects($this->any())
            ->method('select')
            ->will($this->returnValue($m_select));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(array('initPaginator'))
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $m_abstract_mapper->expects($this->once())
            ->method('initPaginator')
            ->with($m_select)
            ->will($this->returnValue('requestPagination'));
        
        $reflection = new \ReflectionClass('Dal\Mapper\AbstractMapper');
        $usePaginator = $reflection->getProperty('usePaginator');
        $usePaginator->setAccessible(true);
        $usePaginator->setValue($m_abstract_mapper, true);
        
        $this->assertEquals('requestPagination', $m_abstract_mapper->fetchAll());
    }

    public function testDeleteWith()
    {
        $m_delete = $this->getMockBuilder('\Zend\Db\Sql\Delete')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('deleteWith')
            ->with($m_delete)
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $out = $m_abstract_mapper->deleteWith($m_delete);
        
        $this->assertEquals('result', $out);
    }

    public function testInsertWith()
    {
        $m_insert = $this->getMockBuilder('\Zend\Db\Sql\Insert')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('insertWith')
            ->with($m_insert)
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->insertWith($m_insert);
        
        $this->assertEquals('result', $out);
    }

    public function testUpdateWith()
    {
        $m_update = $this->getMockBuilder('\Zend\Db\Sql\Update')
            ->setMethods(array())
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('updateWith')
            ->with($m_update)
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->updateWith($m_update);
        
        $this->assertEquals('result', $out);
    }

    /*public function testPrintSql()
    {
        $m_select = $this->getMockBuilder('Zend\Db\Sql\SqlInterface')->getMock();
        
        $m_select->expects($this->any())
            ->method('getSqlString')
            ->will($this->returnValue('SELECT * FROM toto'));
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->setMethods(['getPlatform','getAdapter'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnSelf());
        
        $m_table_gateway->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnSelf());
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        
        $out = $m_abstract_mapper->printSql($m_select);
        
        $this->assertEquals('SELECT * FROM toto', $out);
    }*/

    public function testInsert()
    {
        $m_model = $this->getMockBuilder('Dal\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('insert')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->insert($m_model);
        
        $this->assertEquals('result', $out);
    }

    public function testDeleteException()
    {
        $this->setExpectedException('Exception', 'Error : delete used an empty model', 0);
        
        $m_model = $this->getMockBuilder('Dal\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('delete')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->delete($m_model);
        
        $this->assertEquals('result', $out);
    }

    public function testDelete()
    {
        $m_model = $this->getMockBuilder('Dal\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        $m_model->expects($this->any())
            ->method('toArrayCurrent')
            ->will($this->returnValue(array('id' => 3)));
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('delete')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->delete($m_model);
        
        $this->assertEquals('result', $out);
    }

    public function testUpdate()
    {
        $m_model = $this->getMockBuilder('Dal\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        $m_model->expects($this->any())
            ->method('toArrayCurrentNoPredicate')
            ->will($this->returnValue(array('value' => 3)));
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('update')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->update($m_model, array('id' => 3));
        
        $this->assertEquals('result', $out);
    }

    public function testUpdateNoWhereParam()
    {
        $m_model = $this->getMockBuilder('Dal\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        $m_model->expects($this->any())
            ->method('toArrayCurrentNoPredicate')
            ->will($this->returnValue(array('value' => 3,'id' => 3)));
        
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('update')
            ->will($this->returnValue('result'));
        $m_table_gateway->expects($this->any())
            ->method('getPrimaryKey')
            ->will($this->returnValue(array('id')));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->update($m_model);
        
        $this->assertEquals('result', $out);
    }

    public function testGetLastInsertValue()
    {
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('getLastInsertValue')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->getLastInsertValue();
        
        $this->assertEquals('result', $out);
    }

    public function testFetchRow()
    {
        $m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')
            ->setMethods(array('select','current'))
            ->disableOriginalConstructor()
            ->getMock();
        $m_table_gateway->expects($this->any())
            ->method('select')
            ->will($this->returnSelf());
        $m_table_gateway->expects($this->any())
            ->method('current')
            ->will($this->returnValue('result'));
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->setConstructorArgs(array($m_table_gateway))
            ->getMock();
        $out = $m_abstract_mapper->fetchRow('id', 3);
        
        $this->assertEquals('result', $out);
    }

    public function testUsePaginatorDefault()
    {
        $reflectionClass = new \ReflectionClass('Dal\Mapper\AbstractMapper');
        
        $reflection = $reflectionClass->getProperty('usePaginator');
        $reflection->setAccessible(true);
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertFalse($reflection->getValue($m_abstract_mapper));
        $out = $m_abstract_mapper->usePaginator([]);
        $this->assertTrue($reflection->getValue($m_abstract_mapper));
        
        $reflection = $reflectionClass->getProperty('paginatorOptions');
        $reflection->setAccessible(true);
        $paginatorOptions = $reflection->getValue($m_abstract_mapper);
        
        $this->assertArrayHasKey('n', $paginatorOptions);
        $this->assertArrayHasKey('p', $paginatorOptions);
        
        $this->assertEquals(10, $paginatorOptions['n']);
        $this->assertEquals(1, $paginatorOptions['p']);
    }

    public function testUsePaginator()
    {
        $reflectionClass = new \ReflectionClass('Dal\Mapper\AbstractMapper');
        
        $reflection = $reflectionClass->getProperty('usePaginator');
        $reflection->setAccessible(true);
        
        $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertFalse($reflection->getValue($m_abstract_mapper));
        $out = $m_abstract_mapper->usePaginator(array('n' => 20,'p' => 3));
        $this->assertTrue($reflection->getValue($m_abstract_mapper));
        
        $reflection = $reflectionClass->getProperty('paginatorOptions');
        $reflection->setAccessible(true);
        $paginatorOptions = $reflection->getValue($m_abstract_mapper);
        
        $this->assertArrayHasKey('n', $paginatorOptions);
        $this->assertArrayHasKey('p', $paginatorOptions);
        
        $this->assertEquals(20, $paginatorOptions['n']);
        $this->assertEquals(3, $paginatorOptions['p']);
    }

    /*
     * public function testInitPaginatorPDO()
     * {
     * $reflectionClass = new \ReflectionClass('Dal\Mapper\AbstractMapper');
     *
     * $m_abstract_mapper = $this->getMockBuilder('Dal\Mapper\AbstractMapper')->setMethods(null)->disableOriginalConstructor()->getMock();
     *
     * $reflection = $reflectionClass->getProperty('paginatorOptions');
     * $reflection->setAccessible(true);
     * $reflection->setValue($m_abstract_mapper, array('n' => 10, 'p' => 2));
     *
     * $reflection = $reflectionClass->getMethod('initPaginator');
     * $reflection->setAccessible(true);
     *
     * $array_params = array('select * fromm toto', array('id' => 1));
     * $out = $reflection->invokeArgs($m_abstract_mapper, array($array_params));
     *
     *
     *
     * $reflection = $reflectionClass->getProperty('usePaginator');
     * $reflection->setAccessible(true);
     * $this->assertFalse($reflection->getValue($m_abstract_mapper));
     *
     * $this->assertEquals('select * fromm toto LIMIT 10 OFFSET 10', $out);
     *
     * $reflection = $reflectionClass->getProperty('paginator');
     * $reflection->setAccessible(true);
     *
     * $paginator = $reflection->getValue($m_abstract_mapper);
     *
     * $this->assertEquals('select * fromm toto', $paginator[0]);
     * $this->assertEquals(1, $paginator[1]['id']);
     * }
     */
    
    public function testCountWithoutPagination()
    {
        $m_resultset = $this->getMockBuilder('\Dal\Db\ResultSet\ResultSet')
            ->setMethods(array('count'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_resultset->expects($this->any())
            ->method('count')
            ->will($this->returnValue(5));
        
        $m_abstract_mapper = $this->getMockBuilder('\Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        
        $reflectionClass = new \ReflectionClass('Dal\Mapper\AbstractMapper');
        $reflection = $reflectionClass->getProperty('result');
        $reflection->setAccessible(true);
        $reflection->setValue($m_abstract_mapper, $m_resultset);
        
        $this->assertEquals(5, $m_abstract_mapper->count());
    }

    public function testCountWithPaginationObjPaginator()
    {
        $m_pagination = $this->getMockBuilder('\Dal\Paginator\Paginator')
            ->setMethods(array('getTotalItemCount'))
            ->disableOriginalConstructor()
            ->getMock();
        
        $m_pagination->expects($this->any())
            ->method('getTotalItemCount')
            ->will($this->returnValue(6));
        
        $m_abstract_mapper = $this->getMockBuilder('\Dal\Mapper\AbstractMapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        
        $reflectionClass = new \ReflectionClass('Dal\Mapper\AbstractMapper');
        $reflection = $reflectionClass->getProperty('paginator');
        $reflection->setAccessible(true);
        $reflection->setValue($m_abstract_mapper, $m_pagination);
        
        $this->assertEquals(6, $m_abstract_mapper->count());
    }

}
