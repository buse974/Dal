<?php

return array(
    'service_manager' => array(
        'abstract_factories' => array(
                'Dal\AbstractFactory\MapperAbstractFactory',
                'Dal\AbstractFactory\ModelAbstractFactory',
                'Dal\AbstractFactory\ServiceAbstractFactory',
        ),
    ),
    'dal-conf' => array(
        'adapter' => 'db-adapter',
        'cache'   => 'storage_memcached',
        'log'     => 'log-system',
        'namespace' => array(
            'service' => 'Dal\\Service',
            'mapper'  => 'Dal\\Mapper',
            'model'   => 'Dal\\Model',
        )
    ),
);
