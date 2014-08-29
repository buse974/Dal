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
        return (strpos($requestedName, 'dal_mapper_')===0);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $name_table = substr($requestedName, 11);
        $adapter = $serviceLocator->get($this->getConfig($serviceLocator)['adapter']);

        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype($serviceLocator->get('dal_model_' . $name_table));
        $tableGateway = new TableGateway($name_table, $adapter, null, $resultSetPrototype, new Sql($adapter, $name_table));
        $obj = 'Dal\\Mapper\\' . $this->toCamelCase($name_table);

        return new $obj($tableGateway);
    }
}
