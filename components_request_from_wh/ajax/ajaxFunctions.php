<?php
require_once ("../init.php");
set_time_limit(0);
require_once (FLW_DOC_ROOT . '/php_code/session_header.php');
error_reporting(E_ALL ^ E_NOTICE);
$arrParams[] = "";
$strAction = $_GET['action'];
if(function_exists($strAction))
{
    try
    {
        $strAction();
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
        header("Content-Type:application/json");
        print json_encode($arrResult);
    }
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////							Items Request 
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function getDataItemsRequest()
{
    require_once (LIB_PATH . "SmtForRepairItemsRequest.php");
    $itemsRequest = new SmtForRepairItemsRequest();
    try
    {
        $arrResult = $itemsRequest->getData();
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: application/json");
    print json_encode($arrResult);
}

function saveChangesOfItemsRequest()
{
    require_once (LIB_PATH . "SmtForRepairItemsRequest.php");
    $itemsRequest = new SmtForRepairItemsRequest();
    try
    {
        $arrResult = $itemsRequest->saveChanges();
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: text/xml");
    print $arrResult;
}

function getExcelItemsRequest() {
    require_once (LIB_PATH . "SmtForRepairItemsRequest.php");
    $itemsRequest = new SmtForRepairItemsRequest();
    try {
        $arrResult = $itemsRequest->getExcel();
    } catch(Exception $e) {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: application/json");
    print json_encode($arrResult);    
}

function getItemDetails()
{
    require_once (LIB_PATH . "SmtForRepairItemsRequest.php");
    $itemsRequest = new SmtForRepairItemsRequest();
    $pn = $_GET['item'];
    $arrResult = $itemsRequest->getItemDetails($pn);
    
    header("Content-Type: application/json");
    print json_encode($arrResult);    
}

//function getNpiTypeByWo()
//{
//    require_once (LIB_PATH . "SmtForRepairItemsRequest.php");
//    $itemsRequest = new SmtForRepairItemsRequest();
//    $wo = $_GET['wo'];
//    $arrResult = $itemsRequest->getNpiTypeByWo($wo);
//    
//    header("Content-Type: application/json");
//    print json_encode($arrResult);    
//}

function getStockOfAllWarehouses()
{
    require_once (LIB_PATH . "SmtForRepairItemsRequest.php");
    $itemsRequest = new SmtForRepairItemsRequest();
    try
    {
        $arrResult = $itemsRequest->getStockOfAllWarehouses($_GET["component"]);
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: application/json");
    print json_encode($arrResult);    
}

function getWhereUsed()
{
    require_once (LIB_PATH . "SmtForRepairItemsRequest.php");
    $itemsRequest = new SmtForRepairItemsRequest();
    try
    {
        $arrResult = $itemsRequest->getWhereUsed($_GET["component"]);
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: application/json");
    print json_encode($arrResult);    
}

function uploadFile()
{
	require_once (LIB_PATH . "Attachments.php");
	$attachments = new Attachments();
	$arrResult["data"] = $attachments->uploadFiles('smt_for_repair');			
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////							Warehouse activity
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function getDataWarehouseActivity()
{
    require_once (LIB_PATH . "SmtForRepairWarehouseActivity.php");
    $warehouseActivity = new SmtForRepairWarehouseActivity();
    try
    {
        $arrResult = $warehouseActivity->getData();
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: application/json");
    print json_encode($arrResult);    
}

function saveChangesOfWarehouseActivity()
{
    require_once (LIB_PATH . "SmtForRepairWarehouseActivity.php");
    $warehouseActivity = new SmtForRepairWarehouseActivity();
    try
    {
        $arrResult = $warehouseActivity->saveChanges();
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: text/xml");
    print $arrResult;
}

function getDataForPrintWarehouseActivity()
{
    require_once (LIB_PATH . "SmtForRepairWarehouseActivity.php");
    $warehouseActivity = new SmtForRepairWarehouseActivity();
    try
    {
        $arrResult['data'] = $warehouseActivity->getDataForPrint();
        $arrResult['error'] = '';
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: application/json");
    print json_encode($arrResult);
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////							History
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function getDataHistory()
{
    require_once (LIB_PATH . "SmtForRepairHistory.php");
    $history = new SmtForRepairHistory();
    try
    {
        $arrResult = $history->getData();
    } catch(Exception $e)
    {
        $arrResult["error"] = $e->getMessage();
    }
    header("Content-Type: application/json");
    print json_encode($arrResult);    
}
?>

