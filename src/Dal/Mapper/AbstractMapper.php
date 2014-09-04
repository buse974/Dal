<?php

namespace Dal\Mapper;

use Dal\Db\Sql\Select;
use Dal\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dal\Model\AbstractModel;
use Dal\Db\ResultSet\ResultSet;

abstract class AbstractMapper implements ServiceLocatorAwareInterface
{
    /**
     *
     * @var \Dal\Db\TableGateway\TableGateway
     */
    protected $tableGateway;
    protected $paginator;
    protected $paginatorOptions = array();
    protected $usePaginator;
    protected $serviceLocator;

    /**
     *
     * @var \Dal\Db\ResultSet\ResultSet
     */
    protected $result;

    /**
     *
     * @var array
     */
    protected $primary_key;

    /**
     * Construct the model with the tablegateway
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Update a modele
     * @param  \Dal\Model\AbstractModel    $model
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function select(AbstractModel $model,$order =null)
    {
        if ($this->usePaginator) {
            $this->usePaginator = false;

            $sl = $this->tableGateway->getSql()->select();

            $sl->where($model->toArrayCurrent());
            if ($order) {
                $sl->order($order);
            }
            $paginator = $this->initPaginator($sl);

            return $paginator;
        }

        $this->result = $this->tableGateway->select($model->toArrayCurrent());

        return $this->result;
    }

    public function requestPdo($request,$param = null)
    {
        $this->result = $this->tableGateway->requestPdo($request,$param);

        return $this->result;
    }

    /**
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function selectPdo($select,$param = null)
    {
        if ($this->usePaginator) {
            $this->usePaginator = false;

            $select = $this->initPaginator(array($select,$param));
        }
        $this->result = $this->tableGateway->selectPdo($select,$param);

        return $this->result;
    }

    /**
     * Get request
     * @param \Zend\Db\ResultSet\ResultSet
     */
    public function selectWith(\Zend\Db\Sql\Select $select)
    {
        if ($this->usePaginator) {
            $this->usePaginator = false;

            return $this->initPaginator($select);
        }

        $this->result = $this->tableGateway->selectWith($select);

        return $this->result;
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll()
    {
        if ($this->usePaginator) {
            $this->usePaginator = false;

            return $this->initPaginator($this->tableGateway->getSql()->select());
        }
        $this->result =  $this->tableGateway->select();

        return $this->result;
    }

    /**
     * delete request
     * @param \Zend\Db\ResultSet\ResultSet
     */
    public function deleteWith(\Zend\Db\Sql\Delete $delete)
    {
        return $this->tableGateway->deleteWith($delete);
    }

    /**
     * delete request
     * @param \Zend\Db\ResultSet\ResultSet
     */
    public function insertWith(\Zend\Db\Sql\Insert $insert)
    {
        return $this->tableGateway->insertWith($insert);
    }

    /**
     * update request
     * @param \Zend\Db\ResultSet\ResultSet
     */
    public function updateWith(\Zend\Db\Sql\Update $update)
    {
        return $this->tableGateway->updateWith($update);
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

    /**
     * Insert a new modele
     * @param  \Dal\Model\AbstractModel $model
     * @return integer
     */
    public function insert(AbstractModel $model)
    {
        return $this->tableGateway->insert($model->toArrayCurrent());
    }

    public function getLastInsertValue()
    {
        return $this->tableGateway->getLastInsertValue();
    }

    /**
     * Update a modele
     * @param  \Dal\Model\AbstractModel $model
     * @return integer
     */
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

    /**
     * Delete full modele
     *
     * @param  \Dal\Model\AbstractModel $model
     * @return boolean
     */
    public function delete(AbstractModel $model)
    {
        $array = $model->toArrayCurrent();

        if (empty($array)) {
            throw new \Exception('Error : delete used an empty model');
        }

        return $this->tableGateway->delete($array);
    }

    /**
     * Set the mapper options and enable the mapper
     *
     * @param  array          $options
     * @return AbstractMapper
     */
    public function usePaginator(array $options = array())
    {
        $this->usePaginator = true;
        $this->paginatorOptions = array_merge($this->paginatorOptions, $options);

        return $this;
    }

    /**
     * Check If option n and p exist. if exist usePagination is true else false
     *
     * @param  array                      $options
     * @return \Dal\Mapper\AbstractMapper
     */
    public function checkUsePagination($options=array())
    {
        if ($options!==null && is_array($options)) {
            if (array_key_exists('n', $options) && array_key_exists('p', $options)) {
                $this->usePaginator(array('n' => $options['n'],'p' => $options['p']));
            }
        }

        return $this;
    }

    /**
     * Init the paginator with a select object
     * @param  Zend\Db\Sql\Select $select
     * @return Paginator
     */
    public function initPaginator($select)
    {
        $ret = null;
        $options = $this->getPaginatorOptions();
        if (!isset($options['n'])) {
            $options['n'] = 10;
        }
        if (!isset($options['p'])) {
            $options['p'] = 1;
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
            $ret = $select[0] . ' LIMIT '.$options['n'].' OFFSET '.(($options['p']-1)*$options['n']);
        }

        return $ret;
    }

    /**
     *
     * @return integer
     */
    public function count()
    {
        $pag = $this->getPaginator();

        if ($pag instanceof \Zend\Paginator\Paginator) {
            return $pag->getTotalItemCount();
        } elseif (is_array($pag)) {
           $req_count = 'SELECT count(1) as `count` FROM ( ' . $pag[0] . ' ) C';
           $statement = $this->tableGateway->getAdapter()->query($req_count);
           $result = $statement->execute($pag[1]);

           return $result->current()['count'];
        }

        return $this->result->count();
    }

    public function printSql($select)
    {
        return $select->getSqlString($this->tableGateway->getAdapter()->getPlatform());
    }

    public function getPaginatorOptions()
    {
        return $this->paginatorOptions;
    }

    /**
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     *
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
    */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
