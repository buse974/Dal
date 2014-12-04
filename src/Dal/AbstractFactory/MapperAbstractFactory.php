<?php

namespace Dal\AbstractFactory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Dal\Db\ResultSet\ResultSet;
use Dal\Db\Sql\Sql;
use Dal\Db\TableGateway\TableGateway;

class MapperAbstractFactory extends AbstractFactory
{
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $namespace = $this->getConfig($serviceLocator)['namespace'];
        $ar = explode('_', $requestedName);

        return (count($ar) >= 2 && array_key_exists($ar[0], $namespace) && $ar[1] === 'mapper');
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $prefix = current(explode('_', $requestedName));
        $namespace = $this->getConfig($serviceLocator)['namespace'][$prefix];
        $name_table = substr($requestedName, strlen($prefix) + 8);

        $adapter = $serviceLocator->get($this->getConfig($serviceLocator)['adapter']);

        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->bufferArrayObjectPrototype();
        $resultSetPrototype->setArrayObjectPrototype($serviceLocator->get($prefix.'_model_'.$name_table));
        $tableGateway = new TableGateway($name_table, $adapter, null, $resultSetPrototype, new Sql($adapter, $name_table));
        $obj =  $namespace['mapper'].'\\'.$this->toCamelCase($name_table);

        return new $obj($tableGateway);
    }
}
