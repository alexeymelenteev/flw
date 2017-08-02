<?php

require_once (FLW_DOC_ROOT . '/public/utilities/class/core/Autoloader.php');

spl_autoload_register(array('Autoloader', 'loadCoreClass'));

class Attachments
	{							
		function uploadFiles($targetFolder) {
		    $targetFolder .= '\\';
        	try 
        	{		    
    		    if ( ! file_exists(Application::getPathToUploadFolder() .$targetFolder)) {
    		        mkdir(Application::getPathToUploadFolder() .$targetFolder);
    		    }

        	    if ( ! file_exists(Application::getPathToUploadFolder() .$targetFolder)) {
    		        throw new Exception('Target directory does not exist');
    		    }    		    
    		    
    			set_time_limit(500);		
    			if (@$_REQUEST["mode"] == "html4") {
    				if (@$_REQUEST["action"] == "cancel") {
    					print_r("{state:'cancelled'}");
    				} 
    				else {
    					$filename = $_FILES["file"]["name"];
    					$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    					$md5file = md5_file($_FILES["file"]["tmp_name"]);
    					
    					move_uploaded_file($_FILES["file"]["tmp_name"], Application::getPathToUploadFolder() .$targetFolder .$md5file ."." .$extension);					
    					
						if(file_exists(Application::getPathToUploadFolder() .$targetFolder .$md5file ."." .$extension)) {						
							
							
							$arrResult["newFileName"] = $md5file .'.' .$extension;
							$arrResult["file_source"] = $filename;
							$arrResult["status"] = "success";
//							$arrResult["link"] = $this->oBizDocDB->sLink .$md5file;
							
							$date = date('d/m/Y', time());
							$arrResult["date"] = $date;
							
							print_r("{state: true, name:'" .json_encode($arrResult) ."', size:" .$_FILES["file"]["size"] ."}");
						}
						else {
							throw new Exception("File does not exist!");
						}
    
    				}
    			}
		    }
			catch (Exception $e)
			{
				$arrResult["status"] = "fail";
				$arrResult["error"] = $e->getMessage();						
				print_r("{state: true, name:'" .json_encode($arrResult) ."', size:" .$_FILES["file"]["size"] ."}");
			}					
		} 	
	
				
	}
?> 