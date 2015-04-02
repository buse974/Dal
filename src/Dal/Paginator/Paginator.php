<?php 

namespace Dal\Paginator;

use Dal\Db\ResultSet\ResultSet;
use Dal\Db\Sql\Sql;
use Dal\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Predicate\Between;
use Zend\Db\Sql\Predicate\Expression;

class Paginator
{	
	/**
	 * @var Sql
	 */
	protected $sql = null;
	
	/**
	 * Database query
	 *
	 * @var Select
	 */
	protected $select = null;
	
	/**
	 * 
	 * @var integer
	 */
	protected $rowCount = null;
	
	
	/**
	 *
	 * @var string
	 */
	protected $d;
	
	/**
	 *
	 * @var string
	 */
	protected $s;
	
	/**
	 *
	 * @var string
	 */
	protected $n;
	
	/**
	 *
	 * @var string
	 */
	protected $p = 1;
	
	/**
	 *
	 * @var string
	 */
	protected $c;
	
	/**
	 * 
	 * @var ResultSet
	 */
	protected $result;
	
	/**
	 * @var ResultSet
	 */
	protected $resultSetPrototype = null;
	
	public function __construct($select, $adapterOrSqlObject, ResultSetInterface $resultSetPrototype = null)
	{
		$this->select = $select;
	
		if ($adapterOrSqlObject instanceof Adapter) {
			$adapterOrSqlObject = new Sql($adapterOrSqlObject);
		}
	
		if (!$adapterOrSqlObject instanceof Sql) {
			throw new \Exception(
					'$adapterOrSqlObject must be an instance of Zend\Db\Adapter\Adapter or Zend\Db\Sql\Sql'
			);
		}
	
		$this->sql                = $adapterOrSqlObject;
		$this->resultSetPrototype = ($resultSetPrototype) ?: new ResultSet;
	}
	
	/**
	 * Returns an array of items for a page.
	 *
	 * @param  int $offset Page offset
	 * 
	 * @return ResultSet
	 */
	public function getItems()
	{
	 	$statement = (null !== $this->c && null !== $this->d) ? $this->getStatementDay() : $this->getStatementPagination();
		$result    = $statement->execute((is_array($this->select)) ? $this->select[1]:null);
		$resultSet = clone $this->resultSetPrototype;
		
		$this->result = $resultSet->initialize($result);
		
		return $this->result;
	}
	
	public function getStatementDay()
	{
		if(null === $this->n) {
			$this->n=1;
		}
		
		$date = new \DateTime($this->d, new \DateTimeZone('UTC'));

		$date->sub(new \DateInterval(sprintf('P%dD', ($this->p*$this->n)-1)));
		$start_date = $date->format('Y-m-j');
		 
		$date->add(new \DateInterval(sprintf('P%dD', $this->n)));
		$end_date = $date->format('Y-m-j');

		if(is_array($this->select)) {
			$query = sprintf('%s WHERE %s BETWEEN %s AND %s', $this->select[0], $this->c, $start_date, $end_date);
		 	$statement = $this->sql->getAdapter()->query($query, ADT::QUERY_MODE_PREPARE);
		} else {
		 	$select = clone $this->select;
		    $select->where(new Between($this->c, $start_date, $end_date));
		 	
		    $statement = $this->sql->prepareStatementForSqlObject($select);
		}
		 
		return $statement;
	}
	
	public function getStatementPagination() 
	{
		if(null === $this->n) {
			$this->n=10;
		}
		
		if(is_array($this->select)) {
			$query = $this->select[0];
			if($this->s !== null && $this->c != null) {
				// @TODO check choise AND or WHERE and insertion before ORDER LIMIT GROUPBY
				$query = sprintf('%s AND %s < %s', $query, $this->c, $this->s);
			}
			$query = sprintf('%s LIMIT %s OFFSET %s', $query, $this->n, (($this->p-1)*$this->n));
			$statement = $this->sql->getAdapter()->query($query, ADT::QUERY_MODE_PREPARE);
		} else {
			$select = clone $this->select;
			$select->offset((($this->p-1)*$this->n));
			$select->limit($this->n);
			
			if($this->s !== null && $this->c != null) {
				$select->where(array($this->c . ' < ?' => $this->s));
			}

			$statement = $this->sql->prepareStatementForSqlObject($select);
		}
		
		return $statement;
	}
	
	/**
	 * Returns the total number of rows in the result set.
	 *
	 * @return int
	 */
	public function getTotalItemCount()
	{
		if ($this->rowCount !== null) {
			return $this->rowCount;
		}
	
		$param = null;
		
		if(is_array($this->select)) {
			$select = $this->select[0];
			$param = $this->select[1];
		} else {
			$select = clone $this->select;
			$select->reset(Select::LIMIT);
			$select->reset(Select::OFFSET);
			$select->reset(Select::ORDER);
		}
		
		$countSelect = new Select;
		$countSelect->columns(array('c' => new Expression('COUNT(1)')));
		$countSelect->from(array('original_select' => $select));
	
		$statement = $this->sql->prepareStatementForSqlObject($countSelect);
		$result    = $statement->execute($param);
		$row       = $result->current();
	
		$this->rowCount = $row['c'];
		
		return $this->rowCount;
	}
    
    public function setS($s)
    {
    	$this->s = $s;
    	
    	return $this;
    }
    
    public function setN($n)
    {
    	$this->n = $n;
    	
    	return $this;
    }
    
    public function setD($d)
    {
    	$this->d = $d;
    	 
    	return $this;
    }
    
    public function setP($p)
    {
    	$this->p = $p;
    
    	return $this;
    }
    
    public function setC($c)
    {
    	$this->c = $c;
    	 
    	return $this;
    }
}
 
