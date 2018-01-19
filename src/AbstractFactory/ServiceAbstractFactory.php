<?php
/**
 *
 * TagnCar (http://tagncar.com)
 *
 * ServiceAbstractFactory
 *
 */
namespace Dal\AbstractFactory;

use Interop\Container\ContainerInterface;

/**
 * Class ServiceAbstractFactory
 */
class ServiceAbstractFactory extends AbstractFactory
{

    /**
     * Determine if we can create a Service with name
     *
     * {@inheritdoc}
     *
     * @see \Zend\ServiceManager\Factory\AbstractFactoryInterface::canCreate()
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $namespace = $this->getConfig($container)['namespace'];
        $ar = explode('_', $requestedName);
        
        return (count($ar) >= 2 && array_key_exists($ar[0], $namespace) && $ar[1] === 'service');
    }

    /**
     * Create Model with name
     *
     * {@inheritdoc}
     *
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $prefix = current(explode('_', $requestedName));
        $namespace = $this->getConfig($container)['namespace'][$prefix];
        $name = substr($requestedName, strlen($prefix) + 9);
        
        $class = sprintf('%s\\%s', $namespace['service'], $this->toCamelCase($name));
        
        if (! class_exists($class)) {
            throw new \Exception('Class does not exist : ' . $class);
        }
        
        $obj = new $class(array('prefix' => $prefix,'name' => $name));
        $obj->setContainer($container);
        
        return $obj;
    }
}
