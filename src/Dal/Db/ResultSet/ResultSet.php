<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Db
 */

namespace Dal\Db\ResultSet;

use JsonSerializable;
use Zend\Db\ResultSet\ResultSet as BaseResultSet;
/**
 * @package    Dal_Db
 * @subpackage ResultSet
 */
class ResultSet extends BaseResultSet implements JsonSerializable
{
	protected $bufferArrayObjectPrototype=false;

    public function toArrayParent($chaine_parent = 'parent_id', $chaine_id = 'id', $indice = null)
    {
        $array=$this->toArray(array($indice));

        $num=0;
        $final=array();
        do {
            $is_present = false;
            foreach ($array as $key => $elm) {
                if ($elm[$chaine_parent]==$num) {
                	if(null!==$indice) {
                    	$final[$key] = $elm;
                	} else {
                		$final[] = $elm;
                	}
                    $num = $elm[$chaine_id];
                    unset($array[$key]);
                    $is_present = true;
                    break;
                }
            }
        } while ($is_present);

        return $final;
    }
    
    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray(array $indices = array(), $unset_indice = false)
    {
    	$return = array();
    	foreach ($this as $row) {
    		if(method_exists($row, 'toArray')) { 
    			$row = $row->toArray();
    		} elseif (method_exists($row, 'getArrayCopy')) {
    			$row = $row->getArrayCopy();
    		}
    		if(count($indices) > 0) {
    			$buffer = &$return;
	    		foreach ($indices as $indice) {
	    			if(isset($row[$indice])) {
	    				$buffer = &$buffer[$row[$indice]];
	    				if($unset_indice) {
	    					unset($row[$indice]);
	    				}
	    			}
	    		}
	    		$buffer = $row;
    		}else {
    			$return[] = $row;
    		}
    	}
    	
    	return $return;
    }
    
    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArrayCurrent($indices = null, $unset_indice = false)
    {
    	$return = array();
    	foreach ($this as $row) {
    		if(method_exists($row, 'toArrayCurrent')) {
    			$row = $row->toArrayCurrent();
    		} elseif(!is_array($row)) {
    			throw new Exception\RuntimeException(
    					'Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array Current'
    			);
    		}
    		
    		if($indices!==NULL) {
    			$buffer = &$return;
    			foreach ($indices as $indice) {
    				if(isset($row[$indice])) {
    					$buffer = &$buffer[$row[$indice]];
    					if($unset_indice) {
    						unset($row[$indice]);
    					}
    				}
    			}
    			$buffer = $row;
    		}else {
    			$return[] = $row;
    		}
    	}
    
    	return $return;
    }

    public function bufferArrayObjectPrototype()
    {
    	$this->buffer();
    	$this->bufferArrayObjectPrototype=true;
    }
    
    /**
     * Iterator: get current item
     *
     * @return array
     */
    public function current()
    {
    	$data = parent::current();
    	 
    	if($this->bufferArrayObjectPrototype && $this->buffer[$this->position]!==$data) {
    	 	$this->buffer[$this->position]=$data;
    	}
    	 
    	return $data; 
    }
    
    public function jsonSerialize()
    {
    	return $this->toArray();
    }
}
