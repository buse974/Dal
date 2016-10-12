<?php
/**
 * 
 * 
 * TagnCar (http://tagncar.com/)
 * 
 * Paginator
 *
 */
namespace Dal\Paginator;

use Dal\Db\ResultSet\ResultSet;
use Dal\Db\Sql\Sql;
use Dal\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Predicate\Between;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Class Paginator
 */
class Paginator
{

    /**
     *
     * @var Sql
     */
    protected $sql = null;

    /**
     * Database query.
     *
     * @var Select
     */
    protected $select = null;

    /**
     *
     * @var int
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
     * @var string
     */
    protected $o = [];

    /**
     *
     * @var ResultSet
     */
    protected $result;

    /**
     *
     * @var ResultSet
     */
    protected $resultSetPrototype = null;

    /**
     * Setter Start
     *
     * @param mixed $s            
     * @return \Dal\Paginator\Paginator
     */
    public function setS($s)
    {
        $this->s = $s;
        
        return $this;
    }

    /**
     * Setter number Element
     *
     * @param int $n            
     * @return \Dal\Paginator\Paginator
     */
    public function setN($n)
    {
        $this->n = $n;
        
        return $this;
    }

    /**
     * Setter String DateTime
     *
     * @param int $d            
     * @return \Dal\Paginator\Paginator
     */
    public function setD($d)
    {
        $this->d = $d;
        
        return $this;
    }

    /**
     * Setter Page Number
     *
     * @param int $p            
     * @return \Dal\Paginator\Paginator
     */
    public function setP($p)
    {
        $this->p = $p;
        
        return $this;
    }

    /**
     * Setter array or string column
     *
     * ["name_field" => ">"]
     * ["name_field" => "DESC"]
     * "name_field" string if option o exist
     *
     * @param array|string $c            
     * @return \Dal\Paginator\Paginator
     */
    public function setC($c)
    {
        $this->c = $c;
        
        return $this;
    }

    /**
     * Setter Order for column
     *
     * @param unknown $o            
     * @return \Dal\Paginator\Paginator
     */
    public function setO($o)
    {
        $this->o = $o;
        
        return $this;
    }

    /**
     * Constructor Pagination
     *
     * @param array|Dal\Db\Sql\Select $select            
     * @param Adapter|Sql $adapterOrSqlObject            
     * @param ResultSetInterface $resultSetPrototype            
     * @throws \Exception
     */
    public function __construct($select, $adapterOrSqlObject, ResultSetInterface $resultSetPrototype = null)
    {
        $this->select = $select;
        
        if ($adapterOrSqlObject instanceof Adapter) {
            $adapterOrSqlObject = new Sql($adapterOrSqlObject);
        }
        
        if (! $adapterOrSqlObject instanceof Sql) {
            throw new \Exception('$adapterOrSqlObject must be an instance of Zend\Db\Adapter\Adapter or Zend\Db\Sql\Sql');
        }
        
        $this->sql = $adapterOrSqlObject;
        $this->resultSetPrototype = ($resultSetPrototype) ?: new ResultSet();
    }

    /**
     * Returns an ResultSet of items for a page.
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function getItems()
    {
        $statement = (null !== $this->c && null !== $this->d) ? $this->getStatementDay() : $this->getStatementPagination();
        $result = $statement->execute((is_array($this->select)) ? $this->select[1] : null);
        $resultSet = clone $this->resultSetPrototype;
        
        $this->result = $resultSet->initialize($result);
        
        return $this->result;
    }

    /**
     * Get Statement Pagination By Date
     *
     * @return \Zend\Db\Adapter\Driver\StatementInterface
     */
    public function getStatementDay()
    {
        if (null === $this->n) {
            $this->n = 1;
        }
        
        $date = new \DateTime($this->d, new \DateTimeZone('UTC'));
        
        $date->sub(new \DateInterval(sprintf('P%dD', ($this->p * $this->n) - 1)));
        $start_date = $date->format('Y-m-j');
        
        $date->add(new \DateInterval(sprintf('P%dD', $this->n)));
        $end_date = $date->format('Y-m-j');
        
        if (is_array($this->select)) {
            $query = sprintf('%s WHERE %s BETWEEN %s AND %s', $this->select[0], $this->c, $start_date, $end_date);
            $adt = $this->sql->getAdapter();
            $statement = $adt->query($query, $adt::QUERY_MODE_PREPARE);
        } else {
            $select = clone $this->select;
            $select->where(new Between($this->c, $start_date, $end_date));
            
            $statement = $this->sql->prepareStatementForSqlObject($select);
        }
        
        return $statement;
    }

