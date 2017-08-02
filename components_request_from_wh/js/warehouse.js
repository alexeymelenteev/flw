var app = {};
window.dhx_globalImgPath="/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlx_std_full/codebase/imgs/";
dhtmlx.skin="dhx_skyblue";

$(document).ready(function() {
		$('a.gallery').fancybox(
				{
					'padding' : 5,
					'imageScale' : false,
					'zoomOpacity' : false,
					'zoomSpeedIn' : 1000, 
					'zoomSpeedOut' : 1000, 
					'zoomSpeedChange' : 1000, 
					'frameWidth' : 700, 
					'frameHeight' : 600, 
					'overlayShow' : true, 
					'overlayColor':  '#7fc7ff',
					'hideOnContentClick' :true,
					'centerOnScroll' : false
					
//					,onComplete: function() {
//						$( "<b>hello</b>wait<b>bye</b>" ).appendTo( $('.fancybox-inner') )
//					}
				});
		
	});

$(document).ready(function() {

	app.priorityMax = 5;
//	$('Loading').style.visibility = 'hidden';
	$('#title').html("Warehouse Activity");
	document.title = "Warehouse Activity";
	app.MainLayout = new dhtmlXLayoutObject("main_box","1C");	
	app.MainLayout.setImagePath('/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxLayout/codebase/imgs/');
	app.MainLayout.setSkin("dhx_skyblue");
	app.MainLayout.dhxWins.setImagePath('/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxWindows/codebase/imgs/');
	app.MainLayout.cells("a").hideHeader();
	
//	app.MainLayout.progressOn();

	app.MainLayout.toolbar = app.MainLayout.cells("a").attachToolbar();
	app.MainLayout.toolbar.setIconsPath("/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlx_std_full/codebase/imgs/toolbar_imgs/");
	app.MainLayout.toolbar.loadXML("../xml/toolbar_warehouse.xml");	
	
	app.grid = app.MainLayout.cells("a").attachGrid();	
	app.grid.imgURL = "/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/imgs/";

//	app.grid.attachEvent('onKeyPress', onKeyPressed); 
	
	app.grid.dp = new dataProcessor('../ajax/ajaxFunctions.php?action=saveChangesOfWarehouseActivity');
	app.grid.dp.setUpdateMode('off');
	app.grid.dp.setTransactionMode('POST', true);
	app.grid.dp.enableDataNames(true);
	
	app.grid.dp.attachEvent('onBeforeUpdate',function(sid,action,tid,node)
	{
		app.MainLayout.cells("a").progressOn();
		return true;
	});
	
	app.grid.dp.attachEvent('onAfterUpdate',function(sid,action,tid,node){
		  app.MainLayout.toolbar.disableItem("save");
		  switch(action)
		  {		  	
		  	case 'inserted':
		  		var img = "<a><img src='http://mignt024/flw/images/icon_trash.gif' id='img_"+id+"' onclick='doOnDelete("+tid+")' " +
					" alt='delete row' title='delete row'/></a>";
		  		app.grid.cells(tid, app.grid.getColIndexById('Delete')).setValue(img);		  				  	
		  		app.grid.cells(tid, app.grid.getColIndexById('id')).setValue(tid);
		  		
				dhtmlx.message({
					text:"Successfully " + action + ".<br /><b> " + node.firstChild.data + "</b>",
					lifetime:3000,
					type:"success" });
		  		break;
		  	
		  	case 'updated':
		  		break;
		  		
		  	case 'deleted':
		  		app.MainLayout.toolbar.disableItem("save");
		  		break;
		  		
		  	case 'error':
				dhtmlx.message({
					text:node.firstChild.data,
					lifetime:3000,
					type:"error" });
		  		break
		   }
		app.MainLayout.cells("a").progressOff();
		return true;
		});		
////	dp.setDataColumns([false,false,true,true,false,true,true,false,false,false,false]);
	app.grid.dp.init(app.grid);
//	app.grid.enableValidation(true);
//	app.grid.setColValidators([null,null,null,null,null,null,null,null,"ValidNumeric"]);
////	app.grid.cells(app.grid.getSelectedId(),0).setAttribute("validate","NotEmpty");
//	app.grid.attachEvent("onValidationError",function(id,ind,value)
//	{
//		if(value == 0)
//		alert(value)
//		return false;
//	});
	
	app.grid.attachEvent("onEditCell",function(stage,rowId,cellInd, cellValue) {	
				switch(stage)
				{
					case 2:
						app.MainLayout.toolbar.enableItem('save');
						break;
						
					case 1:
						app.MainLayout.toolbar.enableItem('save');
						break;
				}
				return true;
			});
	
	app.grid.attachEvent("onRowSelect", function(id,ind){
		app.MainLayout.toolbar.enableItem("print");
	});	
   	
   	app.MainLayout.cells("a").progressOn();
	
	$.get('../ajax/ajaxFunctions.php?action=getDataWarehouseActivity',	
		function(transport) {
			if(transport) {				
				if(transport.status == 'success')
				{
					app.grid.setHeader(transport.headers);
					app.grid.setColumnIds(transport.ids);
					app.grid.setInitWidths(transport.widths);
					app.grid.setColTypes(transport.types);
					app.grid.setColAlign(transport.aligns);
					app.grid.attachHeader(transport.filters);
					app.grid.setColSorting(transport.sortings);
					app.grid.enableBlockSelection(true);
					app.grid.enableMultiselect(true);
					app.grid.attachEvent('onKeyPress', onKeyPressed);					
					
					app.grid.init();

					if(transport.data != null)
					{					
						app.grid.parse(transport.data,"jsarray");
						app.grid.setColumnHidden(app.grid.getColIndexById('days_waiting'), true);
						
						app.grid.forEachRow(function(id){
							if (app.grid.cells(id, app.grid.getColIndexById('days_waiting')).getValue() > 0) {
								app.grid.cells(id, app.grid.getColIndexById('requested_date')).setBgColor("#E88181");
							}
							});
						if(transport.show_instruction == '') {
							$('#instruction').trigger('click');
						}
					}
					app.MainLayout.cells("a").progressOff();
				}
				else
				{
					dhtmlx.message({
						text:transport.error,
						lifetime:3000,
						type:"error" });
						app.MainLayout.cells("a").progressOff();
						return;
				}				
			}
		});
});

