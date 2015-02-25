<?php 

namespace Dal\Paginator;

use Dal\Db\ResultSet\ResultSet;
use Dal\Db\Sql\Sql;
use Dal\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Predicate\Between;
use Zend\Db\Sql\Predicate\Expression;

class DayPaginator
{
	/**
	 * 
	 * @var string
	 */
	protected $column;
	
	/**
	 * 
	 * @var string
	 */
	protected $start_date;
	
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
	
	protected $rowCount = null;
	
	protected $nbr_day = 20;
	
	/**
	 * @var ResultSet
	 */
	protected $resultSetPrototype = null;
	
	public function __construct(Select $select, $adapterOrSqlObject, ResultSetInterface $resultSetPrototype = null)
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
	 * @return ResultSet
	 */
	public function getItemsByPage($offset)
	{
		$select = clone $this->select;
		
		$date = new \DateTime($this->getStartDate(), new \DateTimeZone('UTC'));

		$date->sub(new \DateInterval(sprintf('P%dD', $offset*$this->nbr_day-1)));
		$start_date = $date->format('Y-m-j');
		
		$date->add(new \DateInterval(sprintf('P%dD', $this->nbr_day)));
		$end_date = $date->format('Y-m-j');
		
		$select->where(new Between($this->column, $start_date, $end_date));
		
		syslog(1, $this->sql->getSqlStringForSqlObject($select, $this->sql->getAdapter()->getPlatform()));
		$statement = $this->sql->prepareStatementForSqlObject($select);
		$result    = $statement->execute();
	    
		$resultSet = clone $this->resultSetPrototype;
		$resultSet->initialize($result);
	
		return $resultSet;
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
	
		$select = clone $this->select;
		$select->reset(Select::LIMIT);
		$select->reset(Select::OFFSET);
		$select->reset(Select::ORDER);
	
		$countSelect = new Select;
		$countSelect->columns(array('c' => new Expression('COUNT(1)')));
		$countSelect->from(array('original_select' => $select));
	
		$statement = $this->sql->prepareStatementForSqlObject($countSelect);
		$result    = $statement->execute();
		$row       = $result->current();
	
		$this->rowCount = $row['c'];
	
		syslog(1, $this->rowCount);
		
		return $this->rowCount;
	}
    
    public function setStartDate($start_date)
    {
    	$this->start_date = $start_date;
    	
    	return $this;
    }
    
    public function getStartDate()
    {
    	if(null === $this->start_date) {
    		$this->start_date = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-j');
    	}
    	
    	return $this->start_date;
    }
    
    public function setNbrDay($nbr_day)
    {
    	$this->nbr_day	= $nbr_day;
    	
    	return $this;
    }
    
    public function setColumn($column)
    {
    	$this->column = $column;
    	 
    	return $this;
    }
}