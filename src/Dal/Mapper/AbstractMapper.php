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
    protected $usePaginator = false;
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
     *
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Update a modele
     *
     * @param \Dal\Model\AbstractModel $model
     * @param array                    $order
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function select(AbstractModel $model, $order = null)
    {
        if ($this->usePaginator === true) {
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

    /**
     * Excecute request directly by PDO
     *
     * @param string $request
     * @param array  $param
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function requestPdo($request, $param = null)
    {
        $this->result = $this->tableGateway->requestPdo($request, $param);

        return $this->result;
    }

    /**
     * Excecute select directly by PDO
     *
     * @param string $select
     * @param array  $param
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function selectPdo($select, $param = null)
    {
        if ($this->usePaginator === true) {
            $select = $this->initPaginatorPDO(array($select, $param));
        }
        $this->result = $this->tableGateway->selectPdo($select, $param);

        return $this->result;
    }

    /**
     * Excecute select directly by PDO wihtout Mappage Model
     *
     * @param string $select
     * @param array  $param
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function selectNMPdo($select, $param = null)
    {
        if ($this->usePaginator === true) {
            $select = $this->initPaginatorPDO(array($select, $param));
        }
        $this->result = $this->tableGateway->selectNMPdo($select, $param);

        return $this->result;
    }

    /**
     * Select request
     *
     * @param \Zend\Db\Sql\Select
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function selectWith(\Zend\Db\Sql\Select $select)
    {
        if ($this->usePaginator === true) {
            return $this->initPaginator($select);
        }

        $this->result = $this->tableGateway->selectWith($select);

        return $this->result;
    }

    /**
     * Fetch All
     *
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll()
    {
        if ($this->usePaginator === true) {
            return $this->initPaginator($this->tableGateway->getSql()->select());
        }
        $this->result =  $this->tableGateway->select();

        return $this->result;
    }

    /**
     * Delete request
     *
     * @param \Zend\Db\Sql\Delete
     *
     * @return integer
     */
    public function deleteWith(\Zend\Db\Sql\Delete $delete)
    {
        return $this->tableGateway->deleteWith($delete);
    }

    /**
     * Insert request
     *
     * @param \Zend\Db\Sql\Insert
     *
     * @return integer
     */
    public function insertWith(\Zend\Db\Sql\Insert $insert)
    {
        return $this->tableGateway->insertWith($insert);
    }

    /**
     * Update request
     *
     * @param \Zend\Db\Sql\Update
     *
     * @return integer
     */
    public function updateWith(\Zend\Db\Sql\Update $update)
    {
        return $this->tableGateway->updateWith($update);
    }

    /**
     * Get row
     *
     * @param string    $column
     * @param multitype $value
     *
     * @return \Dal\Model\AbstractModel
     */
    public function fetchRow($column, $value)
    {
        $where = array($column, $value);

        return $this->tableGateway->select($where)->current();
    }

    /**
     * Insert a new modele
     *
     * @param \Dal\Model\AbstractModel $model
     *
     * @return integer
     */
    public function insert(AbstractModel $model)
    {
        return $this->tableGateway->insert($model->toArrayCurrent());
    }

    /**
     * Get Last insert value
     *
     * @return integer
     */
    public function getLastInsertValue()
    {
        return $this->tableGateway->getLastInsertValue();
    }

    /**
     * Update a modele
     *
     * @param \Dal\Model\AbstractModel $model
     * @param array                    $where
     *
     * @return integer
     */
    public function update(AbstractModel $model, $where = null)
    {
        $datas = $model->toArrayCurrent();

        if ($where === null) {
            foreach ($this->tableGateway->getPrimaryKey() as $key) {
                $where[$key] = $datas[$key];
                unset($datas[$key]);
            }
        }

        return (count($datas) > 0) ?
            $this->tableGateway->update($datas, $where) : false;
    }

    /**
     * Delete full modele
     *
     * @param \Dal\Model\AbstractModel $model
     *
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
     * @param array $options
     *
     * @return AbstractMapper
     */
    public function usePaginator(array $options = array())
    {
        $this->usePaginator = true;

        $this->paginatorOptions['n'] = (isset($options['n'])) ? $options['n'] : 10;
        $this->paginatorOptions['p'] = (isset($options['p'])) ? $options['p'] : 1;

        return $this;
    }

    /**
     * Init the paginator with a select array
     *
     * @param array $select
     *
     * @return string
     */
    protected function initPaginatorPDO(array $select)
    {
        $this->usePaginator = false;
        $this->paginator = $select;

        return sprintf('%s LIMIT %s OFFSET %s', $select[0], $this->paginatorOptions['n'], (($this->paginatorOptions['p']-1)*$this->paginatorOptions['n']));
    }

    /**
     * Init the paginator with a select object
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return mixed
     */
    protected function initPaginator(\Zend\Db\Sql\Select $select)
    {
        $this->usePaginator = false;
        $this->paginator = new Paginator(new DbSelect(
                $select,
                $this->tableGateway->getAdapter(),
                $this->tableGateway->getResultSetPrototype()
        ));

        $this->paginator->setItemCountPerPage($this->paginatorOptions['n']);

        if($this->paginator->count() < $this->paginatorOptions['p']) {
        	return (new ResultSet())->initialize(array());
        } else {
        	return $this->paginator->getItemsByPage($this->paginatorOptions['p']);
        }
    }

    /**
     *
     * @return integer
     */
    public function count()
    {
        $count = null;

        if ($this->paginator === null) {
            $count = $this->result->count();
        } elseif ($this->paginator instanceof \Zend\Paginator\Paginator) {
            $count = $this->paginator->getTotalItemCount();
        } elseif (is_array($this->paginator)) {
            $count = $this->tableGateway->getAdapter()->query(sprintf('SELECT count(1) as `count` FROM ( %s ) C', $this->paginator[0]))->execute($this->paginator[1])->current()['count'];
        }

        return $count;
    }

    /**
     * Return request sql
     *
     * @param \Zend\Db\Sql\SqlInterface $request
     *
     * @return string
     */
    public function printSql(\Zend\Db\Sql\SqlInterface $request)
    {
        return $request->getSqlString($this->tableGateway->getAdapter()->getPlatform());
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
     * Get service locator
     *
     * @return ServiceLocatorInterface
    */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
