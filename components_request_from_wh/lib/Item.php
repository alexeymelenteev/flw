<?php
/**
 * 
 * @author Alexey Melenteev
 *
 */

require_once (FLW_DOC_ROOT . "/public/utilities/php_code/utilities_function.php");
require_once (FLW_DOC_ROOT . "/public/utilities/php_code/htmlMimeMail/htmlMimeMail.php");
require_once (FLW_DOC_ROOT . '/smt_for_repair/lib/LotsHolder.php');
require_once (FLW_DOC_ROOT . '/smt_for_repair/lib/Lot.php');

class Item {

    protected static $instance;
    protected static $oMySQL;
    protected static $oBAAN;
    protected static $controllersMail = array(); 
    
    public static $reasons = array("ECO", "RMA/REWORK");

    protected $properties = array();
    protected $arrChangesToBeUpdated = array();
    protected $lotsHolder;

    protected function __construct($oMySQL, $oBAAN, $data) { 
        if(self::$instance == null) {
            self::$instance = $this;
            self::$oMySQL = $oMySQL;
            self::$oBAAN = $oBAAN;
        }         
        $this->properties = $data;
        $this->lotsHolder = new LotsHolder();
        $this->setLotsHolder();
        
        if ($this->getProperty('WO') != '') {
            $this->setNpiType();
        }
        
    	if (in_array($this->getProperty('reason'), self::$reasons) && $this->getProperty('mail_sent_to_controller') == 0) {
            if ($this->sendMailAboutActionToControllers()) {
                $this->setProperty('mail_sent_to_controller', 1);
                $this->arrChangesToBeUpdated['mail_sent_to_controller'] = 1;
                $this->saveChanges();
            }
        }
    }
    
    public static function getInstanceByData($oMySQL, $oBAAN, $data) {        
        $object = new Item($oMySQL, $oBAAN, $data);       
        return $object;
    } 
    
    public static function getInstanceByComponent($oMySQL, $oBAAN, $component) {
        $data['component'] = $component;
                        
        $object = new Item($oMySQL, $oBAAN, $data);       
        return $object;
    }

    public static function getInstanceById($oMySQL, $oBAAN, $id) {
        $sql = "SELECT * FROM smt_for_repair.main WHERE id = " .$id;
        $oMySQL->query($sql);
        $data = $oMySQL->fetch();
         
        $object = new Item($oMySQL, $oBAAN, $data);       
        return $object;
    }    
    
    public function isNeededBeSentToHistory() {
        $today = new DateTime();
        $requested_date = new DateTime($this->getProperty('requested_date'));
        $interval = round(($today->format('U') - $requested_date->format('U')) / (60 * 60 * 24));
        
        if($interval > 30) {
            $this->sendMailAboutTransferToHistoryBecause30Days();
            $this->moveToHistory();
            return true;
        }
    }
        
