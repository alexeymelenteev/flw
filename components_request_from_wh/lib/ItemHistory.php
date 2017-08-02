<?php

class ItemHistory
{
    protected static $instance;
    protected static $oMySQL;
    protected static $oBAAN;
    
    public static $reasons = array("ECO", "RMA/REWORK");

    protected $properties = array();
    
    protected function __construct($oMySQL, $oBAAN, $data) { 
        if(self::$instance == null) {
            self::$instance = $this;
            self::$oMySQL = $oMySQL;
            self::$oBAAN = $oBAAN;
        }         
        $this->properties = $data;

        if ($this->getProperty('WO') != '') {
            $this->setNpiType();
        }
    }

    public static function getInstanceById($oMySQL, $oBAAN, $id) {
        $sql = "SELECT * FROM smt_for_repair.main WHERE id = " .$id;
        $oMySQL->query($sql);
        $data = $oMySQL->fetch();
         
        $object = new Item($oMySQL, $oBAAN, $data);       
        return $object;
    }

    private function setNpiType() {
    
        $this->setProperty('t_npif', '');
        
        $sql = "SELECT t1.t_npif FROM ttisfc001400 t1 WHERE t_pdno = " .$this->getProperty('WO');
    	self::$oBAAN->query($sql);
    	$row = self::$oBAAN->fetch();
    	if($row !== false) {
    	    $this->setProperty('t_npif', $row['t_npif']);
    	}        
    }

    public function getWhereUsed() {
            
		$sql = "SELECT t1.t_mitm, t2.t_dsca, t1.t_qana, t1.t_opno FROM ttibom010400 t1
				INNER JOIN ttiitm001400 t2
				ON t1.t_mitm = t2.t_item
				WHERE t1.t_sitm = '" .$this->getProperty('component') ."' AND to_char(t1.t_exdt) = '01/01/0001'";
    	self::$oBAAN->query($sql);
    	$rows = self::$oBAAN->fetchAll();
    	if($rows === false) {
    	    $rows = array();
    	}
    	return $rows;        
    }

    public function getProperty($name) {
        switch ($name) {
            case 'component':
                $component = str_pad($this->properties['component'], 16, " ", STR_PAD_LEFT);
                return $component;
            break;
            
            case 't_npif':
                return trim($this->properties['t_npif']);
            break;
            
            case 'mrb_material_receiving_date':
            case 'done_date':  
                if ($this->properties[$name] == '0000-00-00 00:00:00') {
                    return '';
                } else {
                    return $this->properties[$name];
                }                
            break;            
            
            default:
                $value = $this->properties[$name];
                if ($this->properties[$name] == null) {
                    $value = '';
                }
                return $value;
            break;
        }
    }

    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
        return $value;
    }

	public function toString_for_Excel() {
		return array('Customer number'=>$this->getProperty('cust_number'),
					 'Customer name'=>$this->getProperty('cust_name'), 
					 'component'=>$this->getProperty('component'),
                     'Description'=>$this->getProperty('desc'),		
					 'Qty'=>$this->getProperty('qty'),					 
					 'WO'=>$this->getProperty('WO'),					 
					 'position'=>$this->getProperty('position'),
					 'Assembly'=>$this->getProperty('assembly'),
					 'Requested date'=>$this->getProperty('requested_date'),
					 'Requester'=>$this->getProperty('requester'),
					 'Reason'=>$this->getProperty('reason'),
					 'Reciving material date'=>$this->getProperty('reciving_material_date'),
					 'price'=>$this->getProperty('price'),
		             'User remarks'=>$this->getProperty('user_remarks'),
		             'WH remarks'=>$this->getProperty('wh_remarks'));
	}    
}

?>