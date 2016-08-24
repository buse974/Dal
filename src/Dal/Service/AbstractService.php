<?php

namespace Dal\Service;

abstract class AbstractService
{
    protected $container;
    protected $mapper;
    protected $model;

    public function __construct($params = null)
    {
        if ($params !== null) {
            $this->mapper = sprintf('%s_mapper_%s', $params['prefix'], $params['name']);
            $this->model = sprintf('%s_modelbase_%s', $params['prefix'], $params['name']);
        }
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
    
    public function usePaginator(array $options = array())
    {
        $this->getMapper()->usePaginator($options);

        return $this;
    }

    /**
     * Get Mapper
     * 
     * @return \Dal\Mapper\AbstractMapper
     */
    public function getMapper()
    {
        return $this->container->get($this->mapper);
    }

    /**
     * Get A Clone of Model
     * 
     * @return \Dal\Model\AbstractModel
     */
    public function getModel()
    {
        return clone $this->container->get($this->model);
    }
}