function showWindowStockOfAllWarehouses(component) {
	wStock = app.MainLayout.dhxWins.createWindow("Stock"+component, 80, 70, 530, 250);
	wStock.setText("Stock of PN: " + component);
	wStock.center();
	wStock.layout = app.MainLayout.dhxWins.window("Stock"+component).attachLayout("1C", "dhx_skyblue");
	wStock.layout = wStock.layout.cells("a");
	wStock.layout.hideHeader();
	wStock.layout.progressOn();
	
	app.MainLayout.dhxWins.window("Stock"+component).button("park").hide();
	
	wStock.layout.grid = wStock.layout.attachGrid();	
	wStock.layout.grid.imgURL = "/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/imgs/";
	
	
	$.get('../ajax/ajaxFunctions.php?action=getStockOfAllWarehouses&component='+component,	
		function(transport)
		{			
			wStock.layout.grid.setHeader(transport.headers);
			wStock.layout.grid.setColumnIds(transport.ids);
			wStock.layout.grid.setInitWidths(transport.widths);
			wStock.layout.grid.setColTypes(transport.types);
			wStock.layout.grid.setColAlign(transport.aligns);
			wStock.layout.grid.attachHeader(transport.filters);
			wStock.layout.grid.setColSorting(transport.sortings);
			wStock.layout.grid.enableBlockSelection(true);
			wStock.layout.grid.attachEvent('onKeyPress', onKeyPressed);
			wStock.layout.grid.init();
			if(transport.data != null)
			{
				wStock.layout.grid.parse(transport.data,"jsarray");
			}
			wStock.layout.progressOff();
		});
}

