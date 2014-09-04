<?php

return array(
    'service_manager' => array(
        'abstract_factories' => array(
                'Dal\AbstractFactory\MapperAbstractFactory',
                'Dal\AbstractFactory\ModelAbstractFactory',
                'Dal\AbstractFactory\ServiceAbstractFactory',
        ),
    ),
);
