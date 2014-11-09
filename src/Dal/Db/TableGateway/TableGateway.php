<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Dal\Db\TableGateway;

use Zend\Db\TableGateway\TableGateway as BaseTableGateway;
use Zend\Db\Adapter\Adapter as ADT;
use Zend\Db\Metadata\Metadata;
use Dal\Db\ResultSet\ResultSet;

class TableGateway extends BaseTableGateway
{
    protected $primary;

   /**
    *
    * @param  string                      $sql
    * @param  array                       $param
    * @return \Dal\Db\ResultSet\ResultSet
    */
    public function selectPdo($select,$param = null)
    {
           $statement = $this->getAdapter()->query($select,ADT::QUERY_MODE_PREPARE);
           $result = $statement->execute($param);

           $resultSet = clone $this->resultSetPrototype;

           return $resultSet->initialize($result);
   }

   /**
    * 
    * @param string $sql
    * @param array  $param
    */
    public function requestPdo($request,$param = null)
    {
          $statement = $this->getAdapter()->query($request,ADT::QUERY_MODE_PREPARE);
          $res = $statement->execute($param)->count();

          $this->lastInsertValue = $this->getAdapter()->getDriver()->getLastGeneratedValue();
          $statement->getResource()->closeCursor();

          return $res;
    }
    
    /**
     * Select whith PDO without mappage
     * 
     * @param string $sql
     * @param array  $param
     */
    public function selectNMPdo($request,$param = null)
    {
    	$statement = $this->getAdapter()->query($request,ADT::QUERY_MODE_PREPARE);
    	$result = $statement->execute($param);
    
    	$resultSet = new ResultSet();
    	
    	return $resultSet->initialize($result);
    }

    /**
     * Get Primary Keys
     */
    public function getPrimaryKey()
    {
        if (null === $this->primary) {
            $metadata = new Metadata($this->adapter);
            $constraints = $metadata->getConstraints($this->getTable());

            foreach ($constraints as $constraint) {
                if ($constraint->getType()=='PRIMARY KEY') {
                    $this->primary = $constraint->getColumns();
                }
            }
        }

        return $this->primary;
    }

}
