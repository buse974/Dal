<?php

namespace Dal\Mapper;

use Dal\Db\Sql\Select;
use Dal\Db\TableGateway\TableGateway;
use Dal\Model\AbstractModel;
use Dal\Paginator\Paginator;

abstract class AbstractMapper
{
    /**
     * @var \Dal\Db\TableGateway\TableGateway
     */
    protected $tableGateway;
    protected $paginator;
    protected $paginatorOptions = array();
    protected $usePaginator = false;
    protected $container;
    /**
     * @var \Dal\Db\ResultSet\ResultSet
     */
    protected $result;

    /**
     * @var array
     */
    protected $primary_key;

    /**
     * Construct the model with the tablegateway.
     *
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Update a modele.
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

            return  $this->initPaginator($sl);
        }

        $this->result = $this->tableGateway->select($model->toArrayCurrent(), $order);

        return $this->result;
    }

    /**
     * Excecute request directly by PDO.
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
     * Excecute select directly by PDO.
     *
     * @param string $select
     * @param array  $param
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function selectBridge(Select $select)
    {
        if ($this->usePaginator === true) {
            return $this->initPaginator($select);
        }

        $this->result = $this->tableGateway->selectBridge($select);

        return $this->result;
    }

    /**
     * Excecute select directly by PDO.
     *
     * @param string $select
     * @param array  $param
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function selectPdo($select, $param = null)
    {
        if ($this->usePaginator === true) {
            return $this->initPaginator(array($select, $param));
        }
        $this->result = $this->tableGateway->selectPdo($select, $param);

        return $this->result;
    }

    /**
     * Excecute select directly by PDO wihtout Mappage Model.
     *
     * @param string $select
     * @param array  $param
     *
     * @return \Dal\Db\ResultSet\ResultSet
     */
    public function selectNMPdo($select, $param = null)
    {
        if ($this->usePaginator === true) {
            return $this->initPaginator(array($select, $param));
        }
        $this->result = $this->tableGateway->selectNMPdo($select, $param);

        return $this->result;
    }

    /**
     * Select request.
     *
     * @param \Zend\Db\Sql\Select
     *
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
     * Fetch All.
     *
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll()
    {
        if ($this->usePaginator === true) {
            return $this->initPaginator($this->tableGateway->getSql()->select());
        }
        $this->result = $this->tableGateway->select();

        return $this->result;
    }

    /**
     * Delete request.
     *
     * @param \Zend\Db\Sql\Delete
     *
     * @return int
     */
    public function deleteWith(\Zend\Db\Sql\Delete $delete)
    {
        return $this->tableGateway->deleteWith($delete);
    }

    /**
     * Insert request.
     *
     * @param \Zend\Db\Sql\Insert
     *
     * @return int
     */
    public function insertWith(\Zend\Db\Sql\Insert $insert)
    {
        return $this->tableGateway->insertWith($insert);
    }

    /**
     * Update request.
     *
     * @param \Zend\Db\Sql\Update
     *
     * @return int
     */
    public function updateWith(\Zend\Db\Sql\Update $update)
    {
        return $this->tableGateway->updateWith($update);
    }

    /**
     * Get row.
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
     * Insert a new modele.
     *
     * @param \Dal\Model\AbstractModel $model
     *
     * @return int
     */
    public function insert(AbstractModel $model)
    {
        return $this->tableGateway->insert($model->toArrayCurrentNoPredicate());
    }

    /**
     * Get Last insert value.
     *
     * @return int
     */
    public function getLastInsertValue()
    {
        return $this->tableGateway->getLastInsertValue();
    }

    /**
     * Update a modele.
     *
     * @param \Dal\Model\AbstractModel $model
     * @param array                    $where
     *
     * @return int
     */
    public function update(AbstractModel $model, $where = null)
    {
        $datas = $model->toArrayCurrentNoPredicate();

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
     * Delete full modele.
     *
     * @param \Dal\Model\AbstractModel $model
     *
     * @return bool
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
     * Set the mapper options and enable the mapper.
     *
     * @param array $options
     *
     * @return AbstractMapper
     */
    public function usePaginator($options)
    {
        if ($options === null) {
            $this->usePaginator = false;

            return $this;
        }

        $this->usePaginator = true;

        $this->paginatorOptions['s'] = (isset($options['s'])) ? $options['s'] : null;
        $this->paginatorOptions['d'] = (isset($options['d'])) ? $options['d'] : null;
        $this->paginatorOptions['c'] = (isset($options['c'])) ? $options['c'] : null;
        $this->paginatorOptions['n'] = (isset($options['n'])) ? $options['n'] : 10;
        $this->paginatorOptions['o'] = (isset($options['o'])) ? $options['o'] : 'DESC';
        $this->paginatorOptions['p'] = (isset($options['p'])) ? $options['p'] : 1;

        return $this;
    }

    /**
     * Init the paginator with a select object.
     *
     * @param \Zend\Db\Sql\Select|array $select
     *
     * @return mixed
     */
    protected function initPaginator($select)
    {
        $this->usePaginator = false;

        $this->paginator = new Paginator(
                    $select,
                    $this->tableGateway->getAdapter(),
                    $this->tableGateway->getResultSetPrototype());

        if (isset($this->paginatorOptions['n'])) {
            $this->paginator->setN($this->paginatorOptions['n']);
        }
        if (isset($this->paginatorOptions['p'])) {
            $this->paginator->setP($this->paginatorOptions['p']);
        }
        if (isset($this->paginatorOptions['c'])) {
            $this->paginator->setC($this->paginatorOptions['c']);
        }
        if (isset($this->paginatorOptions['s'])) {
            $this->paginator->setS($this->paginatorOptions['s']);
        }
        if (isset($this->paginatorOptions['d'])) {
            $this->paginator->setD($this->paginatorOptions['d']);
        }
        if (isset($this->paginatorOptions['o'])) {
            $this->paginator->setO($this->paginatorOptions['o']);
        }

        return $this->paginator->getItems();
    }

    /**
     * @return integer|null
     */
    public function count()
    {
        $count = null;
        
        if ($this->paginator === null) {
            $count = $this->result->count();
        } elseif ($this->paginator instanceof Paginator) {
            $count = $this->paginator->getTotalItemCount();
        }

        return $count;
    }

    /**
     * Return request sql.
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
     * Set container
     *
     * @param \Interop\Container\ContainerInterface $container
     * @return \Dal\Model\AbstractModel
     */
    public function setContainer($container)
    {
        $this->container = $container;
    
        return $this;
    }
    
}