function showWindowOfWhereUsed(component) {
	wWhereUsed = app.MainLayout.dhxWins.createWindow("Where used"+component, 80, 70, 530, 250);
	wWhereUsed.setText("Where used list for PN: " + component);
	wWhereUsed.center();
	wWhereUsed.layout = app.MainLayout.dhxWins.window("Where used"+component).attachLayout("1C", "dhx_skyblue");
	wWhereUsed.layout = wWhereUsed.layout.cells("a");
	wWhereUsed.layout.hideHeader();
	wWhereUsed.layout.progressOn();
	
	app.MainLayout.dhxWins.window("Where used"+component).button("park").hide();
	
	wWhereUsed.layout.grid = wWhereUsed.layout.attachGrid();	
	wWhereUsed.layout.grid.imgURL = "/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/imgs/";
		
	$.get('../ajax/ajaxFunctions.php?action=getWhereUsed&component='+component,	
			function(transport)
			{				
				wWhereUsed.layout.grid.setHeader(transport.headers);
				wWhereUsed.layout.grid.setColumnIds(transport.ids);
				wWhereUsed.layout.grid.setInitWidths(transport.widths);
				wWhereUsed.layout.grid.setColTypes(transport.types);
				wWhereUsed.layout.grid.setColAlign(transport.aligns);
				wWhereUsed.layout.grid.attachHeader(transport.filters);
				wWhereUsed.layout.grid.setColSorting(transport.sortings);
				wWhereUsed.layout.grid.enableBlockSelection(true);
				wWhereUsed.layout.grid.attachEvent('onKeyPress', onKeyPressed);
				wWhereUsed.layout.grid.init();
				if(transport.data != null)
				{
					wWhereUsed.layout.grid.parse(transport.data,"jsarray");
				}
				wWhereUsed.layout.progressOff();
			});
}

function doOnSave() {	
		app.grid.dp.sendData();			
}

function doOnPrint() {	
	app.MainLayout.progressOn();
	
	var selectedId = '';
	var id = app.grid.getSelectedRowId().split(',');

	for ( i = 0; i < id.length; i++ ) {
		if (i == id.length - 1) {
			selectedId = selectedId + app.grid.cells(id[i], app.grid.getColIndexById('id')).getValue();
		} else {
			selectedId = selectedId + app.grid.cells(id[i], app.grid.getColIndexById('id')).getValue() + ',';
		}
	}
	
	$('#ifrm').attr('src', 'http://mignt024/flw/smt_for_repair/templates/print.html');
	$('#ifrm').contents().find("body").html('');

	$.get('../ajax/ajaxFunctions.php?action=getDataForPrintWarehouseActivity&selectedId='+selectedId,
		function(transport) {	
			if(transport.data != '') {
				$('#ifrm').contents().find("body").append(transport.data);
				document.getElementById('ifrm').contentWindow.print();
			}
			app.MainLayout.progressOff();
		});				
}

function doOnGetExcel()
{
	app.MainLayout.progressOn();
	$.get('../ajax/ajaxFunctions.php?action=getExcelWarehouseActivity',	
	function(transport) {
		if(transport.status == "success") {
			if(transport.data != null) {
				$('#ifrm').attr('src', transport.data);			
			}
			app.MainLayout.progressOff();
			}
	});	
}

function onKeyPressed(code, ctrl, shift) 
{
	if (code == 67 && ctrl) 
	{
		this.setCSVDelimiter('\t');
		this.copyBlockToClipboard();
	}
	if (code == 86 && ctrl) 
	{
		this.pasteBlockFromClipboard();
	}
	return true;
}

function trim(x) {
    return x.replace(/^\s+|\s+$/gm,'');
}

function isNumeric(n) {
	  return !isNaN(parseFloat(n)) && isFinite(n);
}

//function doOnDeleteRow()
//{
//	app.grid.deleteSelectedRows();
//	//app.MainLayout.toolbar.enableItem("save");
//	app.MainLayout.toolbar.disableItem("delete");
////	app.MainLayout.toolbar.disableItem("copy");
//}
