<?php
/**
 * 
 * Enter description here ...
 * @author Alexey Melenteev
 *
 */

require_once (FLW_DOC_ROOT . '/smt_for_repair/lib/Lot.php');

class LotsHolder implements IteratorAggregate, Countable {
    
    // each item should be 3 symbols, like this - ' MH' 
    protected static $logicWarehosesExcludedFromCalculateTotalAmount = array('BTN',
                                                                             'CRM',
                                                                             'FIX',
                                                                             'LOG',
                                                                             'LVR',
                                                                             'MOT',
                                                                             'MRB',
                                                                             'MRS',
                                                                             'MSC',
                                                                             'NEF',
                                                                             'ROW',
                                                                             'SEP',
                                                                             'SMI',
                                                                             'VRZ',
                                                                             'ZOO');
    protected static $locationsExcludedFromCalculateTotalAmount = array('INSPECT');
    
    protected $lotsHolder = array();
    
    public function addLot($row) {
        $lot = new Lot($row);
        $this->lotsHolder[] = $lot;
    }
    
    public function getStockOfAllWarehouses() {
        $sum = 0;
        foreach ($this->lotsHolder as $lot) {
            $sum += $lot->t_strs;
        }
        return $sum;
    }
    
    public function getStockExcludingWarehosesAndLocations() {
//        foreach($logicWarehosesExcludedFromCalculateTotalAmount as $warehouse) {
//            
//        }
//        $warehouse = str_pad($warehouse, 3, ' ', STR_PAD_LEFT);
        $sum = 0;
        
        foreach ($this->lotsHolder as $lot) {
            if(in_array($lot->t_cwar, self::$logicWarehosesExcludedFromCalculateTotalAmount)) {            
                continue;
            }

            if(in_array($lot->t_loca, self::$locationsExcludedFromCalculateTotalAmount)) {            
                continue;
            }            
            $sum += $lot->t_strs;
        }
        return $sum;
    }

    public function getLotsWithoutNEF() {
        $lotsHolder = array();
        
        foreach ($this->lotsHolder as $lot) {
            if ($lot->t_cwar == 'NEF') {
                continue;
            }
            array_push($lotsHolder, $lot);
        }
        return $lotsHolder;
    }

    public function getLotsWithNEF() {
        $lotsHolder = array();
        
        foreach ($this->lotsHolder as $lot) {
            if ($lot->t_cwar == 'NEF') {
                array_push($lotsHolder, $lot);
            }            
        }
        
        $lotsHolder = array_merge($lotsHolder, $this->getLotsWithoutNEF());
        return $lotsHolder;
    }    

    public function getIterator() {
        return new ArrayIterator($this->lotsHolder);
    }
    
    public function count() {
        return count($this->lotsHolder);
    }
}

?>