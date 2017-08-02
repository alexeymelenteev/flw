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

class PermissionDenyException extends Exception {}

class SmtForRepairWarehouseActivity 
{
	private static $arrNPI_Type = array("", "NPI", "Normal", "Experiment");
	private static $arrYesNo = array("No", "Yes");

	private $currentUser;
	
	function __construct() {
		global $user_profile_object;
		$this->currentUser = new User($user_profile_object->t_emno);		
	}
		
	public function getData() {
		
		try {
		    
    		$dbMySQL = Application::getMySqlDbConnection();
    		$dbBAAN = Application::getDbBaanConnection();
    		             
            $sql = "SELECT *, datediff(now(), requested_date) days_waiting FROM smt_for_repair.main 
                		WHERE done = 0 
                		AND deleted = 0
                		ORDER BY id";	
				   					
    		$dbMySQL->query($sql);    				 
    		
    		while ($row = $dbMySQL->fetch()) {
                $item = Item::getInstanceByData($dbMySQL, $dbBAAN, $row);
                if($item->isNeededBeSentToHistory()) {               
                    continue;
                }

                if ($item->getProperty('reason') == 'ECO' || $item->getProperty('reason') == 'RMA/REWORK') {
                    if ($item->getProperty('WO') == '') {
                        continue;
                    }
                }
    		
//    			$stock = "show^javascript:showWindowStockOfAllWarehouses(&apos;" .$item->getProperty('component') ."&apos;)^_self";
    			$stock = $item->getStockExcludingWarehosesAndLocations() <= 0 ? "No stock" : "show^javascript:showWindowStockOfAllWarehouses(&apos;" .$item->getProperty('component') ."&apos;)^_self";		
    			$showWhereUsedLink = "show^javascript:showWindowOfWhereUsed(&apos;" .$item->getProperty('component') ."&apos;)^_self";
    			$requested_date = new DateTime($item->getProperty('requested_date'));
    			$mrbMaterial = $item->getProperty('mrb_material_receiving_date') != '' ? 1 : 0;

    			if ($item->getProperty('reason') == 'lost') {
    			    $linkToApprovalFile = $item->getProperty('approval_file');
    			} 
    			
    			$arrGridData[] = array (
    				$item->getProperty('id'),
    				$requested_date->format('d-m-Y'),
    				$item->getProperty('requester'),
    				$item->getProperty('user_remarks'),
    				$item->getProperty('wh_remarks'),
    				trim($item->getProperty('component')),
    				0,
    				$item->getProperty('qty'),
    				$item->getProperty('price'),
    				$item->getProperty('price') * $item->getProperty('qty'),
    				$item->getProperty('reason'),
    				$linkToApprovalFile,
    				$item->getProperty('WO'),
    				$item->getProperty('position'),
    				self::$arrNPI_Type[$item->getProperty('t_npif')],
    				$stock,
    				$mrbMaterial,
    				$item->getProperty('cust_name'),
    				$item->getProperty('cust_number'),
    				$showWhereUsedLink,
    				$item->getProperty('description'),
    				$item->getProperty('days_waiting')
    			);				
    		}
    		
    		$arrParams[]="";
    		$objGridHelper = new GridHelper($arrParams);
    		$objGridHelper->addColumn("id", "id", "ro", 0, "center", "#text_filter", "str",false,"");    		
    		$objGridHelper->addColumn("Requested date", "requested_date", "ro", 100, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn("Requester", "requester", "ro", 120, "left", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("Remarks", "remarks", "ro", 120, "left", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('<span style="color:red">WH remarks</span>', "wh_remarks", "ed", 120, "left", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('Component', "component", "ro", 150, "left", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('<span style="color:red">Done</span>', "done", "acheck", 60, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn('Qty', "qty", "ro", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn("Price", "price", "ro", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn("Total amount", "total_amount", "ro", 90, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('Reason', "reason", "ro", 100, "center", "#select_filter", "str",false,"");
            $objGridHelper->addColumn('Approval file', "approval_file", "link", 120, "center", "#text_filter", "str",false,"");    		
    		$objGridHelper->addColumn('WO', "wo", "ro", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('WO position', "wo_position", "ro", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('NPI type', "npi_type", "ro", 80, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("Stock", "stock", "link", 100, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn('<span style="color:red">MRB material received</span>', "mrb_material_received", "acheck", 80, "center", "#select_filter", "str",false,"");    		
    		$objGridHelper->addColumn("Customer name", "customer_name", "ro", 200, "left", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("Customer number", "customer_number", "ro", 70, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("Where Used", "where_used", "link", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn("Description", "description", "ro", 220, "left", "#text_filter", "str",false,"");   
    		$objGridHelper->addColumn("Days waiting", "days_waiting", "ro", 10, "center", "#text_filter", "str",false,"");
    		$arrResult['profession'] = '';		    
    		
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

    	$sXML = "<?xml version='1.0' encoding='iso-8859-1'?><data>";
    	
    	$arrIds = explode(",", $_POST["ids"]);
    	
    	foreach($arrIds as $key=>$value)
    	{
			switch($_POST[$value ."_!nativeeditor_status"])
			{
				case "updated":
				    try {	
				            if ($this->currentUser->isPermitedToProgram(SMT_FOR_REPEAR_SMT_STOCK) == false) {
				                throw new PermissionDenyException;
				            }
				            
				            $materialReceivedDate = 'null';
				            			        
				            if ($_POST[$value ."_mrb_material_received"] == 1) {
				                $materialReceivedDate = 'now()';
				                $mrb_material_receiving_by = $this->currentUser->getEmployeeNumberTrimed() ." - " .$this->currentUser->getFullName();
				            }
				            
				            $doneDate    = 'null';
				            $doneBy      = '';
				            
				    		if ($_POST[$value ."_done"] == 1) {
				                $doneDate = 'now()';
				                $doneBy = $this->currentUser->getEmployeeNumberTrimed() ." - " .$this->currentUser->getFullName();
				            }				            
				            
        					$sql = "UPDATE smt_for_repair.main SET
        						wh_remarks = '" .$_POST[$value   ."_wh_remarks"] ."', 
        						done 						= "  .$_POST[$value ."_done"] .",
        						done_by 				    = '" .$doneBy ."',
        						done_date 				    = "  .$doneDate .",      						 
        						mrb_material_receiving_date = "  .$materialReceivedDate .",
        						mrb_material_receiving_by   = '" .$mrb_material_receiving_by ."'    
        						WHERE id = " .$_POST[$value ."_id"];
        							
        					$dbMySQL->query($sql);
        					$dbMySQL->fetch();
    					
    			        if($dbMySQL->rowCount() > 0) {
    						$sXML .= "<action type='" .$_POST[$value ."_!nativeeditor_status"] ."' sid='" .$value ."' tid='" .$value ."'></action>";
    						$item = Item::getInstanceById($dbMySQL, $dbBAAN, $_POST[$value ."_id"]);
    						$item->sendMailOnDone();
    			        }
				    } 
				    catch (PermissionDenyException $e) {
				        $sXML .= "<action type='error' sid='" .$value ."' tid='" .$value ."'>You do not have permission on update this data</action>";
				    }
				    catch (Exception $e) {
				        $sXML .= "<action type='error' sid='" .$value ."' tid='" .$value ."'>update failed</action>";
				    }										
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
            $arrResult["data"]["requested_date"] = $timeNow->format('Y-m-d H:i:s');
			$arrResult["data"]["requester"] = $this->currentUser->getEmployeeNumber() .' - ' .$this->currentUser->getFullName();
						
		}
		catch(Exception $e) {
			$arrResult["error"]["status"] = "fail";
			$arrResult["error"]["error_desc"] = $e->getMessage();
		}		
		return $arrResult;					
	}
	
	public function getDataForPrint() {
	    $dbMySQL = Application::getMySqlDbConnection();
		$dbBAAN = Application::getDbBaanConnection();
			    
	    $selectedId = explode(',', $_GET['selectedId']);
	    
	    $itemsHolder = array();
	    
	    foreach ($selectedId as $id) {
	        $itemsHolder[] = Item::getInstanceById($dbMySQL, $dbBAAN, $id);    
	    }
	    
	    $html = '';
	    
	    foreach ($itemsHolder as $item) {
	        $html .= '<fieldset>';
	        $html .= '<legend>Component: <b>' .$item->getProperty('component') .'</b></legend>';
    	    $html .= '<table>
                		<thead>
                			<tr>
                				<th>Qty.</th>
                				<th>Price</th>
                				<th>Total amount</th>
                				<th>Reason</th>
                				<th>NPI type</th>
                			</tr>
                		</thead>	        
	                    <tbody>
	        				<tr>';
	        $html .= '<td>' .$item->getProperty('qty') .'</td>';
	        $html .= '<td>' .$item->getProperty('price') .'</td>';
	        $html .= '<td>' .$item->getProperty('price') * $item->getProperty('qty') .'</td>';
	        $html .= '<td>' .$item->getProperty('reason') .'</td>';
	        $html .= '<td>' .self::$arrNPI_Type[$item->getProperty('t_npif')] .'</td>';
//	        $html .= '<td>' .$item->getProperty('description') .'</td>';
//	        $requested_date = new DateTime($item->getProperty('requested_date'));
//	        $html .= '<td>' .$requested_date->format('d-m-Y') .'</td>';
//	        $html .= '<td>' .$item->getRequesterFullName() .'</td>';
//	        $html .= '<td>' .$item->getProperty('user_remarks') .'</td>';
	        $html .= '    </tr>
	        			</tbody>
    	    		</table>';
	        
	        $html .= '<br />';
	        
    	    $html .= '<table>
                		<thead>
                			<tr>
                				<th>Date</th>
                				<th>Location</th>
                				<th>Warehouse</th>
                				<th>Lot</th>
                				<th>Stock</th>
                				<th>Manufacturer</th>
                			</tr>
                		</thead>	        
	                    <tbody>
	        				<tr>';	        
	        
            $lotsHolder = $item->getLotsForPrint();
            	        
	        foreach ($lotsHolder as $lot) {
	            $html .= '<tr>';
	            $html .= '<td>' .$lot->t_date .'</td>';
	            $html .= '<td>' .$lot->t_loca .'</td>';
	            $html .= '<td>' .$lot->t_cwar .'</td>';
	            $html .= '<td>' .$lot->t_clot .'</td>';
	            $html .= '<td>' .$lot->t_strs .'</td>';
	            $html .= '<td>' .$lot->manufacturer .'</td>';
	            $html .= '<tr>';
	        }
	        $html .= '</tbody>';
	        $html .= '</table>';
    	    $html .= '</fieldset>';
    	    $html .= '<br />';
	    }
	    return $html;
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
        				$item->getProperty('prod_line_meneger'),
        				$item->getProperty('reason'),
        				$item->getProperty('mrb_material_receiving_date'),
        				$item->getProperty('price'),
        				iconv ("windows-1255", "UTF-8" ,$item->getProperty('user_remarks')),
        				iconv ("windows-1255", "UTF-8" ,$item->getProperty('wh_remarks'))
        			);				
        		}
        		
        		$header = "Customer number,Customer name,Component,Description,Qty,WO,position,Assembly,Requested date,Requester,user remarks,WH remarks";
    			
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