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
use Dal\Model\AbstractModel;

class ResultSet extends BaseResultSet implements JsonSerializable
{
	protected $bufferArrayObjectPrototype=false;

	/**
	 * To Array by parent id
	 * 
	 * @param string $chaine_parent
	 * @param string $chaine_id
	 * @param array $indices
	 * @param string $unset_indice
	 * 
	 * @return array
	 */
    public function toArrayParent($chaine_parent = 'parent_id', $chaine_id = 'id',array $indices = array(), $unset_indice = false)
    {
        $array=$this->toArray();

        $num=0;
        $final=array();
        do {
            $is_present = false;
            foreach ($array as $key => $row) {
                if ($row[$chaine_parent]==$num) {
                	if(count($indices) > 0) {
                		$buffer = &$final;
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
                		$final[] = $row;
                	}
                    $num = $row[$chaine_id];
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
    public function toArrayCurrent(array $indices = array(), $unset_indice = false)
    {
    	$return = array();
    	foreach ($this as $row) {
    		if($row instanceof AbstractModel) {
    			$row = $row->toArrayCurrent();
    		} elseif(method_exists($row, 'toArray')) {
    			$row = $row->toArray();
    		} elseif (method_exists($row, 'getArrayCopy')) {
    			$row = $row->getArrayCopy();
    		}
    			
    			
    		if(!is_array($row)) {
    			throw new \RuntimeException(
    					'Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array Current'
    			);
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
    
    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
    	return $this->toArray();
    }
}
