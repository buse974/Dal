<?php

/**
 * Zend Framework (http://framework.zend.com/).
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Dal\Db\TableGateway;

use Zend\Db\TableGateway\TableGateway as BaseTableGateway;
use Zend\Db\Adapter\Adapter as ADT;
use Zend\Db\Metadata\Metadata;
use Dal\Db\ResultSet\ResultSet;
use Dal\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSetInterface;

class TableGateway extends BaseTableGateway
{
    protected $primary;

    /**
     * Select.
     *
     * @param \Closure|string|array $where
     *
     * @return ResultSet
     */
    public function select($where = null, $order = null)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }

        $select = $this->sql->select();

        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }
        if ($order !== null) {
            $select->order($order);
        }

        return $this->selectWith($select);
    }

    /**
     * @param string $sql
     * @param array  $param
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function selectPdo($select, $param = null)
    {
        $statement = $this->getAdapter()->query($select, ADT::QUERY_MODE_PREPARE);
        $result = $statement->execute($param);

        $resultSet = clone $this->resultSetPrototype;

        return $resultSet->initialize($result);
    }

    /**
     * @param string $sql
     * @param array  $param
     */
    public function requestPdo($request, $param = null)
    {
        $statement = $this->getAdapter()->query($request, ADT::QUERY_MODE_PREPARE);
        $res = $statement->execute($param)->count();

        $this->lastInsertValue = $this->getAdapter()->getDriver()->getLastGeneratedValue();
        $statement->getResource()->closeCursor();

        return $res;
    }

    /**
     * Select whith PDO without mappage.
     *
     * @param string $sql
     * @param array  $param
     */
    public function selectNMPdo($request, $param = null)
    {
        $statement = $this->getAdapter()->query($request, ADT::QUERY_MODE_PREPARE);
        $result = $statement->execute($param);

        $resultSet = new ResultSet();

        return $resultSet->initialize($result);
    }

    /**
     * @param Select $select
     *
     * @return ResultSetInterface
     */
    public function selectBridge(Select $select)
    {
        // prepare and execute
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        // build result set
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * Get Primary Keys.
     */
    public function getPrimaryKey()
    {
        if (null === $this->primary) {
            $metadata = $this->getMetadata();
            $constraints = $metadata->getConstraints($this->getTable());

            foreach ($constraints as $constraint) {
                if ($constraint->getType() == 'PRIMARY KEY') {
                    $this->primary = $constraint->getColumns();
                }
            }
        }

        return $this->primary;
    }

    protected function getMetadata()
    {
        return new Metadata($this->adapter);
    }
}
