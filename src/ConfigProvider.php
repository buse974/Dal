<?php

namespace Dal;

use Interop\Http\ServerMiddleware\DelegateInterface;
use JRpc\Json\Server\Server;
use Zend\Diactoros\Response\JsonResponse;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'abstract_factories' => [
                Dal\AbstractFactory\MapperAbstractFactory::class,
                Dal\AbstractFactory\ModelAbstractFactory::class ,
                Dal\AbstractFactory\ModelBaseAbstractFactory::class,
                Dal\AbstractFactory\ServiceAbstractFactory::class,
            ],
        ];
    }
}
