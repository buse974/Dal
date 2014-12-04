<?php

namespace DalTest\Db\Sql;

use PHPUnit_Framework_TestCase;
use Dal\Db\Sql\Sql;

class SqlTest extends PHPUnit_Framework_TestCase
{
    public function testSelect($table = null)
    {
        $m_sql = $this->getMockBuilder('Dal\Db\Sql\Sql')->disableOriginalConstructor()->setMethods(null)->getMock();

        $out = $m_sql->select('table');

        $this->assertInstanceOf('Dal\Db\Sql\Select', $out);
    }
}
