<?php

namespace Dal\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractService implements ServiceLocatorAwareInterface
{
    protected $serviceLocator;
    protected $mapper;

    public function __construct($mapper = null)
    {
        if ($mapper !== null) {
            $this->mapper = $mapper;
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
