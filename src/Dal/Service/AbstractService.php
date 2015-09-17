<?php

namespace Dal\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractService implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $mapper;
    protected $model;

    public function __construct($params = null)
    {
        if ($params !== null) {
            $this->mapper = sprintf('%s_mapper_%s', $params['prefix'], $params['name']);
            $this->model = sprintf('%s_model_%s', $params['prefix'], $params['name']);
        }
    }

    public function usePaginator(array $options = array())
    {
        $this->getMapper()->usePaginator($options);

        return $this;
    }

    /**
     * @return \Dal\Mapper\AbstractMapper
     */
    public function getMapper()
    {
        return $this->getServiceLocator()->get($this->mapper);
    }

    /**
     * @return \Dal\Model\AbstractModel
     */
    public function getModel()
    {
        return clone $this->getServiceLocator()->get($this->model);
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
