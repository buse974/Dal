<?php

namespace Dal\Db\Sql;

use Zend\Db\Sql\Sql as BaseSql;
use Zend\Db\Sql\Exception\InvalidArgumentException;

class Sql extends BaseSql
{
    /**
     * (non-PHPdoc).
     *
     * @see \Zend\Db\Sql\Sql::select()
     */
    public function select($table = null)
    {
        if ($this->table !== null && $table !== null) {
            throw new InvalidArgumentException(sprintf(
                'This Sql object is intended to work with only the table "%s" provided at construction time.',
                $this->table
            ));
        }

        return new \Dal\Db\Sql\Select(($table) ?: $this->table);
    }
}
