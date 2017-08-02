<?php
/*
Include the session header. Modify path according to where you put the header
file.
*/
require_once($_SERVER['DOCUMENT_ROOT'] .'/flw/php_code/session_header.php');
?>

<html>
<head>
<title>Menu page</title>
<script language="JavaScript1.2" src="/flw/public/utilities/javaScript/coolframe_menu/coolframe.js" type="text/javascript"></script>
<script language="JavaScript1.2" type="text/javascript">
/*******************************************************************************
Copyright (c) 1999 Thomas Brattli (www.bratta.com)

eXperience DHTML coolFrameMenus - Get it at www.bratta.com
Version Beta 1.0
This script can be used freely as long as all copyright messages are
intact.
Visit www.bratta.com/dhtml/scripts.asp for the latest version of the script.
*******************************************************************************/
// Dieses Script wurde von Klaus Hentschel ins deutsche ?bersetzt
// www: http://www.javarea.de
// www: http://kh@javarea.de/
// Bitte entfernen sie diesen Vermerk nicht

//checken der Browsertypen
function checkBrowser(){
	this.ver=navigator.appVersion;
	this.dom=document.getElementById?1:0;
	this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom)?1:0;
	this.ie4=(document.all && !this.dom)?1:0;
	this.ns5=(this.dom && parseInt(this.ver) >= 5) ?1:0;
	this.ns4=(document.layers && !this.dom)?1:0;
	this.opera=!(this.ie5 || this.ie4 || this.ns4 || this.ns5) && this.dom;
	this.bw=(this.ie5 || this.ie4 || this.ns4 || this.ns5);
	return this;
}
var bw=new checkBrowser();
</script>

</head>
<body bgcolor="#7BAFDF">
<script language="JavaScript1.2" src="/flw/public/utilities/javaScript/coolframe_menu/coolframe_style.js" type="text/javascript"></script>
<script language="JavaScript1.2" type="text/javascript">
oCFMenu.startPage="handlers/handler.php?page=items_request"; 	//erst wenn main frame geladen ist wird das menu geladen	

oCFMenu.makeTop('Components request','handlers/handler.php?page=items_request','flw_main',150); 

<?php if(in_array(SMT_FOR_REPEAR_SMT_STOCK,$user_profile_object->allowed_programs)) {?>
	oCFMenu.startPage="handlers/handler.php?page=warehouse";
	oCFMenu.makeTop('Warehouse','handlers/handler.php?page=warehouse','flw_main',150);

<?php }?>	
oCFMenu.makeTop('History','handlers/handler.php?page=history','flw_main',150);


oCFMenu.construct();

//bei jedem ver�ndern der Gr�sse wird das Frameset neu geladen (reload)
searchtext=location.search;
isresized=searchtext.lastIndexOf("resizedurl");
if(isresized>-1){ 			//Get PAGE
	oCFMenu.startPage="http://" + searchtext.substr(isresized+11,searchtext.length);
}

if(top[oCFMenu.menuFrameName]) top[oCFMenu.menuFrameName].location.href=oCFMenu.startPage;
</script>


</body>
</html>
