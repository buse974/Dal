<?php
namespace Dal\Model;

use JsonSerializable;
use Dal\Stdlib\Hydrator\ClassMethods;
use Zend\Db\Sql\Predicate\IsNull;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Predicate\IsNotNull;

abstract class AbstractModel implements JsonSerializable, ServiceLocatorAwareInterface
{

    /**
     * Prefix name.
     *
     * @var string
     */
    protected $prefix;

    protected $array_prefix = null;

    protected $parent_model;

    protected $service_locator = null;

    protected $all_parent;

    private $delimiter = '$';

    private $delimiter_opt = '!';

    private $keep = true;

    /**
     * Construct model with associate table name.
     *
     * @param AbstractModel $parent_model            
     * @param string $prefix            
     */
    public function __construct(AbstractModel $parent_model = null, $prefix = null)
    {
        if ($prefix !== null) {
            $this->prefix = $prefix;
        }
        if ($parent_model !== null) {
            $this->parent_model = $parent_model;
        }
    }

    /**
     * Populate from an array.
     *
     * @param array $data            
     *
     * @return AbstractModel
     */
    public function exchangeArray(array &$data)
    {
        $hydrator = new ClassMethods();
        $formatted = [];
        if (null !== $this->prefix) {
            $pr = $this->allParent();
            $eq = ($pr === $this->prefix);
            foreach ($data as $key => $value) {
                $fkey = null;
                if (0 === ($tmp = strpos($key, $this->prefix . $this->delimiter))) {
                    $fkey = substr($key, strlen($this->prefix . $this->delimiter));
                } elseif ($eq === false && $tmp !== false && 0 === strpos($key, $pr . $this->delimiter)) {
                    $fkey = substr($key, strlen($pr . $this->delimiter));
                } elseif (0 === ($tmp = strpos($key, $this->prefix . $this->delimiter_opt))) {
                    $fkey = substr($key, strlen($this->prefix . $this->delimiter_opt));
                    if($value === null) {
                        $this->keep = false;
                    }
                } elseif ($eq === false && $tmp !== false && 0 === strpos($key, $pr . $this->delimiter_opt)) {
                    $fkey = substr($key, strlen($pr . $this->delimiter_opt));
                    if($value === null) {
                        $this->keep = false;
                    }
                }
                if (null !== $fkey && $hydrator->canBeHydrated($fkey, $this)) {
                    $formatted[$fkey] = ($value === null) ? new IsNull() : $value;
                    unset($data[$key]);
                }
            }
        }
        if (null === $this->prefix || empty($formatted)) {
            foreach ($data as $key => $value) {
                if ($hydrator->canBeHydrated($key, $this)) {
                    $formatted[$key] = ($value === null) ? new IsNull() : $value;
                    unset($data[$key]);
                }
            }
        }
        if(!empty($formatted) && $this->keep === true) {
            $hydrator->hydrate($formatted, $this);
        }
         
        return $this;
    }

    /**
     * Convert the model to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $hydrator = new ClassMethods();
        $vars = $hydrator->extract($this);
        
        foreach ($vars as $key => &$value) {
            if ($value === null) {
                unset($vars[$key]);
            } elseif (is_object($value) && ! $value instanceof \ArrayObject) {
                if (method_exists($value, 'toArray')) {
                    $value = $value->toArray();
                    if (count($value) == 0) {
                        unset($vars[$key]);
                    }
                } elseif ($value instanceof IsNull) {
                    $vars[$key] = null;
                } else {
                    unset($vars[$key]);
                }
            } elseif (is_bool($value)) {
                $vars[$key] = (int) $value;
            }
        }
        
        return $vars;
    }

    public function toArrayCurrent()
    {
        $hydrator = new ClassMethods();
        $vars = $hydrator->extract($this);
        
        $predivar = [];
        foreach ($vars as $key => &$value) {
            if ($value instanceof \Zend\Db\Sql\Predicate\PredicateInterface) {
                if (method_exists($value, 'setIdentifier')) {
                    $value->setIdentifier($key);
                }
                $predivar[] = $value;
                unset($vars[$key]);
            } elseif ($value === null || is_object($value)) {
                unset($vars[$key]);
            } elseif (is_array($value)) {
                foreach ($value as $v) {
                    if (is_object($v)) {
                        unset($vars[$key]);
                        break;
                    }
                }
            } elseif (is_bool($value)) {
                $vars[$key] = (int) $value;
            }
        }
        
        return array_merge($vars, $predivar);
    }

    public function toArrayCurrentNoPredicate()
    {
        $hydrator = new ClassMethods();
        $vars = $hydrator->extract($this);
        
        foreach ($vars as $key => &$value) {
            if ($value instanceof \Zend\Db\Sql\Predicate\IsNull) {
                $vars[$key] = null;
            } elseif ($value === null || is_object($value)) {
                unset($vars[$key]);
            } elseif (is_bool($value)) {
                $vars[$key] = (int) $value;
            }
        }
        
        return $vars;
    }

    public function allParent()
    {
        if (null === $this->all_parent) {
            $this->all_parent = (null !== $this->parent_model) ? $this->parent_model->allParent() . '_' . $this->prefix : $this->prefix;
        }
        
        return $this->all_parent;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    protected function getParentArray()
    {
        return (null !== $this->parent_model) ? array_merge($this->parent_model->getParentArray(), array($this->prefix)) : array($this->prefix);
    }

    /**
     * Return true if relation is boucle infinie.
     *
     * @return bool
     */
    protected function isRepeatRelational()
    {
        $is_repeat = false;
        
        if (array_count_values($this->getParentArray())[$this->prefix] > 1) {
            $is_repeat = true;
        }
        
        return $is_repeat;
    }

    /**
     *
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->service_locator = $serviceLocator;
        
        return $this;
    }

    /**
     * Get service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (null === $this->service_locator && null !== $this->parent_model) {
            $this->service_locator = $this->parent_model->getServiceLocator();
        }
        
        return $this->service_locator;
    }

    /**
     * Set parent model.
     *
     * @param AbstractModel $parent_model            
     */
    public function setParentModel(AbstractModel $parent_model)
    {
        $this->parent_model = $parent_model;
        
        return $this;
    }

    /**
     * Set prefix.
     *
     * @param int $parent_model            
     */
    public function setPrefix($prefix)
    {
        if (null !== $prefix) {
            $this->prefix = $prefix;
        }
        
        return $this;
    }

    /**
     * Set array prefix.
     *
     * @param
     *            integer array_prefix
     */
    public function setArrayPrefix($array_prefix)
    {
        if (null !== $array_prefix) {
            $this->array_prefix = $array_prefix;
        }
        
        return $this;
    }

    /**
     * return Model if needed.
     *
     * @param string $model            
     * @param array $data            
     * @param string $prefix            
     *
     * @return AbstractModel|null
     */
    public function requireModel($model, &$data, $prefix = null)
    {
        $class = null;
        $name = (null !== $prefix) ? $prefix : substr($model, strlen(explode('_', $model)[0]) + 7);
        
        foreach ($this->array_prefix as $k => $ap) {
            if (strrpos($ap, $name) === (strlen($ap) - strlen($name))) {
                unset($this->array_prefix[$k]);
                $class = clone $this->getServiceLocator()->get($model);
                $class->setPrefix($prefix);
                $class->setArrayPrefix($this->array_prefix);
                $class->setParentModel($this);
                $class->exchangeArray($data);
                if (! $class->Keep()) {
                    return new IsNull();
                }
                break;
            }
        }
        
        return $class;
    }

    public function Keep()
    {
        return $this->keep;
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }
}
