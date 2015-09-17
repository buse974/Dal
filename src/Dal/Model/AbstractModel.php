<?php

namespace Dal\Model;

use JsonSerializable;
use Dal\Stdlib\Hydrator\ClassMethods;
use Zend\Db\Sql\Predicate\IsNull;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractModel implements JsonSerializable, ServiceLocatorAwareInterface
{
    /**
     * Prefix name.
     *
     * @var string
     */
    protected $prefix;
    protected $parent_model;
    protected $service_locator = null;
    private $delimiter = '$';

    /**
     * Construct model with associate table name.
     *
     * @param AbstractModel $parent_model
     * @param string        $prefix
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
        foreach ($data as &$val) {
            if (empty($val) && !is_numeric($val)) {
                $val = new IsNull();
            }
        }
        $formatted = array();

        if ($this->prefix !== null) {
            foreach ($data as $key => $value) {
                if (0 === strpos($key, $this->allParent().$this->delimiter)) {
                    $formatted[substr($key, strlen($this->allParent().$this->delimiter))] = $value;
                    unset($data[$key]);
                } elseif (0 === strpos($key, $this->prefix.$this->delimiter)) {
                    $formatted[substr($key, strlen($this->prefix.$this->delimiter))] = $value;
                    unset($data[$key]);
                }
            }
        }
        $hydrator = new ClassMethods();
        if (!empty($formatted)) {
            $hydrator->hydrate($formatted, $this);
        } else {
            $hydrator->hydrate($data, $this, true);
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
            } elseif (is_object($value)) {
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

        foreach ($vars as $key => &$value) {
            if ($value === null || (is_object($value) && !$value instanceof IsNull)) {
                unset($vars[$key]);
            } elseif (is_array($value)) {
                foreach ($value as $v) {
                    if (is_object($v)) {
                        unset($vars[$key]);
                        break;
                    }
                }
            } elseif (is_object($value) && $value instanceof IsNull) {
                $vars[$key] = null;
            } elseif (is_bool($value)) {
                $vars[$key] = (int) $value;
            }
        }

        return $vars;
    }

    public function allParent()
    {
        return (null !== $this->parent_model) ? $this->parent_model->allParent().'_'.$this->prefix : $this->prefix;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    protected function getParentArray()
    {
        return (null !== $this->parent_model) ?
             array_merge($this->parent_model->getParentArray(), array($this->prefix)) : array($this->prefix);
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
     * Set parent model.
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
     * return Model if needed.
     *
     * @param string $model
     * @param array  $data
     *
     * @return AbstractModel|null
     */
    public function requireModel($model, &$data, $prefix = null)
    {
        $class = null;
        $name = (null !== $prefix) ? $prefix : explode('_', $model)[2];

        foreach ($data as $k => $v) {
            if (strpos(explode('$', $k)[0], $name) !== false) {
                $class = clone $this->getServiceLocator()->get($model);
                $class->setPrefix($prefix);
                $class->setParentModel($this);
                $class->exchangeArray($data);
                break;
            }
        }

        return $class;
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