    /**
     * Get Statement Pagination
     *
     * @return \Zend\Db\Adapter\Driver\StatementInterface
     */
    public function getStatementPagination()
    {
        if (null === $this->n) {
            $this->n = 10;
        }
        if (null !== $this->c && is_array($this->c)) {
            $co = (reset($this->c) === 'ASC' || reset($this->c) === '>') ? '>' : '<';
            $cc = key($this->c);
        } else 
            if (null !== $this->c && ! is_array($this->c)) {
                $co = ($this->o === 'ASC' || $this->o === '>') ? '>' : '<';
                $cc = $this->c;
                $this->o = [];
            }
        
        if (is_array($this->select)) {
            $query = $this->select[0];
            if (! empty($cc)) {
                // @TODO check choise AND or WHERE and insertion before ORDER LIMIT GROUPBY
                $query = sprintf('%s AND %s %s %s', $query, $cc, $co, $this->s);
            }
            $query = sprintf('%s LIMIT %d OFFSET %d', $query, $this->n, (($this->p - 1) * $this->n));
            $adt = $this->sql->getAdapter();
            
            $statement = $adt->query($query, $adt::QUERY_MODE_PREPARE);
        } else {
            $table = $this->select->getRawState(Select::TABLE);
            $cols = $this->select->getRawState(Select::COLUMNS);
            $ords = $this->select->getRawState(Select::ORDER) + $this->o;
            $fords = [];
            
            if (count($cols) === 1 && reset($cols) === '*') {
                foreach ($ords as $ok => $ov) {
                    if(strpos($ok, '.') === false) {
                        $fords[$ok] = $ov;
                    } else {
                        $tmp = explode('.', $ok);
                        if($tmp[0] === $table) {
                            $fords[$tmp[1]] = $ov;
                        }
                    }
                }
            } else {
                foreach ($ords as $ok => $ov) {
                    if (is_int($ok) && is_string($ov)) {
                        $tmp = explode(' ', $ov);
                        $ok = $tmp[0];
                        $ov = (count($tmp) === 2) ? $tmp[1] : 'ASC';
                    }
                    if(!is_int($ok)) {
                       $tmp = $this->checkColumns($ok, $table, $cols);
                       if ($tmp !== false) {
                           $fords[$tmp] = $ov;
                       }
                    }
                }
            }
            
            $Select = new Select();
            $Select->columns(array('*'));
            $Select->from(array('original_select' => $this->select));
            if (! empty($cc)) {
                if (count($cols) === 1 && reset($cols) === '*') {
                    if(strpos($cc, '.') !== false) {
                        $tmp = explode('.', $ok);
                        $cc = ($tmp[0] === $table) ? $tmp[1] : false;
                    }
                } else {
                    $cc = $this->checkColumns($cc, $table, $cols);
                }
                
                if ($cc !== false) {
                    $Select->where(array($cc . ' ' . $co . ' ?' => $this->s));
                }
            }
            $Select->offset((($this->p - 1) * $this->n));
            $Select->limit($this->n);
            $Select->order($fords);
            
            $statement = $this->sql->prepareStatementForSqlObject($Select);
        }
        
        return $statement;
    }

    /**
     * Returns the total number of rows in the result set
     *
     * @return int
     */
    public function getTotalItemCount()
    {
        if ($this->rowCount !== null) {
            return $this->rowCount;
        }
        
        $param = null;
        
        if (is_array($this->select)) {
            $select = $this->select[0];
            $param = $this->select[1];
            $adt = $this->sql->getAdapter();
            
            $statement = $adt->query(sprintf("SELECT COUNT(1) AS c FROM (%s) AS original_select", $select), $adt::QUERY_MODE_PREPARE);
        } else {
            $select = clone $this->select;
            $select->reset(Select::LIMIT);
            $select->reset(Select::OFFSET);
            $select->reset(Select::ORDER);
            
            $countSelect = new Select();
            $countSelect->columns(array('c' => new Expression('COUNT(1)')));
            $countSelect->from(array('original_select' => $select));
            
            $statement = $this->sql->prepareStatementForSqlObject($countSelect);
        }
        
        $result = $statement->execute($param);
        $row = $result->current();
        
        $this->rowCount = $row['c'];
        
        return $this->rowCount;
    }

    /**
     * Check Column name
     *
     * @param string $ok            
     * @return array
     */
    private function checkColumns($ok, $table, $cols)
    {
        $fords = false;
        
        foreach ($cols as $ck => $cv) {
            if ($cv instanceof Expression || is_string($cv)) {
                if ($ok === $ck) {
                    $fords = $ok;
                    break;
                } elseif ($cv instanceof Expression && $ok === $cv->getExpression()) {
                    $fords = $ck;
                    break;
                } elseif (is_string($cv)) {
                    if ($ok == $table . '.' . $cv) {
                        $fords = $ck;
                        break;
                    }
                }
            }
        }
        
        return $fords;
    }
}
