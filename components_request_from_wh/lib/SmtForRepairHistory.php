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

class SmtForRepairHistory
{
	private static $arrNPI_Type = array("", "NPI", "Normal", "Experiment");
	private static $arrYesNo = array("No", "Yes");
	
	public function getData() {
		
		try {
		    
    		$dbMySQL = Application::getMySqlDbConnection();
    		$dbBAAN = Application::getDbBaanConnection();
    		             
            $sql = "SELECT * FROM smt_for_repair.main WHERE done = 1 OR deleted = 1 ORDER BY cust_number";	
				   					
    		$dbMySQL->query($sql);    				 
    		$rows = $dbMySQL->fetchAll();
    		
    		foreach ($rows as $row) {
                $item = Item::getInstanceById($dbMySQL, $dbBAAN, $row['id']);
//                if($item->isNeededBeSentToHistory()) {               
//                    continue;
//                }
    				
    			$showWhereUsedLink = "show^javascript:showWindowOfWhereUsed(&apos;" .$item->getProperty('component') ."&apos;)^_self";
    			$requested_date = new DateTime($item->getProperty('requested_date'));
                $mrbMaterial = $item->getProperty('mrb_material_receiving_date') != '' ? 1 : 0;
    			
    			$arrGridData[] = array (
    				$item->getProperty('id'),
    				$requested_date->format('d-m-Y'),
    				$item->getProperty('requester'),
    				$item->getProperty('user_remarks'),
    				$item->getProperty('wh_remarks'),
    				trim($item->getProperty('component')),
    				self::$arrYesNo[$item->getProperty('done')],
    				self::$arrYesNo[$item->getProperty('deleted')],
    				$item->getProperty('deleted_by'),
    				$item->getProperty('deleted_date'),
    				$item->getProperty('qty'),
    				$item->getProperty('reason'),
    				$item->getProperty('WO'),
    				$item->getProperty('position'),
    				self::$arrNPI_Type[$item->getProperty('t_npif')],
    				self::$arrYesNo[$mrbMaterial],
    				$item->getProperty('assy'),
    				$item->getProperty('cust_name'),
    				$item->getProperty('cust_number'),
    				$showWhereUsedLink,
    				$item->getProperty('description'),
    				$item->getProperty('price'),
    				$item->getProperty('price') * $item->getProperty('qty'),
    				$item->getProperty('days_waiting')
    			);				
    		}
    		
    		$arrParams[]="";
    		$objGridHelper = new GridHelper($arrParams);
    		$objGridHelper->addColumn("id", "id", "ro", 0, "center", "#text_filter", "str",false,"");    		
    		$objGridHelper->addColumn("Requested date", "requested_date", "ro", 100, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn("Requester", "requester", "ro", 120, "left", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("Remarks", "remarks", "ro", 120, "left", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('WH remarks', "wh_remarks", "ed", 120, "left", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('Component', "component", "ro", 150, "left", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('Done', "done", "ro", 60, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn('Deleted', "deleted", "ro", 60, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn('Deleted by', "deleted_by", "ro", 130, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn('Deleted date', "deleted_date", "ro", 120, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('Qty', "qty", "ro", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('Reason', "reason", "ro", 100, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn('WO', "wo", "ro", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('WO position', "wo_position", "ro", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn('NPI type', "npi_type", "ro", 80, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("MRB material received", "mrb_material_received", "ro", 80, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("Assy", "assy", "ro", 120, "left", "#text_filter", "str",false,"");    		
    		$objGridHelper->addColumn("Customer name", "customer_name", "ro", 200, "left", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("Customer number", "customer_number", "ro", 70, "center", "#select_filter", "str",false,"");
    		$objGridHelper->addColumn("Where Used", "where_used", "link", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn("Description", "description", "ro", 220, "left", "#text_filter", "str",false,"");   
    		$objGridHelper->addColumn("Price", "price", "ro", 80, "center", "#text_filter", "str",false,"");
    		$objGridHelper->addColumn("Total amount", "total_amount", "ro", 90, "center", "#text_filter", "str",false,"");
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
}

?>