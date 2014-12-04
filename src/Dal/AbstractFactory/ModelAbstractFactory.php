<?php

namespace Dal\AbstractFactory;

use Zend\ServiceManager\ServiceLocatorInterface;

class ModelAbstractFactory extends AbstractFactory
{
    /**
     * Determine if we can create a service with name
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $namespace = $this->getConfig($serviceLocator)['namespace'];
        $ar = explode('_', $requestedName);

        return (count($ar) >= 2 && array_key_exists($ar[0], $namespace) && $ar[1] === 'model');
    }

    /**
     * Create service with name
     *
     * @param  ServiceLocatorInterface           $serviceLocator
     * @param $name
     * @param $requestedName
     * @return \Dms\ServiceFactory\CodingFactory
    */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $prefix = current(explode('_', $requestedName));
        $namespace = $this->getConfig($serviceLocator)['namespace'][$prefix];
        $name_table = $this->toCamelCase(substr($requestedName, strlen($prefix) + 7));

        $class = $namespace['model'].'\\'.$name_table;

        if (!class_exists($class)) {
            throw new \Exception("Not exist : ".$namespace['model']."\\".$name_table."\n");
        }

        return new $class();
    }
}
