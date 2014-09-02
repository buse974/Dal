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
        return (strpos($requestedName, 'dal_model_')===0);
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
        $name_table = $this->toCamelCase(substr($requestedName, 10));

        if (class_exists($this->getConfig($serviceLocator)['namespace']['model'] . '\\' . $name_table . '\\Relational')) {
            $class = $this->getConfig($serviceLocator)['namespace']['model'] . '\\' . $name_table . '\\Relational';
        } elseif (class_exists($this->getConfig($serviceLocator)['namespace']['model'] . '\\' . $name_table)) {
            $class = $this->getConfig($serviceLocator)['namespace']['model'] . '\\' . $name_table;
        } else {
            echo "Not exist : " . $this->getConfig($serviceLocator)['namespace']['model'] . "\\" . $name_table . "\n";
        }

        return new $class();
    }
}