    private function setLotsHolder() {        

         $sql = "SELECT t1.t_item, t1.t_loca, t1.t_clot, t1.t_strs, to_char(t1.t_date, '%d-%m-%Y') t_date, t2.t_mnum, t2.t_mitm, t1.t_cwar, trim(t3.t_dsca) manufacturer
                FROM ttdilc101400 t1 
                INNER JOIN ttdltc001400 t2
                ON t1.t_clot = t2.t_clot
                INNER JOIN ttcmcs950400 t3
                ON t2.t_mnum = t3.t_mnum 
				WHERE t1.t_item = '" . $this->getProperty("component") ."' 
				ORDER BY t1.t_date, t_clot, t_loca";
    	self::$oBAAN->query($sql);
    	while($row = self::$oBAAN->fetch()) {
    	    $this->lotsHolder->addLot($row);
    	}        
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
    
    public function getLotsHolder() {
        return $this->lotsHolder;
    }
    
    public function getLotsForPrint() {
	    if ($this->getProperty('t_npif') == 'NPI') {
            $lots = $this->lotsHolder->getLotsWithoutNEF();
        } else {
            $lots = $this->lotsHolder->getLotsWithNEF();
        }
        return $lots;
    }    
    
    public function getStockExcludingWarehosesAndLocations() {
        return $this->lotsHolder->getStockExcludingWarehosesAndLocations();
    }    
            
    private function moveToHistory($deletedBy = 'FLW')
    {
        $sql = "UPDATE smt_for_repair.main SET deleted = 1, deleted_date = now(), deleted_by = '" .$deletedBy 
                    ."' WHERE id = " .$this->getProperty('id');
        self::$oMySQL->query($sql);
        
//        $this->properties["deleted"] = 1;
//        $this->arrChangesToBeUpdated["deleted"] = 1;
//        $this->properties["deleted_date"] = new DateTime();
//        $this->arrChangesToBeUpdated["deleted_date"] = 1;
//        $this->properties["deleted_by"] = $deletedBy;
//        $this->arrChangesToBeUpdated["deleted_by"] = 1;        
//        $this->saveChanges();		
    } 

    public function getRequesterEmployeeNumber() {
        $employeeNumber = '';
        
        if($this->getProperty('requester') != '') {
            $employee = explode('-', $this->getProperty('requester'));
            $employeeNumber = trim($employee[0]);
        }
        return $employeeNumber;
    }
    
    public function getRequesterFullName() {
        $employeeNumber = '';
        
        if($this->getProperty('requester') != '') {
            $employee = explode('-', $this->getProperty('requester'));
            $employeeNumber = trim($employee[1]);
        }
        return $employeeNumber;
    }    

    private function getControllersByProject($cuno) {
        if (isset(self::$controllersMail[$cuno])) {
            return self::$controllersMail[$cuno];
        }
        
        $sql = "SELECT group_concat(distinct t2.t_info separator ',') emails 
                    FROM `mybaan`.`ttccom971400` t1
                    INNER JOIN `mybaan`.`ttccom001400` t2 ON (t1.t_emno= t2.t_emno)
                    INNER JOIN `mybaan`.`ttcmcs023400` t3 ON (t1.t_citg = t3.t_citg)
                    WHERE t1.t_code='PMG' AND t3.t_cuno = '" .$this->getProperty('cust_number') ."'";
         
    	self::$oMySQL->query($sql);
    	$row = self::$oMySQL->fetch();
    	$controllers = self::$controllersMail[$cuno] = $row['emails'];
        return $controllers;
    }    
    
    protected function sendMailAboutTransferToHistoryBecause30Days() {

            $from = "FLW <flw_web@il.flextronics.com>";
            $user = new User($this->getRequesterEmployeeNumber());
            $to = $user->getEmail();
            $cc = "alexey.melenteev@flextronics.com";
            $message = "The request regarding to data below can not be served <br /><br />";
            
            $tmp = "<head><style>table, th, td {border: 1px solid black;padding-left:10px;padding-right:10px};th{background-color:#9BD6D6};</style></head>";
            $tmp .= "<table cellpadding='2'><tr><th>Component</th><th>WO</th><th>Qty</th></tr>";
            $tmp .= '<tr><td>' .$this->getProperty('component') .'</td><td>' .$this->WO .'</td><td>' .$this->getProperty('qty') .'</td></tr>';
            $tmp .= '</table>';
            $message .= $tmp;
                        
            return $this->sendMail("SMT for repair.", $from, $message, $to, $cc);
    }   

    protected function sendMailAboutActionToControllers() {

            $from = "FLW <flw_web@il.flextronics.com>";
            $to = self::getControllersByProject($this->getProperty('cust_number'));
            $cc = "alexey.melenteev@flextronics.com";
            $message = "";
            
            $tmp = "<head><style>table, th, td {border: 1px solid black;padding-left:10px;padding-right:10px};th{background-color:#9BD6D6};</style></head>";
            $tmp .= "<table cellpadding='2'><tr><th>Component</th><th>Qty</th></tr>";
            $tmp .= '<tr><td>' .$this->getProperty('component') .'</td><td>' .$this->getProperty('qty') .'</td></tr>';
            $tmp .= '</table>';
            $message .= $tmp;
                        
            return $this->sendMail("Please Logged in to FLW and go to WH->SMT for repair.", $from, $message, $to, $cc);
    }

    public function sendMailOnDone() {

        $user = new User($this->getProperty('requester'));
        $from = "FLW <flw_web@il.flextronics.com>";
        $to = $user->getEmail();
        $cc = "alexey.melenteev@flextronics.com";
        $message = "";
        
        $tmp = "<head><style>table, th, td {border: 1px solid black;padding-left:10px;padding-right:10px};th{background-color:#9BD6D6};</style></head>";
        $tmp .= "<table cellpadding='2'><tr><th>Component</th><th>Qty</th></tr>";
        $tmp .= '<tr><td>' .$this->getProperty('component') .'</td><td>' .$this->getProperty('qty') .'</td></tr>';
        $tmp .= '</table>';
        $message .= $tmp;
                    
        return $this->sendMail("SMT for repair. Your request was served", $from, $message, $to, $cc);
    }    

    private function sendMail($subject, $from, $message, $to, $cc)
    {
        try
        {
            $result = sendInstantMail($subject, $from, $header = '', $message, $to, $cc);
                        
            if($result === false)
                throw new Exception();
            return true;
        } catch(Exception $e)
        {
            self::writeLog('Could not send mail to ' .$to .' with subject ' .$subject .'\r\n');
            return false;
        }
    } 	
	
    public function saveChanges()
    {
        if(count($this->arrChangesToBeUpdated) == 0)
            return;
        $sql = "UPDATE smt_for_repair.main SET ";
        foreach ($this->arrChangesToBeUpdated as $key => $value)
        {
            $sql_tmp .= "##" . $key . " = " .  $this->getProperty($key) . "##";
        }
        $sql .= $sql_tmp . " WHERE id = " . $this->getProperty("id");
        $sql = str_replace(array('####', '##'), array(",", ""), $sql);
    	self::$oMySQL->query($sql);
    	self::$oMySQL->fetch();
    }

    public function getProperty($name)
    {
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

    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
        return $value;
    }

    public function setMailOnDone($value)
    {
        $this->setProperty('mail_on_done', $value);
        $this->arrChangesToBeUpdated['mail_on_done'] = 1;
        return $this;
    }
    
    private static function writeLog($text) {
        $file = fopen("D:\\temp\\smt_for_repair.txt","w");
        echo fwrite($file, $text);
        fclose($file);        
    }
    
	public function toString_for_Excel()
	{
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