<?php
/**
 *
 * TagnCar (http://tagncar.com)
 *
 * ModelAbstractFactory
 *
 */
namespace Dal\AbstractFactory;

use Dal\Db\ResultSet\ResultSet;
use Dal\Db\Sql\Sql;
use Dal\Db\TableGateway\TableGateway;
use Interop\Container\ContainerInterface;

/**
 * Class MapperAbstractFactory
 */
class MapperAbstractFactory extends AbstractFactory
{

    /**
     * Determine if we can create a Mapper with name
     * 
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\AbstractFactoryInterface::canCreate()
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $namespace = $this->getConfig($container)['namespace'];
        $ar = explode('_', $requestedName);
        
        return (count($ar) >= 2 && array_key_exists($ar[0], $namespace) && $ar[1] === 'mapper');
    }

    /**
     * Create Mapper with name
     * 
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $prefix = current(explode('_', $requestedName));
        $namespace = $this->getConfig($container)['namespace'][$prefix];
        $name_table = substr($requestedName, strlen($prefix) + 8);
        
        $adapter = $container->get($this->getConfig($container)['adapter']);
        
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->bufferArrayObjectPrototype();
        $resultSetPrototype->setArrayObjectPrototype($container->get($prefix . '_model_' . $name_table));
        $tableGateway = new TableGateway($name_table, $adapter, null, $resultSetPrototype, new Sql($adapter, $name_table));
        $class = $namespace['mapper'] . '\\' . $this->toCamelCase($name_table);
        
        $obj = new $class($tableGateway);
        $obj->setContainer($container);
        
        return $obj;
    }
}
