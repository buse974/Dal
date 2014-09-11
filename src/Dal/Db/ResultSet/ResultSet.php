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
	
    public function jsonSerialize()
    {
       return $this->toArray();
    }

    public function toArrayParent($chaine_parent = 'parent_id', $chaine_id = 'id', $indice = null)
    {
        $array=$this->toArray($indice);

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
    public function toArray($indice = null, $next_indice = null)
    {
    	$return = array();
    	foreach ($this as $row) {
    		if (is_array($row)) {
    			if($indice!==null && isset($row[$indice])) {
    				if($next_indice!==null && isset($row[$next_indice])) {
    					$return[$row[$indice]][$row[$next_indice]] = $row;
    				} else {
    					$return[$row[$indice]] = $row;
    				}
    			} else {
    				$return[] = $row;
    			}
    		} elseif (method_exists($row, 'toArray')) {   			
    			if($indice!==null && ($methode = str_replace(array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z'),array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'), 'get_' . $indice)) && method_exists($row,$methode)) {
    				if($next_indice!==null && ($next_methode = str_replace(array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z'),array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'), 'get_' . $next_indice)) && method_exists($row,$next_methode)) {
    					$return[$row->$methode()][$row->$next_methode()] = $row->toArray();
    				} else {
    					$return[$row->$methode()] = $row->toArray();
    				}
    			} else {
    				$return[] = $row->toArray();
    			}
    		} elseif (method_exists($row, 'getArrayCopy')) {
    			$tmp_array = $row->getArrayCopy();
    			if($indice!==null && isset($tmp_array[$indice])) {
    				if($next_indice!==null && isset($tmp_array[$next_indice])) {
    					$return[$tmp_array[$indice]][$tmp_array[$next_indice]] = $tmp_array;
    				} else {
    					$return[$tmp_array[$indice]] = $tmp_array;
    				}
    			} else {
    				$return[] = $tmp_array;
    			}
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
    public function toArrayCurrent($indice = null)
    {
    	$return = array();
    	foreach ($this as $row) {
    		if (is_array($row)) {
    			if($indice!==null && isset($row[$indice])) {
    				$return[$row[$indice]] = $row;
    			} else {
    				$return[] = $row;
    			}
    		} elseif (method_exists($row, 'toArrayCurrent')) {
    			if($indice!==null && method_exists($row, 'get' . ucfirst($indice))) {
    				$return[$row->{'get' . ucfirst($indice)}()] = $row->toArrayCurrent();
    			} else {
    				$return[] = $row->toArrayCurrent();
    			}
    		} else {
    			throw new Exception\RuntimeException(
    					'Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array Current'
    			);
    		}
    	}
    
    	return $return;
    }

    public function toArrayGroup($indice)
    {
        if (!is_array($indice)) {
            $indice = array($indice);
        }

        $array = $this->toArray();
        $doubleTableau = array();
        $newArray = array();
        $ident = true;

        foreach ($array as $elm) {
            if (count($newArray)!==0) {
                foreach ($indice as $ind) {
                    $ident &= ($elm[$ind] == $newArray[0][$ind]);
                }
            }
            if ($ident) {
                $newArray[] = $elm;
            } else {
                $doubleTableau[] = $newArray;
                $newArray = array();
                $ident = true;
                $newArray[] = $elm;
            }
        }
        $doubleTableau[] = $newArray;
        // ici on rassemble tout les tableaux double
        foreach ($doubleTableau as $tt) {
            if (count($tt)==1) {
                $mod =array();
                // si on a qu'un seule element on met dans un tableau les groups demandé
                foreach (current($tt) as $key => $val) {
                    $mod[$key] = (in_array ($key , $indice)) ? $val :  $mod[$key] = array($val);
                }
            } else {
                $mod = $this->group($tt,$indice);
            }
            $ret[] = $mod;
        }

        return $ret;
    }

    public function group($array,$indice)
    {
            $tabr = array();
            foreach ($array as $tab) {
                $tabr = array_merge_recursive($tabr,array_map("serialize", $tab ));
            }

            $tabr = array_map('array_reverse',array_map('array_unique',$tabr));

            foreach ($tabr as $key => $tt) {
                $tt = array_map('unserialize',$tabr[$key]);
                $tabr[$key] = (in_array ($key , $indice)) ?  $tt[0] : $tt;
            }

            return $tabr;
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
}
