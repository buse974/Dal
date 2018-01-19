<?php
namespace Dal\AbstractFactory;

use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Interop\Container\ContainerInterface;

abstract class AbstractFactory implements AbstractFactoryInterface
{

    private $underscore_alpha = array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z');

    private $alpha = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

    protected $config_name = 'dal-conf';

    protected $config;

    public function getConfig(ContainerInterface $container)
    {
        if (null === $this->config) {
            if (! $container->has('config')) {
                return false;
            }
            
            $this->config = $container->get('config')[$this->config_name];
        }
        
        return $this->config;
    }

    public function toCamelCase($name)
    {
        return ucwords(str_replace($this->underscore_alpha, $this->alpha, $name));
    }
}
