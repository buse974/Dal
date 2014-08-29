<?php

namespace Dal\Stdlib\Hydrator;

use Zend\Stdlib\Hydrator\ClassMethods as BaseClassMethods;

class ClassMethods extends BaseClassMethods
{
    public function extract($object)
    {
        $attributes = array();
        $methods = get_class_methods($object);

        foreach ($methods as $method) {
            if (strpos($method,'get')===0) {
                $attribute = substr(str_replace(array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'), array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z'), $method),4);
                $attributes[$attribute] = $object->$method();
            }
        }

        return $attributes;
    }

    /**
     * Hydrate an object by populating getter/setter methods
     *
     * Hydrates an object by getter/setter methods of the object.
     *
     * @param  array                            $data
     * @param  object                           $object
     * @return object
     * @throws Exception\BadMethodCallException for a non-object $object
     */
    public function hydrate(array $data, $object)
    {
        foreach ($data as $property => $value) {
            $method = str_replace(array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z'),array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'), 'set_' . $property);

            if (is_callable(array($object, $method))) {
                $object->$method($value);
            }
        }

        return $object;
    }
}
