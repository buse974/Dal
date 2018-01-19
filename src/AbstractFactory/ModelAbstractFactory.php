<?php
/**
 * 
 * TagnCar (http://tagncar.com)
 *
 * ModelAbstractFactory
 *
 */
namespace Dal\AbstractFactory;

use Interop\Container\ContainerInterface;

/**
 * Class ModelAbstractFactory
 */
class ModelAbstractFactory extends AbstractFactory
{

    /**
     * Determine if we can create a Model with name
     * 
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\AbstractFactoryInterface::canCreate()
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $namespace = $this->getConfig($container)['namespace'];
        $ar = explode('_', $requestedName);

        return (count($ar) >= 2 && array_key_exists($ar[0], $namespace) && $ar[1] === 'model');
    }
    
    /**
     * Create Model with name
     * 
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $prefix = current(explode('_', $requestedName));
        $namespace = $this->getConfig($container)['namespace'][$prefix];
        $name_table = $this->toCamelCase(substr($requestedName, strlen($prefix) + 7));
        
        $class = $namespace['model'].'\\'.$name_table;
        
        if (!class_exists($class)) {
            throw new \Exception('Class does not exist : '.$namespace['model'].'\\'.$name_table);
        }
        
        $obj = new $class();
        $obj->setContainer($container);
        
        return $obj;
    }
    
}
