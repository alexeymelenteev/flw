<?php
require("../init.php");
require_once (FLW_DOC_ROOT.'/php_code/session_header.php');
require_once (FLW_DOC_ROOT."/public/utilities/class/frontend.class.php");
require_once (FLW_DOC_ROOT."/public/utilities/class/db.class.php");
require_once (FLW_DOC_ROOT . '/public/utilities/php_code/utilities_function.php');
//require_once(LIB_PATH . ".class.php");
?>
<script type="text/javascript">
	_css_prefix="/flw/public/utilities/javaScript/dhtmlx/dhtmlxGrid/codebase/";
	_js_prefix="/flw/public/utilities/javaScript/dhtmlx/dhtmlxGrid/codebase/";
	/*_css_prefix="../../codebase/";
	_js_prefix="../../codebase/";*/
</script>
<?php
$objFrontEnd = new Frontend();
$objFrontEnd->_use_ajax = false;

//	DHTMLX includes
?>
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlx_std_full/codebase/dhtmlx.css' />	
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_web.css' />
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxToolbar/codebase/skins/dhtmlxtoolbar_dhx_skyblue.css' />
	<link rel="stylesheet" type="text/css" href="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/dhtmlxgrid.css" />
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_web.css' />
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css' />
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn_bricks.css' />	
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxLayout/codebase/skins/dhtmlxlayout_dhx_skyblue.css' />
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxWindows/codebase/dhtmlxwindows.css' />	
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxWindows/codebase/skins/dhtmlxwindows_dhx_skyblue.css' />		
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxForm/codebase/skins/dhtmlxform_dhx_skyblue.css' />
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxVault/codebase/dhtmlxvault.css' />
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxCombo/codebase/dhtmlxcombo.css' />
	<link rel='stylesheet' type='text/css' href='/flw/public/utilities/javascript/vendors/fancyBox/source/jquery.fancybox.css' />
	<link rel="stylesheet" type="text/css" href="../css/styles.css" />
	
	<script src='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlx_std_full/codebase/dhtmlx.js' type='text/javascript'></script>	
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxToolbar/codebase/dhtmlxtoolbar.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxWindows/codebase/dhtmlxwindows.js" type="text/javascript"></script>	
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/dhtmlxcontainer.js" type="text/javascript"></script>	
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/dhtmlxgrid.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_filter.js"></script>	
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
	<script src='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_pgn.js' type='text/javascript'></script>
	<script src='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_selection.js' type='text/javascript'></script>
	<script src='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_nxml.js' type='text/javascript'></script>
	<script src='/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_clist.js' type='text/javascript'></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_grid.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_grid.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_dhxcalendar.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_cntr.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_combo.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_acheck.js" type="text/javascript"></script>	
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_srnd.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_splt.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_group.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_hmenu.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_math.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/ext/dhtmlxgrid_rowspan.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxDataProcessor/codebase/dhtmlxdataprocessor.js" type="text/javascript"></script>

	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxLayout/codebase/dhtmlxlayout.js"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxForm/codebase/dhtmlxform.js"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxVault/codebase/dhtmlxvault.js"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxCombo/codebase/dhtmlxcombo.js"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxCombo/codebase/ext/dhtmlxcombo_extra.js"></script>	
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxForm/codebase/ext/dhtmlxform_item_container.js"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxForm/codebase/ext/dhtmlxform_item_upload.js"></script>
	<script src="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxForm/codebase/ext/dhtmlxform_item_combo.js"></script>
	<script src="/flw/public/utilities/javascript/vendors/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
	<script src="/flw/public/utilities/javascript/vendors/fancyBox/source/jquery.fancybox.js"></script>
	<script src="/flw/public/utilities/javascript/vendors/fancyBox/source/jquery.fancybox.pack.js"></script>

<?php
//	DHTMLX includes END

$objFrontEnd->template_dir = PROJECT_PATH . "templates/";
$objFrontEnd->_cssPath = PROJECT_PATH_REL . "css/";
$objFrontEnd->addCSS("/flw/public/css/global.css", "/flw/public/css/" . $_COOKIE['screenResolution'].".css", $objPageHelper->objCurrentPage->strCss);
$objFrontEnd->_jsPath = "/flw/public/utilities/javaScript/";
$arrJavascript=explode(",",$objPageHelper->objCurrentPage->strJavascript);
for ($i=0; $i<count($arrJavascript); $i++){
	if($arrJavascript[$i]{0} != "/"){
		$arrJavascript[$i]=PROJECT_PATH_REL . "js/" . $arrJavascript[$i];
	}
}
//$arrJavascript=array_merge(array("prototype.js","js_library.js"),$arrJavascript);
$objFrontEnd->addJS($arrJavascript);
//$objFrontEnd->setCharset("UTF-8");


/* ----------------------------------------------------------------------------------------- */
/* Start DB Connection */
/* ----------------------------------------------------------------------------------------- */
//$DBLayer = new DBLayer ( );
//$DBLayer->file = __FILE__;
//$DBLayer->connect ();
//

$objFrontEnd->printHeader();
require ($objFrontEnd->template_dir . $objPageHelper->objCurrentPage->strTemplate);
$objFrontEnd->printFooter();
?>
