<?php

namespace DalTest\Db\Sql;

use PHPUnit_Framework_TestCase;
use Dal\Db\Sql\Sql;

class SqlTest extends PHPUnit_Framework_TestCase
{
    public function testSelect()
    {
    	$m_sql = $this->getMockBuilder('Dal\Db\Sql\Sql')
    	              ->disableOriginalConstructor()
    	              ->setMethods(null)
    	              ->getMock();

        $out = $m_sql->select('table');

        $this->assertInstanceOf('Dal\Db\Sql\Select', $out);
    }
    
    public function testSelectException()
    {
    	$this->setExpectedException(
    			'InvalidArgumentException', 'This Sql object is intended to work with only the table "une_table" provided at construction time.'
    	);
    	
    	$m_sql = $this->getMockBuilder('Dal\Db\Sql\Sql')
    	              ->disableOriginalConstructor()
    	              ->setMethods(null)
    	              ->getMock();
    
    	$reflection = new \ReflectionClass('Dal\Db\Sql\Sql');
    	$table = $reflection->getProperty('table');
    	$table->setAccessible(true);
    	$table->setValue($m_sql, 'une_table');
    	
    	$m_sql->select('table');
    }
}
