<?php

namespace DalTest\Mapper;

use \PHPUnit_Framework_TestCase;


class AbstractMapperTest extends PHPUnit_Framework_TestCase
{
    public function testSelect()
    {
    	$m_sql	    = $this->getMockBuilder('Dal\Db\Sql\Sql')->setMethods(array('where'))->disableOriginalConstructor()->getMock();
    	$m_sql->expects($this->any())->method('select')->will($this->returnSelf());
    	$m_sql->expects($this->any())->method('where')->will($this->returnSelf());
    	
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('select')->will($this->returnValue('resultset'));
    	$m_table_gateway->expects($this->any())->method('getSql')->will($this->returnValue($m_sql));
    	
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true,array('initPaginator'));
    	$m_abstract_mapper->expects($this->any())->method('initPaginator')->will($this->returnValue('paginition'));
    	$m_abstract_mapper->usePaginator();
    	$m_model = $this->getMockForAbstractClass('Dal\Model\AbstractModel');
    	$out = $m_abstract_mapper->select($m_model);
    	
    	$this->assertEquals('paginition', $out);
    }
    
    public function testSelectWhitoutPagination()
    {
    	$m_sql	    = $this->getMockBuilder('Dal\Db\Sql\Sql')->setMethods(array('where'))->disableOriginalConstructor()->getMock();
    	$m_sql->expects($this->any())->method('select')->will($this->returnSelf());
    	$m_sql->expects($this->any())->method('where')->will($this->returnSelf());
    	 
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('select')->will($this->returnValue('resultset'));
    	$m_table_gateway->expects($this->any())->method('getSql')->will($this->returnValue($m_sql));
    	 
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true,array());
    	$m_abstract_mapper->expects($this->any())->method('initPaginator')->will($this->returnValue('paginition'));
    	$m_model = $this->getMockForAbstractClass('Dal\Model\AbstractModel');
    	$out = $m_abstract_mapper->select($m_model);
    	 
    	$this->assertEquals('resultset', $out);
    }

    public function testRequestPdo()
    {
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('requestPdo')->with('request','param')->will($this->returnValue('result'));
    	 
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true,array());
    	$out = $m_abstract_mapper->requestPdo('request','param');
    	
    	$this->assertEquals('result', $out);
    }

    public function testSelectPdo()
    {
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('selectPdo')->with('request','param')->will($this->returnValue('result'));
    	
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true,array());
    	$out = $m_abstract_mapper->selectPdo('request','param');
    	 
    	$this->assertEquals('result', $out);
    }
    
    public function testSelectNMPdo()
    {
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('selectNMPdo')->with('request','param')->will($this->returnValue('result'));
    	 
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true,array());
    	$out = $m_abstract_mapper->selectNMPdo('request','param');
    	
    	$this->assertEquals('result', $out);
    }

    public function testSelectWith()
    {
    	$m_select	    = $this->getMockBuilder('Dal\Db\Sql\Select')->setMethods(array())->disableOriginalConstructor()->getMock();
    	
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('selectWith')->with($m_select)->will($this->returnValue('result'));
    	
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true);
    	$out = $m_abstract_mapper->selectWith($m_select);
    	 
    	$this->assertEquals('result', $out);
    }

    public function testFetchAll()
    {
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('select')->will($this->returnValue('result'));
    	 
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true);
    	$out = $m_abstract_mapper->fetchAll();
    	
    	$this->assertEquals('result', $out);
    }

    public function testDeleteWith()
    {
    	$m_delete	    = $this->getMockBuilder('\Zend\Db\Sql\Delete')->setMethods(array())->disableOriginalConstructor()->getMock();
    	 
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('deleteWith')->with($m_delete)->will($this->returnValue('result'));
    	 
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true);
    	$out = $m_abstract_mapper->deleteWith($m_delete);
    	
    	$this->assertEquals('result', $out);
    }

    public function testInsertWith()
    {
    	$m_insert	    = $this->getMockBuilder('\Zend\Db\Sql\Insert')->setMethods(array())->disableOriginalConstructor()->getMock();
    	
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('insertWith')->with($m_insert)->will($this->returnValue('result'));
    	
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true);
    	$out = $m_abstract_mapper->insertWith($m_insert);
    	 
    	$this->assertEquals('result', $out);
    }

    public function testUpdateWith()
    {
    	$m_update	    = $this->getMockBuilder('\Zend\Db\Sql\Update')->setMethods(array())->disableOriginalConstructor()->getMock();
    	 
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('updateWith')->with($m_update)->will($this->returnValue('result'));
    	 
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true);
    	$out = $m_abstract_mapper->updateWith($m_update);
    	
    	$this->assertEquals('result', $out);
    }

    public function testPrintSql()
    {
    	$m_select	    = $this->getMockBuilder('Dal\Db\Sql\Select')->setMethods(array('getSqlString'))->disableOriginalConstructor()->getMock();
    	$m_select->expects($this->any())->method('getSqlString')->will($this->returnValue('SELECT * FROM toto'));
    	 
    	$m_table_gateway = $this->getMockBuilder('Dal\Db\TableGateway\TableGateway')->disableOriginalConstructor()->setMethods(array()) ->getMock();
    	$m_table_gateway->expects($this->any())->method('getAdapter')->will($this->returnSelf());
    	$m_table_gateway->expects($this->any())->method('getPlatform')->will($this->returnSelf());
    	 
    	$m_abstract_mapper = $this->getMockForAbstractClass('Dal\Mapper\AbstractMapper',array($m_table_gateway),'',true,true,true,array('initPaginator'));
    	$out = $m_abstract_mapper->printSql($m_select);
    
    	$this->assertEquals('SELECT * FROM toto', $out);
    }
    
    protected function fetchRow($column, $value)
    {
        if (is_int($value)) {
            $where = $column . ' = ' . $value;
        } else {
            $where = array($column, $value);
        }
        $resultSet = $this->tableGateway->select($where);
        $result = $resultSet->current();

        return $result;
    }

    public function insert(AbstractModel $model)
    {
        return $this->tableGateway->insert($model->toArrayCurrent());
    }

    public function update(AbstractModel $model,$where = null)
    {
        $datas = $model->toArrayCurrent();

        if ($where === null) {
             foreach ($this->tableGateway->getPrimaryKey() as $key) {
                $where[$key] = $datas[$key];
                unset($datas[$key]);
            }
        }

        return (count($datas) > 0) ?
            $this->tableGateway->update($datas,$where) : false;
    }

    public function delete(AbstractModel $model)
    {
        $array = $model->toArrayCurrent();

        if (empty($array)) {
            throw new \Exception('Error : delete used an empty model');
        }

        return $this->tableGateway->delete($array);
    }

    public function getLastInsertValue()
    {
    	return $this->tableGateway->getLastInsertValue();
    }
    
    public function usePaginator(array $options = array())
    {
        $this->usePaginator = true;
        $this->paginatorOptions = array_merge($this->paginatorOptions, $options);

        return $this;
    }

    public function checkUsePagination($options=array())
    {
        if ($options!==null && is_array($options)) {
            if (array_key_exists('n', $options) && array_key_exists('p', $options)) {
                $this->usePaginator(array('n' => $options['n'],'p' => $options['p']));
            }
        }

        return $this;
    }

    function initPaginator($select)
    {
        $ret = null;
        $options = $this->getPaginatorOptions();
        if (!isset($options['n'])) {
            $options['n'] = 10;
        }
        if (!isset($options['p'])) {
            $options['p'] = 1;
        }
        if (!isset($options['s'])) {
        	$options['s'] = null;
        }

        if ($select instanceof \Zend\Db\Sql\Select) {
            $this->paginator = new Paginator(new DbSelect(
                    $select,
                    $this->tableGateway->getAdapter(),
                    $this->tableGateway->getResultSetPrototype()
            ));
            $this->paginator->setItemCountPerPage($options['n']);
            $ret = ($this->paginator->count() < $options['p']) ? (new \Dal\Db\ResultSet\ResultSet())->initialize(array()) : $this->paginator->getItemsByPage($options['p']);

        } elseif (is_array($select)) {
            $this->paginator = $select;
            $ret = sprintf('%s LIMIT %s OFFSET %s',$select[0],$options['n'],(($options['p']-1)*$options['n']));
        }

        return $ret;
    }

    public function testCount()
    {
       /* $pag = $this->getPaginator();

        if ($pag instanceof \Zend\Paginator\Paginator) {
            return $pag->getTotalItemCount();
        } elseif (is_array($pag)) {
           $req_count = sprintf('SELECT count(1) as `count` FROM ( %s ) C',$pag[0]);
           $statement = $this->tableGateway->getAdapter()->query($req_count);
           $result = $statement->execute($pag[1]);

           return $result->current()['count'];
        }

        return $this->result->count();*/
    }

    public function getPaginatorOptions()
    {
        return $this->paginatorOptions;
    }
}
