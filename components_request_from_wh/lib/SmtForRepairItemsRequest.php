<?php
/**
 * 
 * Enter description here ...
 * @author Alexey Melenteev
 *
 */

require_once '../init.php';
require_once (FLW_DOC_ROOT . '/php_code/session_header.php');
require_once (FLW_DOC_ROOT . '/public/utilities/class/gridHelper.class.php');
require_once (FLW_DOC_ROOT . '/public/utilities/class/excel.class.php');
require_once (FLW_DOC_ROOT . '/public/utilities/php_code/utilities_function.php');
require_once (FLW_DOC_ROOT . '/public/utilities/php_code/htmlMimeMail/htmlMimeMail.php');
require_once (FLW_DOC_ROOT . '/public/utilities/class/core/Autoloader.php');
require_once (FLW_DOC_ROOT . '/smt_for_repair/lib/Item.php');

spl_autoload_register(array('Autoloader', 'loadCoreClass'));

class SmtForRepairItemsRequest 
{
	private static $arrNPI_Type = array("", "NPI", "Normal", "Experiment");
	private static $arrYesNo = array("No", "Yes");
	
	private $currentUser;
	
	function __construct() {
	    global $user_profile_object;
		$this->currentUser = new User($user_profile_object->t_emno);		
	}
	
private function prepareSqlForController() {
    $permittedCustomers = implode("','", $this->currentUser->getPermitedCustomers());
    if(empty($permittedCustomers) == true) {
        throw new Exception("You have not permission to any customer");
    }
    
    $sql = "SELECT * FROM smt_for_repair.main 
        		WHERE cust_number in ('" .$permittedCustomers ."') 
        		AND reason IN ('" .implode("','", Item::$reasons) ."') 
        		AND done = 0 
        		AND deleted = 0
        		ORDER BY cust_name";
    return $sql;
}

private function prepareSqlForCommonUser() {
    $permittedCustomers = implode("','", $this->currentUser->getPermitedCustomers());
    if(empty($permittedCustomers) == true) {
        throw new Exception("You have not permission to any customer");
    }
        
    $sql = "SELECT * FROM smt_for_repair.main 
        		WHERE cust_number in ('" .$permittedCustomers ."')
        		AND done = 0 
        		AND deleted = 0
        		ORDER BY cust_name, id";
    return $sql;
}
	
