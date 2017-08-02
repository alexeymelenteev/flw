<?
/**
 * @todo change DIR_NAME to name of your project directory
 */
define("DIR_NAME", "smt_for_repair");
define("PROJECT_PATH",$_SERVER['DOCUMENT_ROOT'].'/flw/' . DIR_NAME . '/');
define("PROJECT_PATH_REL",'/flw/' . DIR_NAME . '/');
define("TEMP_FILE_PATH",$_SERVER['DOCUMENT_ROOT']."/flw/tmp/");
define("EXCEL_FILE_PATH",TEMP_FILE_PATH);
define("AJAX_PATH", PROJECT_PATH."ajax/");
define("HANDLERS_PATH", PROJECT_PATH."handlers/");
define("LIB_PATH", PROJECT_PATH."lib/");
define("TEMPLATES_PATH", PROJECT_PATH."templates/");
require_once ($_SERVER['DOCUMENT_ROOT']."/flw/public/utilities/class/pageHelper.class.php");
$objPageHelper = new PageHelper();

$objPageHelper->addPage("items_request","main.tpl.php","items_request.js?seed=".time(),"styles.css");
$objPageHelper->addPage("warehouse","main.tpl.php","warehouse.js","styles.css");
$objPageHelper->addPage("history","main.tpl.php","history.js?seed=".time(),"styles.css");

if (isset($_GET["page"])) {
	$objPageHelper->setPage($_GET["page"]);
}
?>