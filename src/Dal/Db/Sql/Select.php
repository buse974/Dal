<?php

namespace Dal\Db\Sql;

use Zend\Db\Sql\Select as BaseSelect;

class Select extends BaseSelect
{
    const JOIN_CROSS = 'cross';

    protected function aliasing(array $columns, $prefix)
    {
    	if(empty($prefix)) {
    		return $columns;
    	}
    	
        if (
        		is_array($prefix)) {
            $prefix = key($prefix);
        }

        foreach ($columns as $key => $column) {
            if (is_string($column)) {
                if ($column == self::SQL_STAR) {
                    continue;
                }
                unset($columns[$key]);
                $columns[$prefix.'$'.$column] = $column;
            }
        }

        return $columns;
    }

    public function columns(array $columns, $prefixColumnsWithTable = true)
    {
        $columns = $this->aliasing($columns, $this->table);

        return parent::columns($columns, $prefixColumnsWithTable); // @codeCoverageIgnore
    }

    public function join($name, $on, $columns = self::SQL_STAR, $type = self::JOIN_INNER)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }
        $columns = $this->aliasing($columns, $name);
        
        return parent::join($name, $on, $columns, $type); // @codeCoverageIgnore
    }
}
