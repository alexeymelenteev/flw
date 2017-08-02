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

	app.pnIsOk = false;
//	$('Loading').style.visibility = 'hidden';
	$('#title').html("Components request");
	document.title = "Components request";
	app.MainLayout = new dhtmlXLayoutObject("main_box","1C");	
	app.MainLayout.setImagePath('/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxLayout/codebase/imgs/');
	app.MainLayout.setSkin("dhx_skyblue");
	app.MainLayout.dhxWins.setImagePath('/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxWindows/codebase/imgs/');
	app.MainLayout.cells("a").hideHeader();
	
//	app.MainLayout.progressOn();

	app.MainLayout.toolbar = app.MainLayout.cells("a").attachToolbar();
	app.MainLayout.toolbar.setIconsPath("/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlx_std_full/codebase/imgs/toolbar_imgs/");
	app.MainLayout.toolbar.loadXML("../xml/toolbar_items_request.xml");	
	
	app.grid = app.MainLayout.cells("a").attachGrid();	
	app.grid.imgURL = "/flw/public/utilities/javascript/dhtmlx_2.2/dhtmlxGrid/codebase/imgs/";

//	app.grid.attachEvent('onKeyPress', onKeyPressed); 
	
	app.grid.dp = new dataProcessor('../ajax/ajaxFunctions.php?action=saveChangesOfItemsRequest');
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
					lifetime:5000,
					type:"success" });
		  		break;
		  	
		  	case 'updated':		  		
		  		app.grid.cells(tid, app.grid.getColIndexById('npi_type')).setValue(node.firstChild.data);
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

	app.grid.dp.init(app.grid);
	
	app.grid.attachEvent("onEditCell",function(stage,rowId,cellInd, cellValue) {	
		switch(stage) {
			case 2:
				switch(cellInd) {
				case app.grid.getColIndexById("qty"):
					totalAmount = app.grid.cells(rowId, app.grid.getColIndexById("qty")).getValue() * app.grid.cells(rowId, app.grid.getColIndexById("price")).getValue();
					app.grid.cells(rowId, app.grid.getColIndexById("total_amount")).setValue(totalAmount);
					break;
					
//				case app.grid.getColIndexById("wo"):
//					app.MainLayout.cells("a").progressOn();
//					id = app.grid.cells(rowId, app.grid.getColIndexById("id")).getValue();
//					$.get('../ajax/ajaxFunctions.php?action=getNpiTypeByWo&wo='+cellValue+'&id='+id,	
//						function(transport) {
//							if(transport) {				
//								if(transport.status == 'success') {
//									app.grid.cells(rowId, app.grid.getColIndexById("npi_type")).setValue(transport.t_npif);
//								} else {
//									dhtmlx.message({
//										text:transport.error,
//										lifetime:3000,
//										type:"error" });
//										app.MainLayout.cells("a").progressOff();
//										return;									
//								}
//							}					
//					});
//					break;
				}
				app.MainLayout.toolbar.enableItem('save');
				break;
				
			case 1:
//						app.MainLayout.toolbar.enableItem('save');
				break;
		}
		return true;
	});
   	
   	app.MainLayout.cells("a").progressOn();
	
	$.get('../ajax/ajaxFunctions.php?action=getDataItemsRequest',	
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
//						alert(transport.profession)		
						app.grid.parse(transport.data,"jsarray");
						
//						if(transport.profession == 'controller') {
							app.grid.setColumnHidden(0, true);
//							app.grid.setColumnHidden(1, true);
							
//							var colCombo = app.grid.getCombo(app.grid.getColIndexById('npi_type'));
//							colCombo.put('','');		
//							colCombo.put('NPI','NPI');	
//							colCombo.put('Normal','Normal');	
//							colCombo.put('Experiment','Experiment');	
//						}
//						alert(transport.show_instruction )
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
						app.MainLayout.toolbar.disableItem('add');
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

function doOnAddRow()
{
	app.MainLayout.toolbar.disableItem('add');
	wNewRow = app.MainLayout.dhxWins.createWindow("addRow", 85, 130, 450, 350);
	wNewRow.attachEvent("onClose", function(win)
			{
				app.MainLayout.toolbar.enableItem('add');
				return true;
			});
	wNewRow.setText("New");
	wNewRow.setModal(true);
	wNewRow.layout = app.MainLayout.dhxWins.window("addRow").attachLayout("1C", "dhx_skyblue");
	wNewRow.layout = wNewRow.layout.cells("a");
	wNewRow.layout.hideHeader();
	
//	formData = [
//				{type: "settings", position: "label-right", labelAlign: "center"},
//				{type: "block", inputWidth:350, offsetLeft:10, offsetTop:10, list:[
//				{type: "label", label: "Item:", offsetTop:10},
//				{type: "label", label: "Needed quantity:", offsetTop:10},
//   				{type: "newcolumn"},   	
//   				{type: "input", name: "item", offsetLeft:50, offsetTop:15, maxLength:30, inputWidth:150},
//   				{type: "input", name: "qty", offsetLeft:50, offsetTop:18, maxLength:30, inputWidth:80},
//   				{type: "newcolumn"},
//   				]},
//   				{type: "newcolumn"},
//	    		{type: "block", offsetTop:20, offsetLeft:200, list:[
//					           										{type: "button", value: "OK", name:"OK"},
//					           										{type: "newcolumn"},
//					           										{type: "button", value: "Cancel", name:"Cancel", offsetLeft:15}
//					           								]}   				
//			];	
	
	formData = [
	    	    {type: "settings", position: "label-left", labelWidth: 150, inputWidth: 185},
	    	    {type: "block", inputWidth:350, offsetLeft:10, offsetTop:15, list:[
		    		{type: "input", label: "Item:", name:"item"},
		    		{type: "input", label: "Needed quantity:", name:"qty", offsetTop:10},
		    		{type: "combo", label: "Reason:", name: "reason", offsetTop:10},
		    		{type: "input", label: "Price:", name:"price", offsetTop:10},
		    		
		    		{type: "fieldset", label: "Upload file", name: "uploader", inputWidth: 300, offsetTop:20, list:[
      					{type: "upload", 
      					 name: "uploadedFile", 
      					 mode: "html4",
						 autoStart: true,
      					 inputWidth: 300, 
      					 url: "../ajax/ajaxFunctions.php?action=uploadFile"
      					}
      				]}
	    		]},
   				{type: "newcolumn"},
	    		{type: "block", inputWidth:200, offsetTop:15, offsetLeft:180, list:[
					           										{type: "button", value: "OK", name:"OK"},
					           										{type: "newcolumn"},
					           										{type: "button", value: "Cancel", name:"Cancel", offsetLeft:15}
					           								]}
	    	];	
	
	wNewRow.layout.form = wNewRow.layout.attachForm(formData);
	
	var combo = wNewRow.layout.form.getCombo("reason");
	combo.addOption([['lost','lost'],['MRB','MRB'],['ECO','ECO'],['Skip','Skip'],['RMA/REWORK','RMA/REWORK']]);
	combo.readonly(true,true);
	combo.selectOption(0);	
	
	wNewRow.layout.form.setItemFocus("item");	
	wNewRow.layout.form.disableItem('OK');
	wNewRow.layout.form.disableItem('price');
	
//	wNewRow.layout.form.disableItem('uploader');
//	wNewRow.getInput('OK').setAttribute("tabIndex", 1);

	wNewRow.layout.form.attachEvent("onBlur", function(name) {
		switch(name) {
			case "item":
				app.pnIsOk = false;

				if(wNewRow.layout.form.getItemValue('item') != '') {
					wNewRow.layout.progressOn();
//					wNewRow.layout.form.setItemValue('qty', '0');

					item = wNewRow.layout.form.getItemValue('item');
					$.get('../ajax/ajaxFunctions.php?action=getItemDetails', {'item':item},	
						function(transport)
						{					
							if(transport.error.status == "success") {
								if(wNewRow.layout.form.getItemValue('qty') != '' && isNumeric(wNewRow.layout.form.getItemValue('qty'))) {
									wNewRow.layout.form.enableItem('OK');									
								}
								wNewRow.layout.form.setItemValue('item', transport.data.t_item);
								wNewRow.layout.form.setItemFocus("qty");
								wNewRow.layout.progressOff();
								wNewRow.layout.form.setItemValue('price', transport.data.price);
								app.pnIsOk = true;
							} else {
								wNewRow.layout.form.disableItem('OK');
								dhtmlx.message({
									text:transport.error.error_desc,
									lifetime:3000,
									type:"error" });
								wNewRow.layout.progressOff();								
							}
						});
				} else {
					wNewRow.layout.form.disableItem('OK');
				}
				break;
			case "qty":
				wNewRow.layout.form.enableItem('OK');
				qtyValue = wNewRow.layout.form.getItemValue('qty');
				priceValue = wNewRow.layout.form.getItemValue('price');
				reasonValue = wNewRow.layout.form.getItemValue('reason');
				
				if (reasonValue == 'lost' && app.pnIsOk && isNumeric(qtyValue)) {
					if (priceValue * qtyValue >= 5) {					
						dhtmlx.message({
							text:'Total Ammount is over $5. Please attach approval e-mail',
							lifetime:3000,
							type:"error" });				
					}
				}				
				if(isFormValided()) {
					wNewRow.layout.form.enableItem('OK');
				} else {
					wNewRow.layout.form.disableItem('OK');
				}
				break;				
		}
	});
	
	wNewRow.layout.form.attachEvent("onChange", function(name) {
		switch(name) {
			case "reason":
				var reason = wNewRow.layout.form.getItemValue('reason');
				if (reason == 'lost') {
					wNewRow.layout.form.enableItem('uploader');
					
					if(isFormValided()) {
						wNewRow.layout.form.enableItem('OK');
					} else {
						wNewRow.layout.form.disableItem('OK');
					}					
				} else {
					wNewRow.layout.form.disableItem('uploader');
					wNewRow.layout.form.setItemValue('uploadedFile', '');
					
					if(isFormValided()) {
						wNewRow.layout.form.enableItem('OK');
					} else {
						wNewRow.layout.form.disableItem('OK');
					}
				}
				break;				
		}
	});	
	
	wNewRow.layout.form.attachEvent("onUploadFile",function(realName, serverName) {
		var o = jQuery.parseJSON(serverName);
		if(o.status == "success") {
			if(isFormValided()) {
				wNewRow.layout.form.enableItem('OK');
			} else {
				wNewRow.layout.form.disableItem('OK');
			}			
			wNewRow.layout.form.newFileName = o.newFileName;
		}
	});
	
	wNewRow.layout.form.attachEvent("onBeforeFileAdd",function(realName){
		var uploader = wNewRow.layout.form.getItemValue('uploadedFile');
//		alert(uploader.uploadedFile_count)
		if (uploader.uploadedFile_count > 0) {
			dhtmlx.message({
				text:'You can not attach more than one file',
				lifetime:3000,
				type:"error" });			
			return false;
		} else {
			return true;
		}
	});	

	wNewRow.layout.form.attachEvent("onButtonClick", function doOnButtonClick(name, command) {
		switch(name) {
			case "OK":	
				wNewRow.layout.form.disableItem('OK');
				app.MainLayout.cells("a").progressOn();
				
				item = wNewRow.layout.form.getItemValue('item');
				$.get('../ajax/ajaxFunctions.php?action=getItemDetails', {'item':item},	
					function(transport)
					{						
						if(transport.error.status == "success") 
						{
							id = app.grid.uid();
							var img = "<a><img src='http://mignt024/flw/images/icon_trash.gif' id='img_"+id+"' onclick='doOnDelete("+id+")' " +
									"onmouseover='this.style.cursor=hand'  alt='delete row' title='delete row'/></a>";
							var whereUsedLink = "show^javascript:showWindowOfWhereUsed(&apos;"+transport.data.t_item+"&apos;)^_self";
							
							app.grid.addRow(id, [,img], 0);
							app.grid.cells(id, app.grid.getColIndexById('customer_name')).setValue(transport.data.t_nama);
							app.grid.cells(id, app.grid.getColIndexById('customer_number')).setValue(transport.data.t_cuno);
							app.grid.cells(id, app.grid.getColIndexById('component')).setValue(transport.data.t_item);
							app.grid.cells(id, app.grid.getColIndexById('qty')).setValue(wNewRow.layout.form.getItemValue('qty'));
							app.grid.cells(id, app.grid.getColIndexById('npi_type')).setValue(transport.data.t_npif);
							app.grid.cells(id, app.grid.getColIndexById('where_used')).setValue(whereUsedLink);
							app.grid.cells(id, app.grid.getColIndexById('reason')).setValue(wNewRow.layout.form.getItemValue('reason'));
							
//							var uploader = wNewRow.layout.form.getItemValue('uploadedFile');
//							var uploadedFileName = uploader.uploadedFile_r_0;
							
							linkToUploadedFile = '';
							if (wNewRow.layout.form.getItemValue('reason') == 'lost') {
								linkToUploadedFile = 'link^http://mignt024/flw/public/utilities/php_code/file_export.php?file=//mignt002/private/FLWuploadsfiles/smt_for_repair/'+wNewRow.layout.form.newFileName;
							}
							app.grid.cells(id, app.grid.getColIndexById('approval_file')).setValue(linkToUploadedFile);
							
							app.grid.cells(id, app.grid.getColIndexById('description')).setValue(transport.data.description);
							app.grid.cells(id, app.grid.getColIndexById('price')).setValue(transport.data.price);
							app.grid.cells(id, app.grid.getColIndexById('total_amount')).setValue(transport.data.price * wNewRow.layout.form.getItemValue('qty'));
							app.grid.cells(id, app.grid.getColIndexById('stock_shortage')).setValue(transport.data.stock_shortage);
							app.grid.cells(id, app.grid.getColIndexById('requested_date')).setValue(transport.data.requested_date);
							app.grid.cells(id, app.grid.getColIndexById('requester')).setValue(transport.data.requester);

//							app.grid.setCellExcellType(id,app.grid.getColIndexById('wo'),'ed');
//							app.grid.setCellExcellType(id,app.grid.getColIndexById('wo_position'),'ed');				
							
							app.MainLayout.toolbar.enableItem('save');
							
							wNewRow.layout.form.setItemValue('item', '');
							wNewRow.layout.form.setItemValue('qty', '');
							wNewRow.layout.form.setItemValue('uploadedFile', '');
							
							app.MainLayout.cells("a").progressOff();
							if(transport.data.message != '') {
								dhtmlx.message({
									text:transport.data.message,
									lifetime:3000,
									type:"error" });
							}
						}
						else
						{
							app.MainLayout.cells("a").progressOff();
							wNewRow.layout.form.enableItem('OK');
							dhtmlx.message({
								text:transport.error.error_desc,
								lifetime:3000,
								type:"error" });	
						}
					});							
				break;
			case "Cancel":
				wNewRow.close();
//				app.MainLayout.toolbar.enableItem("save");
				break;
		}
	});	
	 // 188697
	function isFormValided() {
		var reasonValue = 	wNewRow.layout.form.getItemValue('reason');
		var qtyValue = 		wNewRow.layout.form.getItemValue('qty');
		var priceValue = 	wNewRow.layout.form.getItemValue('price');
		var uploader = 		wNewRow.layout.form.getItemValue('uploadedFile')

		if (reasonValue == 'lost' && app.pnIsOk && isNumeric(qtyValue)) {
			if (priceValue * qtyValue <= 100 && priceValue <= 5) {
				return true;
			}
			if (uploader.uploadedFile_count >= 1) {
				return true;
			}
		} else {
			if (app.pnIsOk && isNumeric(qtyValue)) {
				return true;
			}			
		}
		return false;
	}	
}

function doOnDelete(id)
{
	app.grid.deleteRow(id);
	app.MainLayout.toolbar.enableItem('save');
}

function doOnGetExcel()
{
	app.MainLayout.progressOn();
	$.get('../ajax/ajaxFunctions.php?action=getExcelItemsRequest',	
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
