<?php

/**
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */

return array(
        'dal-conf' => array(
                'adapter' => 'adapter',
                'namespace' => array(
                        'dal-test' => array(
                                'service' => 'Mock\\Service',
                                'mapper' => 'Mock\\Mapper',
                                'model' => 'Mock\\Model',
                        ),
                ),
        ),
);
