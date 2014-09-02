<?php

namespace Dal\AbstractFactory;

use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceAbstractFactory extends AbstractFactory
{
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return (strpos($requestedName, 'dal_service_')===0);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $name = substr($requestedName, 12);
        $class = $this->getConfig($serviceLocator)['namespace']['service'] . '\\' . $this->toCamelCase($name);

        if (class_exists($class)) {
            $obj = new $class('dal_mapper_' . $name);
        } else {
            throw new \Exception('class is not exist : ' . $class);
        }

        return $obj;
    }
}
