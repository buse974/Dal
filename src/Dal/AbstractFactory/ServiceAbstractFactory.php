<?php

namespace Dal\AbstractFactory;

use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceAbstractFactory extends AbstractFactory
{
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
    	$namespace = $this->getConfig($serviceLocator)['namespace'];
    	$ar = explode('_', $requestedName);

    	return (count($ar) >= 2 && array_key_exists($ar[0], $namespace) && $ar[1]==='service');
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
    	$prefix = current(explode('_', $requestedName));
    	$namespace = $this->getConfig($serviceLocator)['namespace'][$prefix];
        $name = substr($requestedName, strlen($prefix) + 9);
      
        $class = $namespace['service'] . '\\' . $this->toCamelCase($name);
        if (class_exists($class)) {
            $obj = new $class($prefix . '_mapper_' . $name);
        } else {
            throw new \Exception('class is not exist : ' . $class);
        }

        return $obj;
    }
}
