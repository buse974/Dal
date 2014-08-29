<?php

namespace Dal\Db\Sql;

use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Select as BaseSelect;

class Select extends BaseSelect
{
    const JOIN_CROSS = 'cross';

    protected function aliasing(array $columns, $prefix)
    {
        if (is_array($prefix)) {
            $prefix = key($prefix);
        }

        foreach ($columns as $key => $column) {
            if (is_string($column)) {
                if ($column == self::SQL_STAR) {
                    continue;
                }
                unset($columns[$key]);
                $columns[$prefix . '$' . $column] = $column;
            }
        }

        return $columns;
    }

    public function columns(array $columns, $prefixColumnsWithTable = true)
    {
        $columns = $this->aliasing($columns, $this->table);

        return parent::columns($columns, $prefixColumnsWithTable);
    }

    public function join($name, $on, $columns = self::SQL_STAR, $type = self::JOIN_INNER)
    {
        if (is_array($name) && (!is_string(key($name)) || count($name) !== 1)) {
            throw new Exception\InvalidArgumentException(
                sprintf("join() expects '%s' as an array is a single element associative array", array_shift($name))
            );
        }
        if (!is_array($columns)) {
            $columns = array($columns);
        }

        $columns = $this->aliasing($columns, $name);

        $this->joins[] = array(
            'name'    => $name,
            'on'      => $on,
            'columns' => $columns,
            'type'    => $type
        );

        return $this;
    }
}
