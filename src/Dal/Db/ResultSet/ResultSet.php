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
    public function jsonSerialize()
    {
       return $this->toArray();
    }

    public function toArray()
    {
        $ret = array();

        if ($this->count()>0) {
            $ret = parent::toArray();
        }

        return $ret;
    }

    public function toArrayParent($chaine_parent = 'parent_id', $chaine_id = 'id')
    {
        $array=$this->toArray();

        $num=0;
        $final=array();
        do {
            $is_present = false;
            foreach ($array as $key => $elm) {
                if ($elm[$chaine_parent]==$num) {
                    $final[] = $elm;
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
    public function toArrayCurrent()
    {
        $return = array();
        foreach ($this as $row) {
            if (is_array($row)) {
                $return[] = $row;
            } elseif (method_exists($row, 'toArrayCurrent')) {
                $return[] = $row->toArrayCurrent();
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
}