	public function getData() {
		
		try {
		    
    		$dbMySQL = Application::getMySqlDbConnection();
    		$dbBAAN = Application::getDbBaanConnection();
    		
    
    		switch($this->currentUser->getProfession()) {
    			case 28:    //controller
    		        $sql = $this->prepareSqlForController();
    		        break;
    			default:
    		        $sql = $this->prepareSqlForCommonUser();
    		}				
			
    		$dbMySQL->query($sql);    				 
    		
    		$i = 0;
    		
    		while($row = $dbMySQL->fetch()) {
    		    $i++;
                $item = Item::getInstanceByData($dbMySQL, $dbBAAN, $row);
                if($item->isNeededBeSentToHistory()) {               
                    continue;
                }
                
    			$delete='';
    			
                if($this->currentUser->getEmployeeNumberTrimed() == $item->getRequesterEmployeeNumber()) 
    			{				
    				$delete='<img id="'.$item->id .'" 
    					src="http://'.$_SERVER ['HTTP_HOST'].'/flw/images/icon_trash.gif" onclick="doOnDelete(' .$i.')" onmouseover="this.style.cursor=\'hand\'"
    					alt="delete row" title="delete row" style="cursor:pointer;">';

    			}
    		
    			$stockShortage = $item->getStockExcludingWarehosesAndLocations() > 0 ? "No" : "Yes^javascript:showWindowStockOfAllWarehouses(&apos;" .$item->getProperty('component') ."&apos;)^_self";		
    			$showWhereUsedLink = "show^javascript:showWindowOfWhereUsed(&apos;" .$item->getProperty('component') ."&apos;)^_self";
    			$requested_date = new DateTime($item->getProperty('requested_date'));
    			
    			$linkToApprovalFile = '';
    			
    			if ($item->getProperty('reason') == 'lost') {
    			    $linkToApprovalFile = $item->getProperty('approval_file');
    			}
    			
    			$arrGridData[] = array (
    				$item->getProperty('id'),
    				$delete,
    				$item->getProperty('cust_name'),
    				$item->getProperty('cust_number'),
    				trim($item->getProperty('component')),
    				$item->getProperty('qty'),
    				$item->getProperty('WO'),
    				$item->getProperty('position'),
    				self::$arrNPI_Type[$item->getProperty('t_npif')],    				
    				$item->getProperty('assembly'),
    				$showWhereUsedLink,
    				$item->getProperty('reason'),
    				$linkToApprovalFile,
    				$item->getProperty('user_remarks'),
    				$item->getProperty('description'),
    				$item->getProperty('price'),
    				$item->getProperty('price') * $item->getProperty('qty'),
    				$stockShortage,
    				$requested_date->format('d-m-Y H:m'),
    				$item->getProperty('requester'),
    				$item->getProperty('wh_remarks'),
    				$item->getProperty('mrb_material_receiving_date')
    			);				
    		}
    		
    		$arrParams[]="";
    		$objGridHelper = new GridHelper($arrParams);
    		$objGridHelper->addColumn("id", "id", "ro", 0, "center", "#text_filter", "str",false,"");
    								
    		switch($this->currentUser->getProfession()) {
    			case 28:    //controller
    			    $objGridHelper->addColumn("Delete", "Delete", "ro",50, "center", "", "str",false,"");
            		$objGridHelper->addColumn("Customer name", "customer_name", "ro", 200, "left", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn("Customer number", "customer_number", "ro", 70, "center", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn('Component', "component", "ro", 150, "left", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('Qty', "qty", "ro", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('<span style="color:red">WO</span>', "wo", "ed", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('<span style="color:red">WO position</span>', "wo_position", "ed", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('NPI type', "npi_type", "ro", 80, "center", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn("Assembly", "assembly", "ro", 120, "left", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Where Used", "where_used", "link", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('Reason', "reason", "ro", 100, "center", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn('Approval file', "approval_file", "link", 120, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('Remarks', "remarks", "ro", 120, "left", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Description", "description", "ro", 220, "left", "#text_filter", "str",false,"");   
            		$objGridHelper->addColumn("Price", "price", "ro", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Total amount", "total_amount", "ro", 90, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Stock shortage", "stock_shortage", "link", 100, "center", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn("Requested date", "requested_date", "ro", 120, "left", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Requester", "requester", "ro", 120, "left", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn("WH remarks", "wh_remarks", "ro", 100, "left", "#text_filter", "str",false,"");
                    $objGridHelper->addColumn("MRB material receiving date", "mrb_material_receiving_date", "ro", 80, "center", "#select_filter", "str",false,"");        		
            		$arrResult['profession'] = 'controller';
    		        break;
    			default:
    			    $objGridHelper->addColumn("Delete", "Delete", "ro",50, "center", "", "str",false,"");
            		$objGridHelper->addColumn("Customer name", "customer_name", "ro", 200, "left", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn("Customer number", "customer_number", "ro", 70, "center", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn('Component', "component", "ro", 150, "left", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('<span style="color:red">Qty</span>', "qty", "ed", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('<span style="color:red">WO</span>', "wo", "ed", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('<span style="color:red">WO position</span>', "wo_position", "ed", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('NPI type', "npi_type", "ro", 80, "center", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn("Assembly", "assembly", "ro", 120, "left", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Where Used", "where_used", "link", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn('Reason', "reason", "ro", 100, "center", "#select_filter", "str",false,"");
                    $objGridHelper->addColumn('Approval file', "approval_file", "link", 120, "center", "#text_filter", "str",false,"");            		
            		$objGridHelper->addColumn('<span style="color:red">Remarks</span>', "remarks", "ed", 120, "left", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Description", "description", "ro", 220, "left", "#text_filter", "str",false,"");   
            		$objGridHelper->addColumn("Price", "price", "ro", 80, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Total amount", "total_amount", "ro", 90, "center", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Stock shortage", "stock_shortage", "link", 100, "center", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn("Requested date", "requested_date", "ro", 120, "left", "#text_filter", "str",false,"");
            		$objGridHelper->addColumn("Requester", "requester", "ro", 120, "left", "#select_filter", "str",false,"");
            		$objGridHelper->addColumn("WH remarks", "wh_remarks", "ro", 100, "left", "#text_filter", "str",false,"");
                    $objGridHelper->addColumn("MRB material receiving date", "mrb_material_receiving_date", "ro", 80, "center", "#select_filter", "str",false,"");        		
            		$arrResult['profession'] = '';
		    }
    		
    		$arrResult ["headers"] = $objGridHelper->getNames ();
    		$arrResult ["ids"] = $objGridHelper->getIds();
    		$arrResult ["types"] = $objGridHelper->getTypes ();
    		$arrResult ["widths"] = $objGridHelper->getWidths ();
    		$arrResult ["aligns"] = $objGridHelper->getAligns ();
    		$arrResult ["filters"] = $objGridHelper->getFilters ();
    		$arrResult ["sortings"] = $objGridHelper->getSortings ();
    		$arrResult ["colors"] = $objGridHelper->getColors ();
    		$arrResult ["data"] = $arrGridData;
    		$arrResult ["status"] = "success";
    		
//    		if(isset($_COOKIE['show_instruction']) == false) {
//    		    ob_start();
//    		    setcookie('show_instruction', 'seen', time() + (86400 * 30 * 30 * 12), "/");
//    		    ob_end_flush();
//    		    $arrResult ["show_instruction"] = '';
//    		} else {
//    		    $arrResult ["show_instruction"] = 'seen';
//    		}
    		return $arrResult;	
		}
		catch(PDOException $e) {
	        $arrResult["status"] = "fail";
	        $arrResult["error"] = $e->getMessage();
	        return $arrResult;		    
		}		
		catch(Exception $e)
		{
	        $arrResult["status"] = "fail";
	        $arrResult["error"] = $e->getMessage();
	        return $arrResult;        		    
		}		
	}
		
	public function saveChanges()
	{		
		$dbMySQL = Application::getMySqlDbConnection();
		$dbBAAN = Application::getDbBaanConnection();		

//		require_once 'infrastructure_problem.class.php';
//		$infrastructure_problem = new infrastructure_check($this->objLocalDb);
//		$arrInf_problem = $infrastructure_problem->GetData();
//		
//		require_once 'Item.class.php';
//		
//		$sql = "select * from smt_for_repair.planned top  ";		
//		
//		$result = $this->objLocalDb->execQuery($sql, __LINE__);
//		$row = $this->objLocalDb->fetchRow($result);
//		$item = new Item();		

    	$sXML = "<?xml version='1.0' encoding='iso-8859-1'?><data>";
    	
    	$arrIds = explode(",", $_POST["ids"]);
    	
    	foreach($arrIds as $key=>$value)
    	{
			switch($_POST[$value ."_!nativeeditor_status"])
			{
				case "updated":
				    try {	
                        $component = $_POST[$value ."_component"];
			            $wo = $_POST[$value ."_wo"];
			            $wo_position = $_POST[$value ."_wo_position"];
			            
				        $message = '';
				        
			        	if($_POST[$value ."_wo"] != '') {
					        if($this->isWoAndPositionAreValid($component, $wo, $wo_position, $message) == false) {
					            throw new Exception($message);    
					        }
					    }
					    
    					$sql = "UPDATE smt_for_repair.main SET Qty = " .$_POST[$value ."_qty"] .", 
        							WO = '" .$_POST[$value ."_wo"] ."', 
        							position = " .$_POST[$value .'_wo_position'] .", 
        							reason = '" .$_POST[$value ."_reason"] ."' 
    							WHERE id = " .$_POST[$value ."_id"] .";";
    							
    					$dbMySQL->query($sql);
    					$dbMySQL->fetch();

    					if($dbMySQL->rowCount() == 0) {
    				        throw new Exception('There is no new data to update. Refresh data in the grid');	    
    					}
    											
						$item = Item::getInstanceById($dbMySQL, $dbBAAN, $_POST[$value ."_id"]);
						$t_npif = self::$arrNPI_Type[$item->getProperty('t_npif')];
						$sXML .= "<action type='" .$_POST[$value ."_!nativeeditor_status"] ."' sid='" .$value ."' tid='" .$value ."'>" .$t_npif ."</action>";
				    }
				    catch (Exception $e) {				        
                        $errorMessage = $e->getMessage();
			        	$sXML .= "<action type='error' sid='" .$value ."' tid='" .$value ."'>" .$errorMessage ."</action>";						
				    }
					break;
					
				case "inserted":
				    try {				 
				            $component = $_POST[$value ."_component"];
				            $wo = $_POST[$value ."_wo"];
				            $wo_position = $_POST[$value ."_wo_position"];
				                   
        					if($component == "") {
        					    throw new Exception('Work Order is ommited');
        					}
    
        					$itemDetails = $this->getItemDetails($component);
    					    if($itemDetails["error"]["status"] != "success") {
    					        throw new Exception($itemDetails["error"]["error_desc"]); 
    					    }				    					    
    					    
    					    $message = '';
    					    
    					    if($_POST[$value ."_wo"] != "") {
    					        if($this->isWoAndPositionAreValid($component, $wo, $wo_position, $message) == false) {
    					            throw new Exception($message);    
    					        }
    					    }
        						    
    						$sql = "INSERT INTO smt_for_repair.main( 
    								   cust_number,
                                       cust_name,
                                       component,
                                       description,
                                       qty,
                                       assembly,
                                       requested_date,
                                       requester,
                                       reason,
                                       approval_file,
                                       mrb_material_receiving_date,
                                       price,
                                       user_remarks,
                                       wh_remarks) 
    								   VALUES ( 
    							'" .str_pad($_POST[$value ."_customer_number"], 6, ' ', STR_PAD_LEFT) ."'"
    							.",'" .$_POST[$value ."_customer_name"] ."'" 
    							.",'" .$_POST[$value ."_component"] ."'" 
    							.",'" .$_POST[$value ."_description"] ."'" 
    							."," .$_POST[$value ."_qty"]  
    							.",'" .$_POST[$value ."_assembly"] ."'" 
    							.",now()"
    							.",'" .$_POST[$value ."_requester"] ."'"
    							.",'" .$_POST[$value ."_reason"] ."'"
    							.",'" .$_POST[$value ."_approval_file"] ."'" 
    							.",'" .$_POST[$value ."_mrb_material_receiving_date"] ."'"
    							."," .$_POST[$value ."_price"] 
    							.",'" .$_POST[$value ."_user_remarks"] ."'" 
    							.",'" .$_POST[$value ."_wh_remarks"] ."')";
    							
    						$dbMySQL->query($sql);
    						$lastInsertId = $dbMySQL->lastInsertId();
        						
    						if($lastInsertId == 0) {
    						    throw new Exception('Could not save data');
    						}
    						Item::getInstanceById($dbMySQL, $dbBAAN, $lastInsertId);
    						$sXML .= "<action type='inserted' sid='" .$value ."' tid='" .$lastInsertId ."'>" .$_POST[$value ."_component"] ."</action>";
				       } 
				       catch (Exception $e) {
				           $errorMessage = $e->getMessage();
                           $sXML .= "<action type='error' sid='" .$_POST[$value ."_id"] ."' tid='" .$_POST[$value ."_id"] ."'>" .$errorMessage ."</action>";				    
				       }    																
					break;
											
				case "deleted":
        					$sql = "UPDATE smt_for_repair.main SET 
        						deleted 					= 1,
        						deleted_by 				    = '" .$this->currentUser->getEmployeeNumberTrimed() ." - " .$this->currentUser->getFullName() ."',
        						deleted_date			    = now()    
        						WHERE id = " .$_POST[$value ."_id"];
        							
        					$dbMySQL->query($sql);
        					$dbMySQL->fetch();
					
			        if($dbMySQL->rowCount() > 0)
						$sXML .= "<action type='deleted' sid='" .$value ."' tid='" .$value ."'>deleted</action>";
					else
						$sXML .= "<action type='error' sid='" .$value ."' tid='" .$value ."'>Could not delete row</action>";					
					break;						
			}
    	}
		$arrResult = $sXML .= "</data>";
		return $arrResult;  		
	}

	public function getItemDetails($pn){	
			
		try {
    		$dbMySQL = Application::getMySqlDbConnection();
    		$dbBAAN = Application::getDbBaanConnection();            
		    
		    $sql = "SELECT t.t_item, t.t_copr, t1.t_dsca, t1.t_cuno, t2.t_nama, t.t_ctyp FROM ttiitm001400 t
                    INNER JOIN ttiitm200400 t1
                    ON t.t_item = t1.t_item
                    INNER JOIN ttccom010400 t2
                    ON t1.t_cuno = t2.t_cuno
                    WHERE t1.t_item = :item";
            
		    $pn = str_pad($pn, 16, ' ', STR_PAD_LEFT); 
		    $pn = strtoupper($pn);
		    $dbBAAN->query($sql, array(':item' => $pn));
		    $row = $dbBAAN->fetch();
		    			
			if($row === false) {
				throw new Exception("Item was not found in BAAN");
			}

			if($row['t_ctyp'] != 'SMT') {
			    throw new Exception("The item does not appear to be SMT");
			}
            				
			$customerNumber = $row["t_cuno"];
			if(!$this->currentUser->isPermitedToCustomer($customerNumber)) {
			    throw new Exception("You do not have permissions for this customer");
		    }						
			
			$arrResult["error"]["status"] = "success";
			
			$timeNow = new DateTime();
			$item = Item::getInstanceByComponent($dbMySQL, $dbBAAN, $pn);
			if($item->getStockExcludingWarehosesAndLocations() > 0) {    
			    $stockShortage = "No";
			    $arrResult["data"]["message"] = '';			    
			} else {
			    $stockShortage = "Yes^javascript:showWindowStockOfAllWarehouses(&apos;" .$item->getProperty('component') ."&apos;)^_self";
			    $arrResult["data"]["message"] = 'Pay Attention: Shortage exists for this item';
			}
			$arrResult["data"]["stock_shortage"] = $stockShortage;
//			$arrResult["data"]["npi_type"] = self::$arrNPI_Type[$item->getProperty('t_npif')];
			
			$arrResult["data"]["t_cuno"] = $row["t_cuno"];
			$arrResult["data"]["t_nama"] = $row["t_nama"];
			$arrResult["data"]["description"] = $row["t_dsca"];
			$arrResult["data"]["t_item"] = trim($row["t_item"]);
            $arrResult["data"]["price"] = $row["t_copr"];
            $arrResult["data"]["t_npif"] = $row["t_npif"];
            $arrResult["data"]["requested_date"] = $timeNow->format('Y-m-d H:i');
			$arrResult["data"]["requester"] = $this->currentUser->getEmployeeNumber() .' - ' .$this->currentUser->getFullName();
						
		}
		catch(Exception $e) {
			$arrResult["error"]["status"] = "fail";
			$arrResult["error"]["error_desc"] = $e->getMessage();
		}		
		return $arrResult;					
	}
	
//    public function getNpiTypeByWo() {
//		try {
//    		$dbBAAN = Application::getDbBaanConnection();            
//		    
//    		$wo = $_GET['wo'];
//            $sql = "SELECT t1.t_npif FROM ttisfc001400 t1 WHERE t_pdno = " .$wo;
//        	$dbBAAN->query($sql);
//        	$row = $dbBAAN->fetch();
//        	$arrResult['t_npif'] = self::$arrNPI_Type[$row['t_npif']];
//        	
//		} catch (Exception $e) {
//			$arrResult["error"]["status"] = "fail";
//			$arrResult["error"]["error_desc"] = $e->getMessage();		    
//		}
//		return $arrResult;        
//    }	
	
	public function isWoAndPositionAreValid($component, $wo, $wo_position, & $message) {
	    $dbBAAN = Application::getDbBaanConnection();
	    
	    $sql = "SELECT t_pono, t_sitm FROM tticst001400 WHERE t_pdno = :wo";
	    $dbBAAN->query($sql, array(':wo' => $wo));
	    
	    $rows = $dbBAAN->fetchAll();	    
	    
	    if ($rows === false || count($rows) == 0) {
	        $message = 'WO ' .$wo .' was not found in BAAN';
	        return false;
	    }
	    
	    $found = false;
	    
	    foreach ($rows as $row) {
	        if ($row['t_pono'] == $wo_position) {
	            $found = true;
	        }
	    }
	    
	    if ($found == false) {
    	    $message = 'WO position ' .$wo_position .' was not found in BAAN';
    	    return false;	        
	    }
	    
		$found = false;
		
	    foreach ($rows as $row) {
	        if (trim($row['t_sitm']) == trim($component)) {
	            $found = true;
	        }
	    }

		if ($found == false) {
    	    $message = 'WO ' .$wo .' does not contain this component ' .$component;
    	    return false;	        
	    }	    
	    

		foreach ($rows as $row) {
	        if (trim($row['t_sitm']) == trim($component) && $row['t_pono'] == $wo_position) {
	            return true;
	        }
	    }	    
	    $message = 'Component ' .$component .' does not match the position ' .$wo_position;
	    return false;		    
	}
	
	public function getStockOfAllWarehouses($component) {
	    $dbMySQL = Application::getMySqlDbConnection();
		$dbBAAN = Application::getDbBaanConnection();
		
	    $item = Item::getInstanceByComponent($dbMySQL, $dbBAAN, $component);
	    $lotsHolder = $item->getLotsHolder();
	    foreach($lotsHolder as $lot) {
	        $arrGridData[] = array ($lot->t_date, $lot->t_loca, $lot->t_cwar, $lot->t_clot, $lot->t_strs, $lot->manufacturer);
	    }
	    	     
		$arrParams[]="";
		$objGridHelper = new GridHelper ($arrParams );
		$objGridHelper->addColumn ( "Date", "date", "ro", 80, "center", "#text_filter", "str",false,"");	
		$objGridHelper->addColumn ( "Location", "location", "ro", 80, "center", "#text_filter", "str",false,"");
		$objGridHelper->addColumn ( "Warehouse", "warehouse", "ro", 90, "center", "#text_filter", "str",false,"");
		$objGridHelper->addColumn ( "Lot", "lot", "ro", 80, "center", "#text_filter", "str",false,"");
		$objGridHelper->addColumn ( "Stock", "stock", "ro", 50, "center", "#text_filter", "str",false,"");
		$objGridHelper->addColumn ( "Manufacturer", "manufacturer", "ro", 120, "center", "#text_filter", "str",false,"");

		$arrResult ["headers"] = $objGridHelper->getNames ();
		$arrResult ["ids"] = $objGridHelper->getIds();
		$arrResult ["types"] = $objGridHelper->getTypes ();
		$arrResult ["widths"] = $objGridHelper->getWidths ();
		$arrResult ["aligns"] = $objGridHelper->getAligns ();
		$arrResult ["filters"] = $objGridHelper->getFilters ();
		$arrResult ["sortings"] = $objGridHelper->getSortings ();
		$arrResult ["colors"] = $objGridHelper->getColors ();
		$arrResult ["data"] = $arrGridData;

		return $arrResult;		
	}
	
	public function getWhereUsed($component) {
	    $dbMySQL = Application::getMySqlDbConnection();
		$dbBAAN = Application::getDbBaanConnection();
		
	    $item = Item::getInstanceByComponent($dbMySQL, $dbBAAN, $component);
	    $whereUsed = $item->getWhereUsed();
	    foreach($whereUsed as $item) {
	        $arrGridData[] = array ($item['t_mitm'], $item['t_dsca'], $item['t_qana'], $item['t_opno']);
	    }
	    	     
		$arrParams[]="";
		$objGridHelper = new GridHelper ($arrParams );
		$objGridHelper->addColumn ( "t_mitm", "t_mitm", "ro", 150, "left", "#text_filter", "str",false,"");	
		$objGridHelper->addColumn ( "t_dsca", "t_dsca", "ro", 218, "left", "#text_filter", "str",false,"");
		$objGridHelper->addColumn ( "t_qana", "t_qana", "ro", 65, "center", "#text_filter", "str",false,"");
		$objGridHelper->addColumn ( "t_opno", "t_opno", "ro", 65, "center", "#text_filter", "str",false,"");

		$arrResult ["headers"] = $objGridHelper->getNames ();
		$arrResult ["ids"] = $objGridHelper->getIds();
		$arrResult ["types"] = $objGridHelper->getTypes ();
		$arrResult ["widths"] = $objGridHelper->getWidths ();
		$arrResult ["aligns"] = $objGridHelper->getAligns ();
		$arrResult ["filters"] = $objGridHelper->getFilters ();
		$arrResult ["sortings"] = $objGridHelper->getSortings ();
		$arrResult ["colors"] = $objGridHelper->getColors ();
		$arrResult ["data"] = $arrGridData;

		return $arrResult;		
	}	
    	
	public function getExcel() {
		
		try {
		    
    		$dbMySQL = Application::getMySqlDbConnection();
    		$dbBAAN = Application::getDbBaanConnection();
    		
    
    		switch($this->currentUser->getProfession()) {
    			case 28:    //controller
    		        $sql = $this->prepareSqlForController();
    		        break;
    			default:
    		        $sql = $this->prepareSqlForCommonUser();
    		}
    		
    		$dbMySQL->query($sql);
    		
    		while($row = $dbMySQL->fetch()) {
                $item = Item::getInstanceByData($dbMySQL, $dbBAAN, $row);
    
    //            if($item->isNeededBeSentToHistory()) {               
    //                continue;
    //            }            
    
    		$arrData[] = array (
        				$item->getProperty('cust_number'),
        				$item->getProperty('cust_name'),
        				$item->getProperty('component'),
        				str_replace('>',' ',$item->getProperty('desc')),
        				$item->getProperty('qty'),
        				$item->getProperty('WO'),
        				$item->getProperty('position'),
        				$item->getProperty('assembly'),
        				$item->getProperty('requested_date'),
        				$item->getProperty('requester'),
        				$item->getProperty('reason'),
        				$item->getProperty('mrb_material_receiving_date'),
        				$item->getProperty('price'),
        				iconv ("windows-1255", "UTF-8" ,$item->getProperty('user_remarks')),
        				iconv ("windows-1255", "UTF-8" ,$item->getProperty('wh_remarks'))
        			);				
        		}
        		
        		$header = "Customer number,Customer name,Component,Description,Qty,WO,position,assembly,Requested date,Requester,Reason,MRB material receiving date,Price,User remarks,WH remarks";
    			
        		$arrResult ["data"] = $arrData;
        		$arrResult["status"] = "success";	
    		} catch(Exception $e) {
    	        $arrResult["status"] = "fail";
    	        $arrResult["error"] = $e->getMessage();
    	        return $arrResult;        		    
    		}		
            $fileName = uniqid("smt_for_repair_") ."." .Excel::refineExt("xls");
            $objExcel = new Excel($this->pathToTemp . $fileName);
            
            array_unshift($arrData, explode(",", $header));
    		$objExcel->addData($arrData, false, false, true, array('text'), false);	
    		
    		$objExcel->setAutoFit();
    		$objExcel->setAutoFilter("1:1");	    
    		$objExcel->export();
    		$objExcel->closeExcel();
    		
    		$arrResult ["data"] = '/flw/public/utilities/php_code/file_export.php?file=/flw/tmp/' .$fileName;
    		return $arrResult;		
	}		
}
?>